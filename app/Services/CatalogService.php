<?php

namespace App\Services;

use App\Models\BrandModel;
use App\Models\CategoryModel;
use App\Models\InventoryItemModel;
use App\Models\ProductMediaModel;
use App\Models\ProductModel;
use App\Models\ProductVariantModel;
use CodeIgniter\Database\BaseConnection;
use Config\Database;
use RuntimeException;
use Throwable;

class CatalogService
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? Database::connect();
    }

    public function createProduct(array $data, array $variants = [], array $media = []): int
    {
        $this->assertRelations($data);
        $this->assertUniqueSlug((string) $data['slug']);

        $this->db->transBegin();

        try {
            $products = new ProductModel($this->db);
            $productId = $products->insert($data, true);

            if (! $productId) {
                throw new RuntimeException('ثبت محصول انجام نشد: ' . json_encode($products->errors(), JSON_UNESCAPED_UNICODE));
            }

            $variantModel = new ProductVariantModel($this->db);
            $inventoryModel = new InventoryItemModel($this->db);

            foreach ($variants as $variant) {
                $variant['product_id'] = $productId;
                $variantId = $variantModel->insert($variant, true);

                if (! $variantId) {
                    throw new RuntimeException('ثبت یکی از تنوع‌های محصول ناموفق بود.');
                }

                $inventoryModel->insert([
                    'variant_id' => $variantId,
                    'quantity' => 0,
                    'low_stock_threshold' => 2,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }

            $mediaModel = new ProductMediaModel($this->db);
            foreach ($media as $item) {
                $item['product_id'] = $productId;
                if (! $mediaModel->insert($item)) {
                    throw new RuntimeException('ثبت یکی از تصاویر محصول ناموفق بود.');
                }
            }

            $this->assertTransaction();
            $this->db->transCommit();

            return (int) $productId;
        } catch (Throwable $exception) {
            $this->db->transRollback();
            throw $exception;
        }
    }

    public function updateProduct(int $productId, array $data): bool
    {
        $product = (new ProductModel($this->db))->find($productId);
        if ($product === null) {
            throw new RuntimeException('محصول موردنظر پیدا نشد.');
        }

        $this->assertRelations($data);
        if (isset($data['slug']) && $data['slug'] !== $product['slug']) {
            $this->assertUniqueSlug((string) $data['slug'], $productId);
        }

        $updated = (new ProductModel($this->db))->update($productId, $data);
        if ($updated === false) {
            throw new RuntimeException('ویرایش محصول انجام نشد.');
        }

        return true;
    }

    public function setProductStatus(int $productId, string $status): bool
    {
        if (! in_array($status, ['draft', 'active', 'archived'], true)) {
            throw new RuntimeException('وضعیت انتخاب‌شده معتبر نیست.');
        }

        $model = new ProductModel($this->db);
        if ($model->find($productId) === null) {
            throw new RuntimeException('محصول موردنظر پیدا نشد.');
        }

        return $model->update($productId, ['status' => $status]);
    }

    public function createCategory(array $data): int
    {
        if (! empty($data['parent_id'])) {
            $parent = (new CategoryModel($this->db))->find($data['parent_id']);
            if ($parent === null) {
                throw new RuntimeException('دسته‌بندی والد پیدا نشد.');
            }
        }

        $model = new CategoryModel($this->db);
        $id = $model->insert($data, true);
        if (! $id) {
            throw new RuntimeException('ثبت دسته‌بندی انجام نشد.');
        }

        return (int) $id;
    }

    public function createBrand(array $data): int
    {
        $model = new BrandModel($this->db);
        $id = $model->insert($data, true);
        if (! $id) {
            throw new RuntimeException('ثبت برند انجام نشد.');
        }

        return (int) $id;
    }

    private function assertRelations(array $data): void
    {
        if (! empty($data['category_id']) && (new CategoryModel($this->db))->find($data['category_id']) === null) {
            throw new RuntimeException('دسته‌بندی انتخاب‌شده وجود ندارد.');
        }

        if (! empty($data['brand_id']) && (new BrandModel($this->db))->find($data['brand_id']) === null) {
            throw new RuntimeException('برند انتخاب‌شده وجود ندارد.');
        }
    }

    private function assertUniqueSlug(string $slug, ?int $exceptId = null): void
    {
        $builder = $this->db->table('products')->where('slug', $slug);
        if ($exceptId !== null) {
            $builder->where('id !=', $exceptId);
        }

        if ($builder->countAllResults() > 0) {
            throw new RuntimeException('این نشانی محصول قبلاً استفاده شده است.');
        }
    }

    private function assertTransaction(): void
    {
        if ($this->db->transStatus() === false) {
            throw new RuntimeException('ثبت اطلاعات کاتالوگ با خطا مواجه شد.');
        }
    }
}