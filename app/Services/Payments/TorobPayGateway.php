<?php

namespace App\Services\Payments;

use CodeIgniter\Database\BaseConnection;
use Config\Database;
use DomainException;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

final class TorobPayGateway implements PaymentGateway
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null, private ?TorobPayClient $client = null)
    {
        $this->db = $db ?? Database::connect();
        $this->client ??= new TorobPayClient();
    }

    public function provider(): string
    {
        return 'torobpay';
    }

    public static function validateAuthority(string $authority): string
    {
        if (preg_match('/^gereh-[a-f0-9]{32}$/D', $authority) !== 1) {
            throw new InvalidArgumentException('شناسه پرداخت ترب‌پی نامعتبر است.');
        }
        return $authority;
    }

    public function startUrl(string $authority): string
    {
        $url = $this->transaction($authority)['gateway_redirect_url'] ?? '';
        return self::validateRedirect($url);
    }

    private static function validateRedirect(string $url): string
    {
        $parts = parse_url($url);
        $host = strtolower($parts['host'] ?? '');
        if (! filter_var($url, FILTER_VALIDATE_URL) || ($parts['scheme'] ?? null) !== 'https'
            || ($host !== 'torobpay.com' && ! str_ends_with($host, '.torobpay.com'))
            || isset($parts['user']) || isset($parts['pass'])
            || ($parts['port'] ?? 443) !== 443) {
            throw new TorobPayException('redirect_invalid');
        }
        return $url;
    }

    public function request(int $amountToman, string $description, ?string $mobile = null): array
    {
        throw new RuntimeException('برای پرداخت ترب‌پی اطلاعات سفارش الزامی است.');
    }

    public function requestForOrder(array $order, string $authority): array
    {
        self::validateAuthority($authority);
        $items = $this->db->table('order_items')->where('order_id', $order['id'])->get()->getResultArray();
        $mobile = $this->db->table('customers')->select('mobile')->where('id', $order['customer_id'])
            ->get()->getRowArray()['mobile'] ?? null;
        if ($items === [] || ! is_string($mobile) || $mobile === '') {
            throw new DomainException('اطلاعات کالاها یا شماره ثبت‌نام مشتری کامل نیست.');
        }

        $amount = ZarinpalGateway::tomanToRial((int) $order['total_toman']);
        $callback = trim((string) env('TOROBPAY_CALLBACK_URL', site_url('payment/torobpay/callback')));
        $result = $this->client->createPayment([
            'amount' => $amount,
            'paymentMethodTypeDto' => (string) env('TOROBPAY_PAYMENT_METHOD', 'INSTALLMENT'),
            'returnURL' => $callback,
            'transactionId' => $authority,
            'mobile' => $mobile,
            'registration_phone_number' => $mobile,
            'address' => $order['address'],
            'postalCode' => $order['postal_code'],
            'customer_full_name' => $order['receiver_name'],
            'city' => $order['city'],
            'province' => $order['province'],
            'cartList' => [[
                'cartId' => (string) $order['order_number'],
                'totalAmount' => $amount,
                'shippingAmount' => (int) $order['shipping_toman'] * 10,
                'isShipmentIncluded' => true,
                'cartItems' => array_map(static fn (array $item): array => [
                    'id' => (string) $item['variant_id'],
                    'name' => $item['product_name'],
                    'count' => (int) $item['quantity'],
                    'amount' => ZarinpalGateway::tomanToRial((int) $item['unit_price_toman']),
                    'category' => 'other',
                    'commissionType' => 0,
                ], $items),
            ]],
        ]);

        return [
            'authority' => $authority,
            'redirect_url' => self::validateRedirect($result['paymentPageUrl']),
            'token' => $result['paymentToken'],
        ];
    }

    public function verify(int $amountToman, string $authority): array
    {
        $payment = $this->transaction($authority);
        $token = (string) ($payment['gateway_token'] ?? '');
        $status = $this->status($token, $authority, $amountToman);
        if ($status === 'REVERT') {
            $this->db->table('payment_transactions')->where('id', $payment['id'])->where('status', 'initiated')
                ->update(['status' => 'failed']);
            throw new DomainException('پرداخت ترب‌پی لغو شد. می‌توانید دوباره پرداخت کنید.');
        }
        if ($status !== 'SETTLE') {
            try {
                if ($status === 'PENDING') {
                    $this->checkTransaction($this->client->verify($token), $authority);
                }
                if (! $this->hasStock((int) $payment['order_id'])) {
                    $this->checkTransaction($this->client->revert($token), $authority);
                    $this->markFailedIfReverted($authority);
                    throw new DomainException('موجودی سفارش کافی نیست و پرداخت برگشت داده شد.');
                }
                $this->checkTransaction($this->client->settle($token), $authority);
            } catch (Throwable $exception) {
                // Recover from a concurrent callback or an interrupted gateway response.
                if ($this->status($token, $authority, $amountToman) !== 'SETTLE') {
                    throw $exception;
                }
            }
        }

        return ['code' => 100, 'ref_id' => (string) $payment['id'], 'verified' => true];
    }

    private function hasStock(int $orderId): bool
    {
        $items = $this->db->table('order_items o')
            ->select('o.quantity, i.quantity AS stock')
            ->join('inventory_items i', 'i.variant_id = o.variant_id', 'left')
            ->where('o.order_id', $orderId)->get()->getResultArray();
        foreach ($items as $item) {
            if ((int) $item['quantity'] < 1 || (int) $item['stock'] < (int) $item['quantity']) {
                return false;
            }
        }
        return $items !== [];
    }

    public function markFailedIfReverted(string $authority): void
    {
        $payment = $this->transaction($authority);
        if (($payment['status'] ?? null) === 'initiated' && $this->status(
            (string) $payment['gateway_token'],
            $authority,
            (int) $payment['amount_toman']
        ) === 'REVERT') {
            $this->db->table('payment_transactions')->where('id', $payment['id'])->where('status', 'initiated')
                ->update(['status' => 'failed']);
        }
    }

    private function transaction(string $authority): ?array
    {
        return $this->db->table('payment_transactions')->where('provider', $this->provider())
            ->where('authority', self::validateAuthority($authority))->get()->getRowArray();
    }

    private function status(string $token, string $authority, int $amountToman): string
    {
        $data = $this->client->status($token);
        $this->checkTransaction($data, $authority);
        $amount = ZarinpalGateway::tomanToRial($amountToman);
        $status = $data['status'] ?? null;
        if (! in_array($data['amount'] ?? null, [$amount, (string) $amount], true)
            || ! in_array($status, ['PENDING', 'VERIFY', 'SETTLE', 'REVERT'], true)) {
            throw new RuntimeException('وضعیت یا مبلغ پرداخت ترب‌پی معتبر نیست.');
        }
        return $status;
    }

    private function checkTransaction(array $data, string $authority): void
    {
        if (($data['transactionId'] ?? null) !== $authority) {
            throw new RuntimeException('شناسه تراکنش در پاسخ ترب‌پی مطابقت ندارد.');
        }
    }
}
