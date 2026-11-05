<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ProductModel;

class ProductsController extends BaseController
{
    public function index()
    {
        $products = [];
        $databaseReady = true;
        try {
            $products = (new ProductModel())->orderBy('id', 'DESC')->findAll(50);
        } catch (\Throwable $exception) {
            log_message('warning', 'Product listing unavailable: {message}', ['message' => $exception->getMessage()]);
            $databaseReady = false;
        }

        return view('admin/products/index', [
            'products' => $products,
            'databaseReady' => $databaseReady,
            'adminName' => session()->get('admin_name') ?: 'مدیر فروشگاه',
        ]);
    }
}
