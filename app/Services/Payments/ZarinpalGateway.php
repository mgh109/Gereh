<?php

namespace App\Services\Payments;

use InvalidArgumentException;
use RuntimeException;

/**
 * ZarinPal v4 transport adapter. This class never marks an order paid.
 *
 * All internal prices are integer TOMAN; ZarinPal v4 requests use RIAL.
 * Only fixed provider hosts are contacted (no user-controlled URLs).
 */
final class ZarinpalGateway
{
    private string $merchantId;
    private string $apiBase;
    private string $paymentBase;
    private string $callbackUrl;

    public function __construct(
        ?string $merchantId = null,
        ?bool $sandbox = null,
        ?string $callbackUrl = null
    ) {
        $this->merchantId = trim($merchantId ?? (string) env('ZARINPAL_MERCHANT_ID', ''));
        $sandbox ??= filter_var(env('ZARINPAL_SANDBOX', true), FILTER_VALIDATE_BOOLEAN);
        $this->apiBase = $sandbox ? 'https://sandbox.zarinpal.com' : 'https://api.zarinpal.com';
        $this->paymentBase = $sandbox ? 'https://sandbox.zarinpal.com' : 'https://www.zarinpal.com';
        $this->callbackUrl = trim($callbackUrl ?? (string) env('ZARINPAL_CALLBACK_URL', ''));
    }

    public static function tomanToRial(int $amountToman): int
    {
        if ($amountToman < 1 || $amountToman > intdiv(PHP_INT_MAX, 10)) {
            throw new InvalidArgumentException('مبلغ پرداخت معتبر نیست.');
        }

        return $amountToman * 10;
    }

    public static function validateAuthority(string $authority): string
    {
        if (preg_match('/^[a-zA-Z0-9_-]{20,120}$/D', $authority) !== 1) {
            throw new InvalidArgumentException('شناسه پرداخت معتبر نیست.');
        }

        return $authority;
    }

    public function startUrl(string $authority): string
    {
        return $this->paymentBase . '/pg/StartPay/' . rawurlencode(self::validateAuthority($authority));
    }

    public function request(int $amountToman, string $description, ?string $mobile = null): array
    {
        $this->assertConfigured();
        $payload = [
            'merchant_id' => $this->merchantId,
            'amount' => self::tomanToRial($amountToman),
            'callback_url' => $this->callbackUrl,
            'description' => mb_substr(trim($description), 0, 255),
        ];
        if ($mobile !== null && preg_match('/^09[0-9]{9}$/D', $mobile) === 1) {
            $payload['metadata'] = ['mobile' => $mobile];
        }
        $data = $this->post('/pg/v4/payment/request.json', $payload);
        if ((int) ($data['code'] ?? 0) !== 100 || ! is_string($data['authority'] ?? null)) {
            throw new RuntimeException('درخواست درگاه پرداخت پذیرفته نشد.');
        }
        $authority = self::validateAuthority($data['authority']);
        return ['authority' => $authority, 'redirect_url' => $this->startUrl($authority)];
    }

    /**
     * Verification is the ONLY admissible external evidence of successful payment.
     * Code 101 alone does not establish prior local settlement; caller must inspect
     * its own locked payment record before treating a duplicate callback as paid.
     */
    public function verify(int $amountToman, string $authority): array
    {
        $this->assertConfigured();
        $data = $this->post('/pg/v4/payment/verify.json', [
            'merchant_id' => $this->merchantId,
            'amount' => self::tomanToRial($amountToman),
            'authority' => self::validateAuthority($authority),
        ]);
        $code = (int) ($data['code'] ?? 0);
        if ($code !== 100 && $code !== 101) {
            throw new RuntimeException('تأیید پرداخت از سمت درگاه ناموفق بود.');
        }
        return [
            'code' => $code,
            'ref_id' => isset($data['ref_id']) ? (string) $data['ref_id'] : null,
            'verified' => $code === 100,
        ];
    }

    private function assertConfigured(): void
    {
        if (preg_match('/^[a-zA-Z0-9-]{30,64}$/D', $this->merchantId) !== 1) {
            throw new RuntimeException('شناسه پذیرنده زرین‌پال تنظیم نشده است.');
        }
        if (! str_starts_with($this->callbackUrl, 'https://')
            && !(ENVIRONMENT !== 'production' && str_starts_with($this->callbackUrl, 'http://localhost:'))) {
            throw new RuntimeException('نشانی بازگشت امن پرداخت تنظیم نشده است.');
        }
    }

    private function post(string $path, array $payload): array
    {
        try {
            $response = service('curlrequest')->post($this->apiBase . $path, [
                'headers' => ['Accept' => 'application/json'],
                'json' => $payload,
                'timeout' => 12,
                'connect_timeout' => 5,
                'http_errors' => false,
            ]);
            if ($response->getStatusCode() !== 200) {
                throw new RuntimeException('خطای ارتباط با درگاه.');
            }
            $body = json_decode((string) $response->getBody(), true);
            if (! is_array($body) || ! is_array($body['data'] ?? null)) {
                throw new RuntimeException('پاسخ درگاه معتبر نیست.');
            }
            return $body['data'];
        } catch (\Throwable $exception) {
            log_message('error', 'ZarinPal request failed: {type}', ['type' => get_class($exception)]);
            throw new RuntimeException('ارتباط امن با درگاه پرداخت برقرار نشد.', 0, $exception);
        }
    }
}
