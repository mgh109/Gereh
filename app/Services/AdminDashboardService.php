<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use Config\Database;

class AdminDashboardService
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? Database::connect();
    }

    public function summary(): array
    {
        $paid = $this->db->table('orders')
            ->select('COUNT(*) AS paid_orders, COALESCE(SUM(total_toman), 0) AS revenue_toman', false)
            ->where('payment_status', 'paid')->get()->getRowArray();

        return [
            'products' => (int) $this->db->table('products')->where('deleted_at', null)->countAllResults(),
            'active_products' => (int) $this->db->table('products')->where('status', 'active')
                ->where('deleted_at', null)->countAllResults(),
            'categories' => (int) $this->db->table('categories')
                ->where('status', 'active')->countAllResults(),
            'brands' => (int) $this->db->table('brands')->where('is_active', 1)->countAllResults(),
            'customers' => (int) $this->db->table('customers')->where('status', 'active')->countAllResults(),
            'orders' => (int) $this->db->table('orders')->countAllResults(),
            'new_orders' => (int) $this->db->table('orders')->where('order_status', 'pending_payment')->countAllResults(),
            'paid_orders' => (int) ($paid['paid_orders'] ?? 0),
            'revenue_toman' => (int) ($paid['revenue_toman'] ?? 0),
            'low_stock_variants' => (int) $this->db->table('inventory_items')
                ->where('quantity <= low_stock_threshold', null, false)->countAllResults(),
        ];
    }
}
