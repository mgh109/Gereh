<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use Config\Database;
use InvalidArgumentException;

final class AdminOrdersService
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? Database::connect();
    }

    public function paginate(array $filters = []): array
    {
        $page = filter_var($filters['page'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $perPage = filter_var($filters['per_page'] ?? 20, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 100]]);
        if ($page === false || $perPage === false || $page > 100000) {
            throw new InvalidArgumentException('صفحه‌بندی نامعتبر است.');
        }

        $query = $this->db->table('orders o')
            ->select('o.id, o.order_number, o.customer_id, o.receiver_name, o.total_toman, o.payment_status, o.order_status, o.shipping_method, o.created_at, c.mobile AS customer_mobile')
            ->join('customers c', 'c.id = o.customer_id');
        foreach (['payment_status' => ['unpaid', 'paid', 'review_required', 'failed'],
                  'order_status' => ['pending_payment', 'processing', 'payment_review', 'cancelled', 'shipped', 'delivered']] as $field => $allowed) {
            $value = $filters[$field] ?? null;
            if ($value === null || $value === '') {
                continue;
            }
            if (! is_string($value) || ! in_array($value, $allowed, true)) {
                throw new InvalidArgumentException('فیلتر وضعیت نامعتبر است.');
            }
            $query->where('o.' . $field, $value);
        }

        $total = $query->countAllResults(false);
        $rows = $query->orderBy('o.id', 'DESC')->limit($perPage, ($page - 1) * $perPage)
            ->get()->getResultArray();
        return [
            'data' => $rows,
            'meta' => [
                'page' => $page, 'per_page' => $perPage,
                'total' => $total, 'last_page' => (int) ceil($total / $perPage),
            ],
        ];
    }

    public function find(int $id): ?array
    {
        if ($id < 1) {
            return null;
        }
        $order = $this->db->table('orders o')
            ->select('o.*, c.mobile AS customer_mobile')
            ->join('customers c', 'c.id = o.customer_id')
            ->where('o.id', $id)->get()->getRowArray();
        if ($order === null) {
            return null;
        }
        unset($order['idempotency_key']);
        $order['items'] = $this->db->table('order_items')->where('order_id', $id)
            ->orderBy('id', 'ASC')->get()->getResultArray();
        $order['payments'] = $this->db->table('payment_transactions')
            ->select('id, provider, authority, ref_id, amount_toman, status, created_at, verified_at')
            ->where('order_id', $id)->orderBy('id', 'DESC')->get()->getResultArray();
        return $order;
    }
}
