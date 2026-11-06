<?php

namespace App\Services\Payments;

use CodeIgniter\Database\BaseConnection;
use Config\Database;
use DomainException;
use RuntimeException;
use Throwable;

/**
 * The only settlement entry point. The gateway is ALWAYS verified server-side.
 * A callback query string is never proof of payment.
 *
 * No public endpoint calls this until payment initiation and order checkout
 * have been end-to-end tested.
 */
final class PaymentSettlementService
{
    private BaseConnection $db;

    public function __construct(
        ?BaseConnection $db = null,
        private ?ZarinpalGateway $gateway = null
    ) {
        $this->db = $db ?? Database::connect();
        $this->gateway ??= new ZarinpalGateway();
    }

    public function settle(string $authority): array
    {
        $authority = ZarinpalGateway::validateAuthority($authority);
        $payment = $this->db->table('payment_transactions')
            ->where('provider', 'zarinpal')->where('authority', $authority)
            ->get()->getRowArray();
        if ($payment === null) {
            throw new DomainException('تراکنش پرداخت پیدا نشد.');
        }
        if ($payment['status'] === 'paid') {
            return ['order_id' => (int) $payment['order_id'], 'status' => 'paid', 'already_settled' => true];
        }
        if ($payment['status'] !== 'initiated') {
            throw new DomainException('تراکنش در وضعیت قابل تأیید نیست.');
        }

        // External network access must happen OUTSIDE the SQL transaction.
        $verification = $this->gateway->verify((int) $payment['amount_toman'], $authority);
        if ($verification['code'] !== 100) {
            // Code 101 means "already verified by gateway", not permission to
            // invent an unrecorded local settlement.
            throw new DomainException('پرداخت قبلاً در درگاه تأیید شده؛ نیازمند تطبیق دستی است.');
        }
        $refId = $verification['ref_id'];
        if ($refId === null || preg_match('/^[0-9]{1,120}$/D', $refId) !== 1) {
            throw new RuntimeException('شناسه پیگیری درگاه نامعتبر است.');
        }

        $this->db->transBegin();
        try {
            $lockedPayment = $this->db->query(
                'SELECT * FROM payment_transactions WHERE id = ? FOR UPDATE',
                [(int) $payment['id']]
            )->getRowArray();
            if ($lockedPayment === null || ! hash_equals((string) $lockedPayment['authority'], $authority)) {
                throw new DomainException('تراکنش تغییر کرده است.');
            }
            if ($lockedPayment['status'] === 'paid') {
                $this->db->transCommit();
                return ['order_id' => (int) $lockedPayment['order_id'], 'status' => 'paid', 'already_settled' => true];
            }
            if ($lockedPayment['status'] !== 'initiated') {
                throw new DomainException('وضعیت تراکنش تغییر کرده است.');
            }
            $orderId = (int) $lockedPayment['order_id'];
            $order = $this->db->query('SELECT * FROM orders WHERE id = ? FOR UPDATE', [$orderId])->getRowArray();
            if ($order === null || $order['payment_status'] !== 'unpaid'
                || $order['order_status'] !== 'pending_payment'
                || (int) $order['total_toman'] !== (int) $lockedPayment['amount_toman']) {
                throw new DomainException('مبلغ یا وضعیت سفارش برای تسویه معتبر نیست.');
            }
            $items = $this->db->table('order_items')->where('order_id', $orderId)
                ->orderBy('variant_id', 'ASC')->get()->getResultArray();
            if ($items === []) {
                throw new DomainException('سفارش بدون کالا قابل تسویه نیست.');
            }
            $now = date('Y-m-d H:i:s');
            foreach ($items as $item) {
                $variantId = (int) $item['variant_id'];
                $quantity = (int) $item['quantity'];
                if ($quantity < 1) {
                    throw new DomainException('تعداد کالای سفارش نامعتبر است.');
                }
                // Conditional decrement provides the last line of defense even
                // if another checkout adjusted inventory between verification and settlement.
                $changed = $this->db->table('inventory_items')
                    ->where('variant_id', $variantId)
                    ->where('quantity >=', $quantity)
                    ->set('quantity', 'quantity - ' . $quantity, false)
                    ->set('updated_at', $now)
                    ->update();
                if (! $changed || $this->db->affectedRows() !== 1) {
                    throw new DomainException('موجودی برای تسویه کافی نیست؛ نیاز به رسیدگی و استرداد وجه است.');
                }
                if (! $this->db->table('inventory_movements')->insert([
                    'variant_id' => $variantId,
                    'quantity_delta' => -$quantity,
                    'movement_type' => 'sale',
                    'reference_type' => 'order',
                    'reference_id' => $orderId,
                    'note' => 'ZarinPal verified settlement',
                    'created_at' => $now,
                ])) {
                    throw new RuntimeException('ثبت گردش موجودی ناموفق بود.');
                }
            }

            if (! $this->db->table('payment_transactions')->where('id', $lockedPayment['id'])->update([
                'status' => 'paid', 'ref_id' => $refId, 'verified_at' => $now,
            ]) || ! $this->db->table('orders')->where('id', $orderId)->update([
                'payment_status' => 'paid', 'order_status' => 'processing',
                'updated_at' => $now,
            ]) || $this->db->transStatus() === false) {
                throw new RuntimeException('ثبت نهایی پرداخت ناموفق بود.');
            }
            // Queue once after verified settlement; SMS is dispatched
            // outside the payment transaction by the outbox worker.
            $notifier = new \App\Services\SmsNotificationService($this->db);
            $notifier->queueForOrder($orderId, 'payment_success');
            $notifier->queueForOrder($orderId, 'order_processing');
            if ($this->db->transStatus() === false) {
                throw new RuntimeException('ثبت اعلان‌های پرداخت ناموفق بود.');
            }
            $this->db->transCommit();
            return ['order_id' => $orderId, 'status' => 'paid', 'already_settled' => false];
        } catch (Throwable $exception) {
            $this->db->transRollback();
            // Provider returned code 100, but local settlement failed.
            // Mark the payment for manual reconciliation without claiming
            // fulfillment or destroying the provider reference.
            try {
                $this->db->transBegin();
                $this->db->table('payment_transactions')
                    ->where('id', $payment['id'])->where('status', 'initiated')
                    ->update(['status' => 'review_required', 'ref_id' => $refId, 'verified_at' => date('Y-m-d H:i:s')]);
                if ($this->db->affectedRows() === 1) {
                    $this->db->table('orders')->where('id', $payment['order_id'])
                        ->where('payment_status', 'unpaid')->update([
                            'payment_status' => 'review_required',
                            'order_status' => 'payment_review',
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                }
                if ($this->db->transStatus() === false) {
                    throw new RuntimeException('Failed to persist settlement review state.');
                }
                (new \App\Services\SmsNotificationService($this->db))
                    ->queueForOrder((int) $payment['order_id'], 'payment_review');
                if ($this->db->transStatus() === false) {
                    throw new RuntimeException('Failed to persist settlement review notification.');
                }
                $this->db->transCommit();
            } catch (Throwable $recordFailure) {
                $this->db->transRollback();
                log_message('critical', 'Could not persist payment review for {id}: {type}', [
                    'id' => (int) $payment['id'], 'type' => get_class($recordFailure),
                ]);
            }
            log_message('critical', 'ZarinPal settlement needs review for transaction {id}: {type}', [
                'id' => (int) $payment['id'], 'type' => get_class($exception),
            ]);
            throw $exception;
        }
    }
}
