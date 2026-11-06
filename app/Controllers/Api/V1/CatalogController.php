<?php

namespace App\Controllers\Api\V1;

use App\Controllers\BaseController;
use App\Services\CatalogReadService;
use CodeIgniter\API\ResponseTrait;

class CatalogController extends BaseController
{
    use ResponseTrait;

    private function catalog(): CatalogReadService
    {
        return new CatalogReadService();
    }

    private function reply($data, array $meta = [], int $status = 200)
    {
        return $this->respond([
            'success' => true, 'data' => $data, 'meta' => $meta,
            'message' => null, 'errors' => null,
        ], $status);
    }

    private function failure(string $message, int $status, array $errors = [])
    {
        return $this->respond([
            'success' => false, 'data' => null, 'meta' => new \stdClass(),
            'message' => $message, 'errors' => $errors ?: null,
        ], $status);
    }

    public function products()
    {
        $filters = $this->request->getGet();
        $filters = is_array($filters) ? $filters : [];
        foreach (['page', 'per_page', 'min_price', 'max_price'] as $field) {
            if (isset($filters[$field]) && (! is_scalar($filters[$field]) || ! ctype_digit((string) $filters[$field]))) {
                return $this->failure('پارامتر ورودی نامعتبر است.', 422, [$field => 'Must be a non-negative integer.']);
            }
        }
        foreach (['sort', 'brand', 'category', 'q'] as $field) {
            if (isset($filters[$field]) && ! is_string($filters[$field])) {
                return $this->failure('پارامتر ورودی نامعتبر است.', 422, [$field => 'Must be a string.']);
            }
        }
        if (isset($filters['sort']) && ! in_array($filters['sort'], ['', 'newest', 'price_asc', 'price_desc'], true)) {
            return $this->failure('مرتب‌سازی نامعتبر است.', 422, ['sort' => 'Unsupported sort order.']);
        }
        try {
            $result = $this->catalog()->products($filters);
            return $this->reply($result['data'], $result['meta']);
        } catch (\Throwable $exception) {
            log_message('error', 'Catalog API listing: {message}', ['message' => $exception->getMessage()]);
            return $this->failure('دریافت محصولات موقتاً امکان‌پذیر نیست.', 500);
        }
    }

    public function product(string $slug)
    {
        try {
            $result = $this->catalog()->product($slug);
            return $result === null ? $this->failure('محصول پیدا نشد.', 404) : $this->reply($result);
        } catch (\Throwable $exception) {
            log_message('error', 'Catalog API product: {message}', ['message' => $exception->getMessage()]);
            return $this->failure('دریافت محصول موقتاً امکان‌پذیر نیست.', 500);
        }
    }

    public function brands()
    {
        try {
            return $this->reply($this->catalog()->brands());
        } catch (\Throwable $exception) {
            log_message('error', 'Catalog API brands: {message}', ['message' => $exception->getMessage()]);
            return $this->failure('دریافت برندها موقتاً امکان‌پذیر نیست.', 500);
        }
    }

    public function categories()
    {
        try {
            return $this->reply($this->catalog()->categories());
        } catch (\Throwable $exception) {
            log_message('error', 'Catalog API categories: {message}', ['message' => $exception->getMessage()]);
            return $this->failure('دریافت دسته‌بندی‌ها موقتاً امکان‌پذیر نیست.', 500);
        }
    }
}
