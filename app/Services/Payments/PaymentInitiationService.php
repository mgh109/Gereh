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

    public function __construct(?BaseConnection $db = null, private ?PaymentGateway $gateway = null, bool $useTorobPay = false)
    {
        $this->db = $db ?? Database::connect();
        if ($useTorobPay && ! TorobPayClient::enabled()) {
            throw new DomainException('پرداخت اعتباری ترب‌پی فعال نیست.');
        }
        $this->gateway ??= match ($useTorobPay ? 'torobpay' : (string) env('GEREH_PAYMENT_PROVIDER', 'zarinpal')) {
            'torobpay' => new TorobPayGateway($this->db),
            'zarinpal' => new ZarinpalGateway(),
            'zibal' => new ZibalGateway(),
            default => throw new RuntimeException('درگاه پرداخت معتبر تنظیم نشده است.'),
        };
    }

    public function initiate(int $customerId, int $orderId): array
    {
        if ($customerId < 1 || $orderId < 1) {
            throw new DomainException('سفارش یا مشتری معتبر نیست.');
        }

        $this->db->transBegin();
        try {
            $order = $this->db->query(
                'SELECT id, customer_id, order_number, receiver_name, receiver_mobile, province, city,
                        address, postal_code, shipping_toman, total_toman, payment_status, order_status
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
                ->where('order_id', $orderId)
                ->whereIn('status', ['requesting', 'initiated'])
                ->orderBy('id', 'DESC')->get()->getRowArray();
            if ($attempt !== null) {
                if ($attempt['status'] === 'initiated' && ! empty($attempt['authority'])) {
                    $gateway = match ($attempt['provider']) {
                        $this->gateway->provider() => $this->gateway,
                        'zarinpal' => new ZarinpalGateway(),
                        'zibal' => new ZibalGateway(),
                        'torobpay' => new TorobPayGateway($this->db),
                        default => throw new DomainException('درگاه پرداخت قبلی معتبر نیست.'),
                    };
                    $this->db->transCommit();
                    return [
                        'authority' => $attempt['authority'],
                        'redirect_url' => $gateway->startUrl($attempt['authority']),
                        'reused' => true,
                    ];
                }
                throw new DomainException('درخواست پرداخت قبلی در حال انجام است.');
            }
            $authority = $this->gateway instanceof TorobPayGateway ? 'gereh-' . bin2hex(random_bytes(16)) : null;
            $now = date('Y-m-d H:i:s');
            if (! $this->db->table('payment_transactions')->insert([
                'order_id' => $orderId,
                'provider' => $this->gateway->provider(),
                'authority' => $authority,
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
            $details = [];
            if ($this->gateway instanceof TorobPayGateway) {
                try {
                    $eligible = (new TorobPayClient())->eligibility((int) $order['total_toman'])['eligible'];
                } catch (Throwable $exception) {
                    $this->releaseClaim($paymentId);
                    throw $exception;
                }
                if (! $eligible) {
                    $this->releaseClaim($paymentId);
                    throw new DomainException('خرید اعتباری برای مبلغ این سفارش در دسترس نیست.');
                }
                $result = $this->gateway->requestForOrder($order, $authority);
                $details = ['gateway_token' => $result['token'], 'gateway_redirect_url' => $result['redirect_url']];
                unset($result['token']);
            } else {
                $result = $this->gateway->request(
                    (int) $order['total_toman'],
                    'سفارش گره ' . $order['order_number'],
                    $order['receiver_mobile']
                );
            }
            $saved = $this->db->table('payment_transactions')
                ->where('id', $paymentId)->where('status', 'requesting')
                ->update(['authority' => $result['authority'], 'status' => 'initiated'] + $details);
            if (! $saved || $this->db->affectedRows() !== 1) {
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

    private function releaseClaim(int $paymentId): void
    {
        $this->db->table('payment_transactions')->where('id', $paymentId)->where('status', 'requesting')
            ->update(['status' => 'failed']);
    }
}
