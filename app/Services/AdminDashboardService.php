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

        // Use settlement time, rather than order creation time, for revenue.
        $today = date('Y-m-d');
        $start = date('Y-m-d', strtotime('-6 days'));
        $receipts = $this->db->table('payment_transactions')
            ->select('DATE(verified_at) AS day, SUM(amount_toman) AS revenue_toman', false)
            ->where('status', 'paid')->where('verified_at >=', $start . ' 00:00:00')
            ->where('verified_at <', date('Y-m-d', strtotime('+1 day')) . ' 00:00:00')
            ->groupBy('DATE(verified_at)', false)->get()->getResultArray();
        $byDay = array_column($receipts, 'revenue_toman', 'day');
        $weekly = [];
        for ($offset = 6; $offset >= 0; $offset--) {
            $day = date('Y-m-d', strtotime('-' . $offset . ' days'));
            $weekly[] = ['date' => $day, 'revenue_toman' => (int) ($byDay[$day] ?? 0)];
        }
        return [
            'engagement' => (new ProductEngagementService($this->db))->dashboard(),
            'today_revenue_toman' => (int) ($byDay[$today] ?? 0),
            'weekly_revenue' => $weekly,
            'processing_orders' => (int) $this->db->table('orders')->where('order_status', 'processing')->countAllResults(),
            'payment_review_orders' => (int) $this->db->table('orders')->where('order_status', 'payment_review')->countAllResults(),
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
