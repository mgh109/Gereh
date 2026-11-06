<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use Config\Database;
use DomainException;
use InvalidArgumentException;

/**
 * Session-backed guest cart. Prices and stock are ALWAYS loaded from the DB,
 * never trusted from the browser or the session.
 */
class CartService
{
    public function __construct(private ?BaseConnection $db = null)
    {
        $this->db ??= Database::connect();
    }

    public function summary(): array
    {
        $stored = (array) session()->get('store_cart');
        $ids = array_values(array_filter(array_map('intval', array_keys($stored)), static fn (int $id) => $id > 0));
        if ($ids === []) {
            return ['lines' => [], 'total' => 0, 'count' => 0];
        }
        $rows = $this->db->table('product_variants v')
            ->select('v.*, p.id AS catalog_product_id, p.name AS product_name, p.slug AS product_slug, p.price_toman AS product_price, COALESCE(i.quantity, 0) AS available')
            ->join('products p', 'p.id = v.product_id', 'inner')
            ->join('inventory_items i', 'i.variant_id = v.id', 'left')
            ->whereIn('v.id', $ids)->where('v.is_active', 1)
            ->where('p.status', 'active')->where('p.deleted_at', null)
            ->get()->getResultArray();
        $lines = [];
        $total = 0;
        $count = 0;
        foreach ($rows as $row) {
            $id = (int) $row['id'];
            $available = max(0, (int) $row['available']);
            $quantity = min(20, $available, max(0, (int) ($stored[$id] ?? 0)));
            if ($quantity === 0) {
                continue;
            }
            $price = (int) ($row['price_toman'] ?? $row['product_price']);
            $variant = [
                'id' => $id, 'product_id' => (int) $row['catalog_product_id'],
                'color_name' => $row['color_name'], 'size_label' => $row['size_label'],
            ];
            $product = ['id' => (int) $row['catalog_product_id'], 'name' => $row['product_name'], 'slug' => $row['product_slug']];
            $lines[] = [
                'id' => $id, 'variant' => $variant, 'product' => $product,
                'quantity' => $quantity, 'available' => $available,
                'price' => $price, 'subtotal' => $price * $quantity,
            ];
            $count += $quantity;
            $total += $price * $quantity;
        }
        return ['lines' => $lines, 'total' => $total, 'count' => $count];
    }

    public function add(int $variantId, int $quantity): array
    {
        $this->assertValid($variantId, $quantity);
        $cart = (array) session()->get('store_cart');
        $next = (int) ($cart[$variantId] ?? 0) + $quantity;
        $this->ensureAvailable($variantId, $next);
        $cart[$variantId] = $next;
        session()->set('store_cart', $cart);
        return $this->summary();
    }

    public function setQuantity(int $variantId, int $quantity): array
    {
        if ($variantId < 1 || $quantity < 0 || $quantity > 20) {
            throw new InvalidArgumentException('تعداد باید بین صفر و ۲۰ باشد.');
        }
        $cart = (array) session()->get('store_cart');
        if (! array_key_exists($variantId, $cart)) {
            throw new DomainException('این کالا در سبد خرید وجود ندارد.');
        }
        if ($quantity === 0) {
            unset($cart[$variantId]);
        } else {
            $this->ensureAvailable($variantId, $quantity);
            $cart[$variantId] = $quantity;
        }
        session()->set('store_cart', $cart);
        return $this->summary();
    }

    public function remove(int $variantId): array
    {
        if ($variantId < 1) {
            throw new InvalidArgumentException('شناسه کالا معتبر نیست.');
        }
        $cart = (array) session()->get('store_cart');
        unset($cart[$variantId]);
        session()->set('store_cart', $cart);
        return $this->summary();
    }

    private function assertValid(int $variantId, int $quantity): void
    {
        if ($variantId < 1 || $quantity < 1 || $quantity > 20) {
            throw new InvalidArgumentException('شناسه کالا یا تعداد معتبر نیست.');
        }
    }

    private function ensureAvailable(int $variantId, int $quantity): void
    {
        if ($quantity > 20) {
            throw new InvalidArgumentException('حداکثر تعداد مجاز هر کالا ۲۰ عدد است.');
        }
        $row = $this->db->table('product_variants v')
            ->select('COALESCE(i.quantity, 0) AS available')
            ->join('products p', 'p.id = v.product_id', 'inner')
            ->join('inventory_items i', 'i.variant_id = v.id', 'left')
            ->where('v.id', $variantId)->where('v.is_active', 1)
            ->where('p.status', 'active')->where('p.deleted_at', null)
            ->get()->getRowArray();
        if ($row === null) {
            throw new DomainException('این تنوع قابل خرید نیست.');
        }
        if ($quantity > (int) $row['available']) {
            throw new DomainException('موجودی کافی نیست.');
        }
    }
}
