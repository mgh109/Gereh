<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\ProductMediaModel;

class Storefront extends BaseController
{
    public function home(): string
    {
        return view('store/home', ['products' => $this->products(6)]);
    }

    public function shop(): string
    {
        return view('store/shop', ['products' => $this->products(24)]);
    }

    public function product(string $slug)
    {
        try {
            $product = (new ProductModel())->where('slug', $slug)->where('status', 'active')->first();
            if ($product === null) {
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
            }
            $media = (new ProductMediaModel())->where('product_id', $product['id'])->orderBy('sort_order', 'ASC')->findAll();
            return view('store/product', ['product' => $product, 'media' => $media]);
        } catch (\CodeIgniter\Exceptions\PageNotFoundException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            log_message('warning', 'Storefront product unavailable: {message}', ['message' => $exception->getMessage()]);
            return view('store/unavailable');
        }
    }

    private function products(int $limit): array
    {
        try {
            $products = (new ProductModel())->where('status', 'active')->orderBy('id', 'DESC')->findAll($limit);
            if ($products === []) {
                return [];
            }
            $mediaModel = new ProductMediaModel();
            foreach ($products as &$product) {
                $image = $mediaModel->where('product_id', $product['id'])->orderBy('sort_order', 'ASC')->first();
                $product['image_url'] = $image['url'] ?? null;
            }
            unset($product);
            return $products;
        } catch (\Throwable $exception) {
            log_message('warning', 'Storefront catalog unavailable: {message}', ['message' => $exception->getMessage()]);
            return [];
        }
    }
}
