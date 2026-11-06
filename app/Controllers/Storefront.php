<?php

namespace App\Controllers;

use App\Services\CatalogReadService;
use CodeIgniter\Exceptions\PageNotFoundException;

class Storefront extends BaseController
{
    public function home(): string
    {
        try {
            $catalog = new CatalogReadService();
            return view('store/home', [
                'products' => $catalog->products(['per_page' => 6])['data'],
                'brands' => $catalog->brands(),
                'banners' => (new \App\Services\HomeContentService())->banners(),
            ]);
        } catch (\Throwable $exception) {
            log_message('error', 'Storefront home catalog: {message}', ['message' => $exception->getMessage()]);
            return view('store/home', ['products' => [], 'brands' => [], 'banners' => []]);
        }
    }

    public function shop(): string
    {
        try {
            $catalog = new CatalogReadService();
            $filters = $this->request->getGet();
            $result = $catalog->products(is_array($filters) ? $filters : []);
            return view('store/shop', [
                'products' => $result['data'],
                'pagination' => $result['meta'],
                'brands' => $catalog->brands(),
            ]);
        } catch (\Throwable $exception) {
            log_message('error', 'Storefront shop catalog: {message}', ['message' => $exception->getMessage()]);
            return view('store/shop', [
                'products' => [], 'brands' => [],
                'pagination' => ['page' => 1, 'per_page' => 24, 'total' => 0, 'last_page' => 0],
            ]);
        }
    }

    public function product(string $slug)
    {
        try {
            $result = (new CatalogReadService())->product($slug);
            if ($result === null) {
                throw PageNotFoundException::forPageNotFound();
            }
            return view('store/product', $result);
        } catch (PageNotFoundException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            log_message('error', 'Storefront product: {message}', ['message' => $exception->getMessage()]);
            return view('store/unavailable');
        }
    }
}
