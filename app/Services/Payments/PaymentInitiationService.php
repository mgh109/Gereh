<?php

namespace App\Services\Payments;

use CodeIgniter\Database\BaseConnection;
use Config\Database;
use DomainException;
use RuntimeException;
use Throwable;

/**
 * Starts payment only for an existing, customer-owned, unpaid order.
 * A database claim avoids duplicate concurrent gateway requests.
 */
final class PaymentInitiationService
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null, private ?ZarinpalGateway $gateway = null)
    {
        $this->db = $db ?? Database::connect();
        $this->gateway ??= new ZarinpalGateway();
    }

    public function initiate(int $customerId, int $orderId): array
    {
        if ($customerId < 1 || $orderId < 1) {
            throw new DomainException('سفارش یا مشتری معتبر نیست.');
        }

        $this->db->transBegin();
        try {
            $order = $this->db->query(
                'SELECT id, order_number, receiver_mobile, total_toman, payment_status, order_status
                 FROM orders WHERE id = ? AND customer_id = ? FOR UPDATE',
                [$orderId, $customerId]
            )->getRowArray();
            if ($order === null) {
                throw new DomainException('سفارش پیدا نشد.');
            }
            if ($order['payment_status'] !== 'unpaid' || $order['order_status'] !== 'pending_payment') {
                throw new DomainException('سفارش قابل پرداخت نیست.');
            }
            $attempt = $this->db->table('payment_transactions')
                ->where('order_id', $orderId)->where('provider', 'zarinpal')
                ->whereIn('status', ['requesting', 'initiated'])
                ->orderBy('id', 'DESC')->get()->getRowArray();
            if ($attempt !== null) {
                if ($attempt['status'] === 'initiated' && ! empty($attempt['authority'])) {
                    $this->db->transCommit();
                    return [
                        'authority' => $attempt['authority'],
                        'redirect_url' => $this->gateway->startUrl($attempt['authority']),
                        'reused' => true,
                    ];
                }
                throw new DomainException('درخواست پرداخت قبلی در حال انجام است.');
            }
            $now = date('Y-m-d H:i:s');
            if (! $this->db->table('payment_transactions')->insert([
                'order_id' => $orderId,
                'provider' => 'zarinpal',
                'amount_toman' => (int) $order['total_toman'],
                'status' => 'requesting',
                'created_at' => $now,
            ])) {
                throw new RuntimeException('ثبت درخواست پرداخت ناموفق بود.');
            }
            $paymentId = (int) $this->db->insertID();
            if ($this->db->transStatus() === false) {
                throw new RuntimeException('ثبت درخواست پرداخت کامل نشد.');
            }
            $this->db->transCommit();
        } catch (Throwable $exception) {
            $this->db->transRollback();
            throw $exception;
        }

        // Never hold DB row locks during the external gateway request.
        try {
            $result = $this->gateway->request(
                (int) $order['total_toman'],
                'سفارش گره ' . $order['order_number'],
                $order['receiver_mobile']
            );
            if (! $this->db->table('payment_transactions')
                ->where('id', $paymentId)->where('status', 'requesting')
                ->update(['authority' => $result['authority'], 'status' => 'initiated'])) {
                throw new RuntimeException('ذخیره شناسه درگاه ناموفق بود؛ نیازمند بررسی دستی است.');
            }
            return $result + ['reused' => false];
        } catch (Throwable $exception) {
            // A gateway request can succeed while its DB response fails.
            // Do not automatically retry this attempt or create a second charge path.
            log_message('critical', 'Payment initiation needs review for transaction {id}: {type}', [
                'id' => $paymentId, 'type' => get_class($exception),
            ]);
            throw $exception;
        }
    }
}
