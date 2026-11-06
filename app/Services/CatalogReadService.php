<?php

namespace App\Services;

use App\Models\BrandModel;
use App\Models\CategoryModel;
use App\Models\ProductModel;
use CodeIgniter\Database\BaseConnection;
use Config\Database;

class CatalogReadService
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? Database::connect();
    }

    public function brands(): array
    {
        return (new BrandModel($this->db))->where('is_active', 1)->orderBy('name')->findAll(100);
    }

    public function categories(): array
    {
        return (new CategoryModel($this->db))->where('status', 'active')->orderBy('sort_order')->findAll(100);
    }

    /**
     * Both SSR and public API use the same filtered, paginated catalog query.
     * All media for the current page is fetched in a single query.
     */
    public function products(array $filters = []): array
    {
        $page = max(1, min(100000, (int) ($filters['page'] ?? 1)));
        $perPage = max(1, min(48, (int) ($filters['per_page'] ?? 24)));
        $query = (new ProductModel($this->db))->where('status', 'active');

        $search = trim((string) ($filters['q'] ?? ''));
        if ($search !== '') {
            $query->like('name', mb_substr($search, 0, 120));
        }
        foreach (['category' => ['categories', 'category_id'], 'brand' => ['brands', 'brand_id']] as $key => [$table, $column]) {
            $slug = trim((string) ($filters[$key] ?? ''));
            if ($slug === '') {
                continue;
            }
            $row = $this->db->table($table)->select('id')->where('slug', $slug)
                ->where($table === 'brands' ? 'is_active' : 'status', $table === 'brands' ? 1 : 'active')
                ->get()->getRowArray();
            if ($row === null) {
                return ['data' => [], 'meta' => ['page' => $page, 'per_page' => $perPage, 'total' => 0, 'last_page' => 0]];
            }
            $query->where($column, $row['id']);
        }
        foreach (['min_price' => '>=', 'max_price' => '<='] as $key => $operator) {
            $price = $filters[$key] ?? null;
            if ($price !== null && $price !== '' && ctype_digit((string) $price)) {
                $query->where('price_toman ' . $operator, (int) $price);
            }
        }
        $sort = (string) ($filters['sort'] ?? '');
        if ($sort === 'price_asc' || $sort === 'price_desc') {
            $query->orderBy('price_toman', $sort === 'price_asc' ? 'ASC' : 'DESC');
        } else {
            $query->orderBy('id', 'DESC');
        }
        $total = $query->countAllResults(false);
        $products = $query->findAll($perPage, ($page - 1) * $perPage);
        $ids = array_map('intval', array_column($products, 'id'));
        if ($ids !== []) {
            $media = $this->db->table('product_media')->select('product_id, file_path, alt_text')
                ->whereIn('product_id', $ids)->orderBy('is_primary', 'DESC')
                ->orderBy('sort_order', 'ASC')->get()->getResultArray();
            $images = [];
            foreach ($media as $image) {
                $id = (int) $image['product_id'];
                if (! isset($images[$id])) {
                    $images[$id] = $image;
                }
            }
            foreach ($products as &$product) {
                $image = $images[(int) $product['id']] ?? null;
                $product['image_url'] = $image ? base_url(ltrim($image['file_path'], '/')) : null;
                $product['image_alt'] = $image['alt_text'] ?? $product['name'];
            }
            unset($product);
        }
        return ['data' => $products, 'meta' => [
            'page' => $page, 'per_page' => $perPage, 'total' => $total,
            'last_page' => (int) ceil($total / $perPage),
        ]];
    }

    public function related(int $productId, int $limit = 6): ?array
    {
        $source = (new ProductModel($this->db))->where('id', $productId)
            ->where('status', 'active')->first();
        if ($source === null) {
            return null;
        }
        if (empty($source['category_id'])) {
            return [];
        }
        $products = (new ProductModel($this->db))->where('status', 'active')
            ->where('category_id', $source['category_id'])
            ->where('id !=', $productId)
            ->orderBy('id', 'DESC')->findAll(min(12, max(1, $limit)));
        $ids = array_map('intval', array_column($products, 'id'));
        if ($ids === []) {
            return [];
        }
        $media = $this->db->table('product_media')
            ->select('product_id, file_path, alt_text')
            ->whereIn('product_id', $ids)->orderBy('is_primary', 'DESC')
            ->orderBy('sort_order', 'ASC')->get()->getResultArray();
        $first = [];
        foreach ($media as $image) {
            $id = (int) $image['product_id'];
            $first[$id] ??= $image;
        }
        foreach ($products as &$product) {
            $image = $first[(int) $product['id']] ?? null;
            $product['image_url'] = $image ? base_url(ltrim($image['file_path'], '/')) : null;
            $product['image_alt'] = $image['alt_text'] ?? $product['name'];
        }
        unset($product);
        return $products;
    }

    public function product(string $slug): ?array
    {
        $product = (new ProductModel($this->db))->where('slug', $slug)
            ->where('status', 'active')->first();
        if ($product === null) {
            return null;
        }
        $id = (int) $product['id'];
        $media = $this->db->table('product_media')->where('product_id', $id)
            ->orderBy('sort_order')->get()->getResultArray();
        $variants = $this->db->table('product_variants v')
            ->select('v.*, COALESCE(i.quantity, 0) AS stock')
            ->join('inventory_items i', 'i.variant_id = v.id', 'left')
            ->where('v.product_id', $id)->where('v.is_active', 1)
            ->orderBy('v.sort_order')->get()->getResultArray();
        return ['product' => $product, 'media' => $media, 'variants' => $variants];
    }
}
