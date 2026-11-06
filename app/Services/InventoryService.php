<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use Config\Database;
use RuntimeException;
use Throwable;

class InventoryService
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? Database::connect();
    }

    /**
     * Apply a signed stock adjustment and persist its audit movement atomically.
     *
     * @return array{variant_id:int, previous_quantity:int, quantity:int, delta:int}
     */
    public function adjust(
        int $variantId,
        int $delta,
        string $movementType,
        ?string $note = null,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): array {
        if ($variantId < 1 || $delta === 0) {
            throw new RuntimeException('شناسه تنوع محصول معتبر نیست یا مقدار تغییر موجودی صفر است.');
        }

        $allowedTypes = ['initial', 'purchase', 'sale', 'return', 'correction', 'cancellation'];
        if (! in_array($movementType, $allowedTypes, true)) {
            throw new RuntimeException('نوع گردش موجودی معتبر نیست.');
        }

        $this->db->transBegin();

        try {
            $variant = $this->db->query(
                'SELECT id FROM product_variants WHERE id = ? AND is_active = 1 FOR UPDATE',
                [$variantId]
            )->getRowArray();

            if ($variant === null) {
                throw new RuntimeException('تنوع محصول پیدا نشد یا غیرفعال است.');
            }

            $inventory = $this->db->query(
                'SELECT id, quantity FROM inventory_items WHERE variant_id = ? FOR UPDATE',
                [$variantId]
            )->getRowArray();

            if ($inventory === null) {
                if ($delta < 0) {
                    throw new RuntimeException('برای این تنوع هنوز موجودی ثبت نشده است.');
                }

                $this->db->table('inventory_items')->insert([
                    'variant_id' => $variantId,
                    'quantity' => 0,
                    'low_stock_threshold' => 2,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                $inventory = [
                    'id' => $this->db->insertID(),
                    'quantity' => 0,
                ];
            }

            $previous = (int) $inventory['quantity'];
            $next = $previous + $delta;

            if ($next < 0) {
                throw new RuntimeException('موجودی کافی نیست؛ عملیات باعث موجودی منفی می‌شود.');
            }

            $this->db->table('inventory_items')
                ->where('id', $inventory['id'])
                ->update([
                    'quantity' => $next,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

            $this->db->table('inventory_movements')->insert([
                'variant_id' => $variantId,
                'quantity_delta' => $delta,
                'movement_type' => $movementType,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'note' => $note,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            if ($this->db->transStatus() === false) {
                throw new RuntimeException('ثبت گردش موجودی با خطا مواجه شد.');
            }

            $this->db->transCommit();

            return [
                'variant_id' => $variantId,
                'previous_quantity' => $previous,
                'quantity' => $next,
                'delta' => $delta,
            ];
        } catch (Throwable $exception) {
            $this->db->transRollback();
            throw $exception;
        }
    }

    /**
     * Absolute inventory correction performed under a database row lock.
     * Every non-zero adjustment is recorded in inventory_movements.
     */
    public function setAbsolute(int $variantId, int $quantity, string $note = 'Admin stock correction'): array
    {
        if ($variantId < 1 || $quantity < 0 || $quantity > 100000000) {
            throw new RuntimeException('شناسه تنوع یا مقدار موجودی نامعتبر است.');
        }
        $this->db->transBegin();
        try {
            $variant = $this->db->query(
                'SELECT id FROM product_variants WHERE id = ? FOR UPDATE',
                [$variantId]
            )->getRowArray();
            if ($variant === null) {
                throw new RuntimeException('تنوع محصول پیدا نشد.');
            }
            $inventory = $this->db->query(
                'SELECT id, quantity FROM inventory_items WHERE variant_id = ? FOR UPDATE',
                [$variantId]
            )->getRowArray();
            $previous = $inventory ? (int) $inventory['quantity'] : 0;
            $now = date('Y-m-d H:i:s');
            if ($inventory === null) {
                $this->db->table('inventory_items')->insert([
                    'variant_id' => $variantId,
                    'quantity' => $quantity,
                    'low_stock_threshold' => 2,
                    'updated_at' => $now,
                ]);
            } else {
                $this->db->table('inventory_items')->where('id', $inventory['id'])
                    ->update(['quantity' => $quantity, 'updated_at' => $now]);
            }
            $delta = $quantity - $previous;
            if ($delta !== 0) {
                $this->db->table('inventory_movements')->insert([
                    'variant_id' => $variantId,
                    'quantity_delta' => $delta,
                    'movement_type' => 'correction',
                    'reference_type' => 'admin',
                    'reference_id' => (int) (session()->get('admin_id') ?? 0) ?: null,
                    'note' => mb_substr($note, 0, 500),
                    'created_at' => $now,
                ]);
            }
            if ($this->db->transStatus() === false) {
                throw new RuntimeException('ثبت موجودی و گردش انبار ناموفق بود.');
            }
            $this->db->transCommit();
            return ['variant_id' => $variantId, 'previous_quantity' => $previous, 'quantity' => $quantity, 'delta' => $delta];
        } catch (Throwable $exception) {
            $this->db->transRollback();
            throw $exception;
        }
    }

}
