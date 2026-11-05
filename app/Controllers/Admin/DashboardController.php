<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\Database\BaseConnection;
use Config\Database;

class DashboardController extends BaseController
{
    public function index()
    {
        return view('admin/dashboard/index', [
            'adminName' => session()->get('admin_name'),
        ]);
    }

    public function summary()
    {
        /** @var BaseConnection $db */
        $db = Database::connect();

        $summary = [
            'products' => (int) $db->table('products')->where('deleted_at', null)->countAllResults(),
            'active_products' => (int) $db->table('products')->where('status', 'active')->where('deleted_at', null)->countAllResults(),
            'categories' => (int) $db->table('categories')->where('status', 'active')->countAllResults(),
            'low_stock_variants' => (int) $db->table('inventory_items')
                ->where('quantity <= low_stock_threshold', null, false)
                ->countAllResults(),
        ];

        return $this->response->setJSON(['success' => true, 'data' => $summary]);
    }
}