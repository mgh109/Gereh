<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\ProductMediaModel;

class Storefront extends BaseController
{
    public function home(): string
    {
        return view('store/home', ['products' => $this->products(6), 'brands' => $this->brands()]);
    }

    public function shop(): string
    {
        return view('store/shop', [
            'products' => $this->products(24, true),
            'brands' => $this->brands(),
        ]);
    }

    public function product(string $slug)
    {
        try {
            $product = (new ProductModel())->where('slug', $slug)->where('status', 'active')->first();
            if ($product === null) {
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
            }
            $media = (new ProductMediaModel())->where('product_id', $product['id'])->orderBy('sort_order', 'ASC')->findAll();
            $variants = (new \\App\\Models\\ProductVariantModel())->where('product_id', $product['id'])->where('is_active', 1)->orderBy('sort_order', 'ASC')->findAll();
            if ($variants) {
                $stockRows = \\Config\\Database::connect()->table('inventory_items')->whereIn('variant_id', array_column($variants, 'id'))->get()->getResultArray();
                $stock = array_column($stockRows, 'quantity', 'variant_id');
                foreach ($variants as &$variant) {
                    $variant['stock'] = (int) ($stock[$variant['id']] ?? 0);
                }
                unset($variant);
            }
            return view('store/product', ['product' => $product, 'media' => $media, 'variants' => $variants]);
        } catch (\CodeIgniter\Exceptions\PageNotFoundException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            log_message('warning', 'Storefront product unavailable: {message}', ['message' => $exception->getMessage()]);
            return view('store/unavailable');
        }
    }

    private function brands(): array
    {
        try {
            return (new \\App\\Models\\BrandModel())->orderBy('name', 'ASC')->findAll(100);
        } catch (\\Throwable $exception) {
            log_message('warning', 'Storefront brands unavailable: {message}', ['message' => $exception->getMessage()]);
            return [];
        }
    }

    private function products(int $limit, bool $filter = false): array
    {
        try {
            $query = (new ProductModel())->where('status', 'active');
            if ($filter) {
                $search = trim((string) $this->request->getGet('q'));
                if ($search !== '') {
                    $query->like('name', $search);
                }
                $categorySlug = trim((string) $this->request->getGet('category'));
                if ($categorySlug !== '') {
                    $category = (new \\App\\Models\\CategoryModel())->where('slug', $categorySlug)->first();
                    if ($category === null) {
                        return [];
                    }
                    $query->where('category_id', $category['id']);
                }
                $brandSlug = trim((string) $this->request->getGet('brand'));
                if ($brandSlug !== '') {
                    $brand = (new \\App\\Models\\BrandModel())->where('slug', $brandSlug)->first();
                    if ($brand === null) {
                        return [];
                    }
                    $query->where('brand_id', $brand['id']);
                }
                $minPrice = $this->request->getGet('min_price');
                $maxPrice = $this->request->getGet('max_price');
                if (is_scalar($minPrice) && ctype_digit((string) $minPrice)) {
                    $query->where('price_toman >=', (int) $minPrice);
                }
                if (is_scalar($maxPrice) && ctype_digit((string) $maxPrice)) {
                    $query->where('price_toman <=', (int) $maxPrice);
                }
            }
            $sort = $filter ? (string) $this->request->getGet('sort') : '';
            if ($sort === 'price_asc') {
                $query->orderBy('price_toman', 'ASC');
            } elseif ($sort === 'price_desc') {
                $query->orderBy('price_toman', 'DESC');
            } else {
                $query->orderBy('id', 'DESC');
            }
            $products = $query->findAll($limit);
            if ($products === []) {
                return [];
            }
            $mediaModel = new ProductMediaModel();
            foreach ($products as &$product) {
                $image = $mediaModel->where('product_id', $product['id'])->orderBy('sort_order', 'ASC')->first();
                $product['image_url'] = (!empty($image['file_path']) ? base_url(ltrim($image['file_path'], '/')) : null);
            }
            unset($product);
            return $products;
        } catch (\Throwable $exception) {
            log_message('warning', 'Storefront catalog unavailable: {message}', ['message' => $exception->getMessage()]);
            return [];
        }
    }
}
