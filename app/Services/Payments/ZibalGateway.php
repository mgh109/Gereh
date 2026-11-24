<?php

namespace App\Services\Payments;

use InvalidArgumentException;
use RuntimeException;

final class ZibalGateway implements PaymentGateway
{
    private const BASE_URL = 'https://gateway.zibal.ir';

    private string $merchantId;
    private string $callbackUrl;

    public function __construct()
    {
        $this->merchantId = trim((string) env('ZIBAL_MERCHANT_ID', ''));
        $this->callbackUrl = trim((string) env('ZIBAL_CALLBACK_URL', ''));
    }

    public function provider(): string
    {
        return 'zibal';
    }

    public static function validateAuthority(string $authority): string
    {
        if (preg_match('/^[1-9][0-9]{0,17}$/D', $authority) !== 1
            || (string) (int) $authority !== $authority) {
            throw new InvalidArgumentException('شناسه پرداخت زیبال معتبر نیست.');
        }
        return $authority;
    }

    public function startUrl(string $authority): string
    {
        return self::BASE_URL . '/start/' . self::validateAuthority($authority);
    }

    public function request(int $amountToman, string $description, ?string $mobile = null): array
    {
        if (! filter_var($this->callbackUrl, FILTER_VALIDATE_URL)
            || parse_url($this->callbackUrl, PHP_URL_SCHEME) !== 'https') {
            throw new RuntimeException('نشانی بازگشت امن زیبال تنظیم نشده است.');
        }

        $data = $this->post('request', [
            'amount' => ZarinpalGateway::tomanToRial($amountToman),
            'callbackUrl' => $this->callbackUrl,
            'description' => mb_substr(trim($description), 0, 255),
        ]);
        $trackId = $data['trackId'] ?? null;
        if (($data['result'] ?? null) !== 100 || (! is_int($trackId) && ! is_string($trackId))) {
            throw new RuntimeException('درخواست پرداخت زیبال پذیرفته نشد.');
        }
        $authority = self::validateAuthority((string) $trackId);
        return ['authority' => $authority, 'redirect_url' => $this->startUrl($authority)];
    }

    public function verify(int $amountToman, string $authority): array
    {
        $amountRial = ZarinpalGateway::tomanToRial($amountToman);
        $data = $this->post('verify', ['trackId' => (int) self::validateAuthority($authority)]);
        $code = $data['result'] ?? null;
        if ($code === 201) {
            return ['code' => 201, 'ref_id' => null, 'verified' => false];
        }
        $refNumber = $data['refNumber'] ?? null;
        if ($code !== 100 || ! in_array($data['amount'] ?? null, [$amountRial, (string) $amountRial], true)
            || (! is_int($refNumber) && ! is_string($refNumber))) {
            throw new RuntimeException('تأیید پرداخت یا تطبیق مبلغ زیبال ناموفق بود.');
        }
        return ['code' => 100, 'ref_id' => (string) $refNumber, 'verified' => true];
    }

    private function post(string $operation, array $payload): array
    {
        if ($this->merchantId === '') {
            throw new RuntimeException('شناسه پذیرنده زیبال تنظیم نشده است.');
        }

        $response = service('curlrequest')->post(self::BASE_URL . '/v1/' . $operation, [
            'headers' => ['Accept' => 'application/json'],
            'json' => ['merchant' => $this->merchantId] + $payload,
            'timeout' => 12,
            'connect_timeout' => 5,
            'http_errors' => false,
        ]);
        if ($response->getStatusCode() !== 200) {
            throw new RuntimeException('خطای ارتباط با زیبال.');
        }

        $data = json_decode((string) $response->getBody(), true, 512, JSON_BIGINT_AS_STRING);
        if (! is_array($data)) {
            throw new RuntimeException('پاسخ زیبال معتبر نیست.');
        }
        return $data;
    }
}
