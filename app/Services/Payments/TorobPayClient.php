<?php

namespace App\Services\Payments;

use RuntimeException;
use InvalidArgumentException;

/**
 * Server-side transport for TorobPay CPG v1.3.31.
 * Tokens are bound to local orders by TorobPayGateway.
 */
final class TorobPayClient
{
    private const BASE = 'https://cpg.torobpay.com';
    private const PAYMENT = '/api/online/payment/v1/';
    private ?string $accessToken = null;
    private int $expiresAt = 0;

    public static function enabled(): bool
    {
        if (! filter_var(env('TOROBPAY_ENABLED', false), FILTER_VALIDATE_BOOLEAN)) {
            return false;
        }
        foreach (['TOROBPAY_CLIENT_ID', 'TOROBPAY_CLIENT_SECRET', 'TOROBPAY_USERNAME', 'TOROBPAY_PASSWORD'] as $key) {
            if (trim((string) env($key, '')) === '') {
                return false;
            }
        }
        return true;
    }

    private function token(): string
    {
        if (! self::enabled()) {
            throw new RuntimeException('ترب‌پی فعال یا پیکربندی نشده است.');
        }
        if ($this->accessToken !== null && time() < $this->expiresAt) {
            return $this->accessToken;
        }
        $basic = base64_encode((string) env('TOROBPAY_CLIENT_ID') . ':' . (string) env('TOROBPAY_CLIENT_SECRET'));
        $data = $this->send('POST', '/api/online/v1/oauth/token', 'Basic ' . $basic, [
            'username' => (string) env('TOROBPAY_USERNAME'),
            'password' => (string) env('TOROBPAY_PASSWORD'),
        ]);
        $token = $data['access_token'] ?? null;
        if (! is_string($token) || $token === '' || preg_match('/[\r\n]/', $token)) {
            throw new TorobPayException('auth_response');
        }
        $this->accessToken = $token;
        $this->expiresAt = time() + 3300;
        return $token;
    }

    public function eligibility(int $amountToman): array
    {
        $amount = ZarinpalGateway::tomanToRial($amountToman);
        if ($amount < 200000 || $amount > 1000000000) {
            return ['eligible' => false];
        }
        $data = $this->call('GET', '/api/online/offer/v1/eligible', ['amount' => $amount]);
        return [
            'eligible' => ($data['eligible'] ?? false) === true,
            'title_message' => is_string($data['title_message'] ?? null) ? $data['title_message'] : '',
            'description' => is_string($data['description'] ?? null) ? $data['description'] : '',
        ];
    }

    /**
     * Payload must be constructed exclusively from a persisted order and
     * authenticated customer data, never directly from the browser.
     */
    public function createPayment(array $payload): array
    {
        $amount = $payload['amount'] ?? null;
        if (! is_int($amount) || $amount < 200000 || $amount > 1000000000
            || ($payload['paymentMethodTypeDto'] ?? null) !== 'ONLINE_CREDIT'
            || ! is_array($payload['cartList'] ?? null) || count($payload['cartList']) !== 1
            || ! is_string($payload['returnURL'] ?? null)
            || parse_url($payload['returnURL'], PHP_URL_SCHEME) !== 'https'
            || ! filter_var($payload['returnURL'], FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('اطلاعات سفارش ترب‌پی معتبر نیست.');
        }
        $data = $this->call('POST', self::PAYMENT . 'token', $payload);
        $token = $data['paymentToken'] ?? null;
        $url = $data['paymentPageUrl'] ?? null;
        if (! is_string($token) || $token === '' || strlen($token) > 2048 || ! is_string($url)
            || ! filter_var($url, FILTER_VALIDATE_URL)
            || parse_url($url, PHP_URL_SCHEME) !== 'https'
            || parse_url($url, PHP_URL_USER) !== null
            || parse_url($url, PHP_URL_PASS) !== null) {
            throw new RuntimeException('پاسخ صدور توکن ترب‌پی معتبر نیست.');
        }
        return ['paymentToken' => $token, 'paymentPageUrl' => $url];
    }

    public function status(string $paymentToken): array
    {
        return $this->call('GET', self::PAYMENT . 'status', $this->paymentPayload($paymentToken));
    }

    public function verify(string $paymentToken): array
    {
        return $this->call('POST', self::PAYMENT . 'verify', $this->paymentPayload($paymentToken));
    }

    public function settle(string $paymentToken): array
    {
        return $this->call('POST', self::PAYMENT . 'settle', $this->paymentPayload($paymentToken));
    }

    public function revert(string $paymentToken): array
    {
        return $this->call('POST', self::PAYMENT . 'revert', $this->paymentPayload($paymentToken));
    }

    private function paymentPayload(string $token): array
    {
        if ($token === '' || strlen($token) > 2048) {
            throw new InvalidArgumentException('شناسه پرداخت ترب‌پی نامعتبر است.');
        }
        return ['paymentToken' => $token];
    }

    private function call(string $method, string $path, array $payload): array
    {
        $data = $this->send($method, $path, 'Bearer ' . $this->token(), $payload);
        if (($data['successful'] ?? false) !== true || ! is_array($data['response'] ?? null)) {
            throw new TorobPayException('api_response');
        }
        return $data['response'];
    }

    private function send(string $method, string $path, string $authorization, array $payload): array
    {
        $options = [
            'headers' => ['Authorization' => $authorization, 'Accept' => 'application/json'],
            'timeout' => 12,
            'connect_timeout' => 5,
            'http_errors' => false,
            'allow_redirects' => false,
            'verify' => true,
        ];
        if ($method === 'GET') {
            $path .= '?' . http_build_query($payload, '', '&', PHP_QUERY_RFC3986);
        } else {
            $options['json'] = $payload;
        }
        try {
            $response = service('curlrequest')->request($method, self::BASE . $path, $options);
        } catch (\CodeIgniter\HTTP\Exceptions\HTTPException $exception) {
            throw new TorobPayException('connection', (int) $exception->getCode());
        }
        $status = $response->getStatusCode();
        if ($status < 200 || $status >= 300) {
            // Never include credentials, token, customer address or provider body in logs.
            $reason = match ($status) {
                401, 403, 404 => $path === '/api/online/v1/oauth/token' ? 'auth_rejected' : 'merchant_access',
                429 => 'rate_limit',
                default => 'upstream_http',
            };
            throw new TorobPayException($reason, $status);
        }
        $data = json_decode((string) $response->getBody(), true, 512, JSON_BIGINT_AS_STRING);
        if (! is_array($data)) {
            throw new TorobPayException('invalid_response', $status);
        }
        return $data;
    }
}
