<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use Config\Database;
use DomainException;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * Creates unpaid orders from trusted DB prices. Does not reserve or deduct stock.
 * Payment confirmation must atomically recheck and decrement stock before fulfillment.
 */
class OrderService
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? Database::connect();
    }

    public function create(int $customerId, array $cart, array $recipient, string $key, string $shippingMethod = 'standard'): array
    {
        if ($customerId < 1) {
            throw new DomainException('برای ثبت سفارش باید وارد حساب کاربری شوید.');
        }
        if (! preg_match('/^[a-zA-Z0-9_-]{16,64}$/D', $key)) {
            throw new InvalidArgumentException('کلید یکتای درخواست معتبر نیست.');
        }
        $required = [
            'receiver_name' => 160, 'receiver_mobile' => 16, 'province' => 100,
            'city' => 100, 'address' => 2000, 'postal_code' => 10,
        ];
        foreach ($required as $field => $limit) {
            if (! isset($recipient[$field]) || ! is_string($recipient[$field])
                || trim($recipient[$field]) === '' || mb_strlen($recipient[$field]) > $limit) {
                throw new InvalidArgumentException('اطلاعات گیرنده ناقص یا نامعتبر است: ' . $field);
            }
            $recipient[$field] = trim($recipient[$field]);
        }
        if (! preg_match('/^09[0-9]{9}$/D', $recipient['receiver_mobile'])
            || ! preg_match('/^[0-9]{10}$/D', $recipient['postal_code'])) {
            throw new InvalidArgumentException('شماره موبایل یا کد پستی نامعتبر است.');
        }

        if ($cart === []) {
            throw new InvalidArgumentException('سبد خرید خالی است.');
        }
        $normalized = [];
        foreach ($cart as $variantId => $quantity) {
            if (! ctype_digit((string) $variantId) || (int) $variantId < 1
                || ! is_numeric($quantity) || (string) (int) $quantity !== (string) $quantity
                || (int) $quantity < 1 || (int) $quantity > 20) {
                throw new InvalidArgumentException('تعداد یا شناسه کالا معتبر نیست.');
            }
            $normalized[(int) $variantId] = (int) $quantity;
        }
        if (count($normalized) > 50) {
            throw new InvalidArgumentException('تعداد اقلام سبد از حد مجاز بیشتر است.');
        }

        $this->db->transBegin();
        try {
            $existing = $this->db->table('orders')->where('customer_id', $customerId)
                ->where('idempotency_key', $key)->get()->getRowArray();
            if ($existing !== null) {
                $this->db->transCommit();
                unset($existing['idempotency_key']);
                return $existing;
            }
            $customer = $this->db->query(
                'SELECT id FROM customers WHERE id = ? AND status = ? FOR UPDATE',
                [$customerId, 'active']
            )->getRowArray();
            if ($customer === null) {
                throw new DomainException('حساب مشتری معتبر نیست.');
            }

            // Recheck after locking the customer row: concurrent requests
            // with the same idempotency key must reuse the first order.
            $existing = $this->db->table('orders')->where('customer_id', $customerId)
                ->where('idempotency_key', $key)->get()->getRowArray();
            if ($existing !== null) {
                $this->db->transCommit();
                unset($existing['idempotency_key']);
                return $existing;
            }

            $lines = [];
            $subtotal = 0;
            ksort($normalized, SORT_NUMERIC);
            foreach ($normalized as $id => $qty) {
                $row = $this->db->query(
                    'SELECT v.id, v.product_id, v.sku, v.size_label, v.color_name, v.price_toman, p.name, p.price_toman AS product_price, i.quantity AS stock
                     FROM product_variants v
                     INNER JOIN products p ON p.id = v.product_id
                     INNER JOIN inventory_items i ON i.variant_id = v.id
                     WHERE v.id = ? AND v.is_active = 1 AND p.status = ? AND p.deleted_at IS NULL
                     FOR UPDATE',
                    [$id, 'active']
                )->getRowArray();
                if ($row === null || (int) $row['stock'] < $qty) {
                    throw new DomainException('موجودی یکی از محصولات کافی نیست.');
                }
                $price = (int) ($row['price_toman'] ?? $row['product_price']);
                if ($price < 1 || $price > intdiv(PHP_INT_MAX, max(10, $qty))) {
                    throw new DomainException('قیمت یکی از محصولات معتبر نیست.');
                }
                $lineTotal = $price * $qty;
                if ($subtotal > intdiv(PHP_INT_MAX, 10) - $lineTotal - 1000000000) {
                    throw new DomainException('مبلغ سفارش از حد مجاز پرداخت بیشتر است.');
                }
                $subtotal += $lineTotal;
                $lines[] = [
                    'product_id' => (int) $row['product_id'], 'variant_id' => $id,
                    'product_name' => $row['name'], 'sku' => $row['sku'],
                    'size_label' => $row['size_label'], 'color_name' => $row['color_name'],
                    'quantity' => $qty, 'unit_price_toman' => $price,
                    'subtotal_toman' => $lineTotal,
                ];
            }
            // Authoritative fee is computed from DB AFTER live product prices,
            // while the order transaction is still open.
            $delivery = (new ShippingService($this->db))->quote(
                $shippingMethod, $subtotal, $recipient['province']
            );
            $shipping = (int) $delivery['shipping_toman'];
            $number = 'GR-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(6)));
            $now = date('Y-m-d H:i:s');
            $order = [
                'customer_id' => $customerId, 'order_number' => $number, 'idempotency_key' => $key,
                'receiver_name' => $recipient['receiver_name'],
                'receiver_mobile' => $recipient['receiver_mobile'],
                'province' => $recipient['province'], 'city' => $recipient['city'],
                'address' => $recipient['address'], 'postal_code' => $recipient['postal_code'],
                'shipping_method' => $delivery['code'], 'shipping_title' => $delivery['title'], 'subtotal_toman' => $subtotal,
                'shipping_toman' => $shipping, 'total_toman' => $subtotal + $shipping,
                'payment_status' => 'unpaid', 'order_status' => 'pending_payment',
                'created_at' => $now, 'updated_at' => $now,
            ];
            if (! $this->db->table('orders')->insert($order)) {
                throw new RuntimeException('ثبت سفارش ناموفق بود.');
            }
            $orderId = (int) $this->db->insertID();
            foreach ($lines as $line) {
                if (! $this->db->table('order_items')->insert(['order_id' => $orderId] + $line)) {
                    throw new RuntimeException('ثبت اقلام سفارش ناموفق بود.');
                }
            }
            // Queue a notification within the order transaction; never send
            // an SMS before the order commit or during a database lock.
            (new SmsNotificationService($this->db))->queueForOrder($orderId, 'order_placed');
            if ($this->db->transStatus() === false) {
                throw new RuntimeException('تراکنش ثبت سفارش ناموفق بود.');
            }
            $this->db->transCommit();
            $result = ['id' => $orderId] + $order;
            unset($result['idempotency_key']);
            return $result;
        } catch (Throwable $exception) {
            $this->db->transRollback();
            throw $exception;
        }
    }

    public function listForCustomer(int $customerId, int $limit = 20): array
    {
        return $this->db->table('orders')->select('id, order_number, total_toman, payment_status, order_status, created_at')
            ->where('customer_id', $customerId)->orderBy('id', 'DESC')
            ->limit(min(max($limit, 1), 50))->get()->getResultArray();
    }

    public function findForCustomer(int $customerId, int $orderId): ?array
    {
        $order = $this->db->table('orders')->where('customer_id', $customerId)
            ->where('id', $orderId)->get()->getRowArray();
        if ($order === null) {
            return null;
        }
        $order['items'] = $this->db->table('order_items')->where('order_id', $orderId)
            ->orderBy('id', 'ASC')->get()->getResultArray();
        unset($order['idempotency_key']);
        return $order;
    }
}
