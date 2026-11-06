<?php

namespace App\Services;

use RuntimeException;

/**
 * Small provider adapter with an explicit JSON webhook contract:
 * POST {to, message}, expecting HTTP 2xx + {"success":true}.
 * No demo SMS or implicit fallback is allowed.
 */
class SmsSender
{
    public function sendOtp(string $mobile, string $code): void
    {
        $url = trim((string) env('GEREH_SMS_WEBHOOK_URL', ''));
        $token = trim((string) env('GEREH_SMS_WEBHOOK_TOKEN', ''));
        if ($url === '' || $token === '' || ! str_starts_with($url, 'https://')) {
            throw new RuntimeException('سرویس ارسال پیامک به‌صورت امن تنظیم نشده است.');
        }
        try {
            $client = service('curlrequest');
            $response = $client->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'to' => $mobile,
                    'message' => 'کد ورود گره: ' . $code,
                ],
                'timeout' => 8,
                'connect_timeout' => 4,
                'http_errors' => false,
            ]);
            $payload = json_decode((string) $response->getBody(), true);
            if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300
                || ! is_array($payload) || ($payload['success'] ?? null) !== true) {
                throw new RuntimeException('پاسخ سرویس پیامک موفق نبود.');
            }
        } catch (\Throwable $exception) {
            log_message('error', 'SMS delivery failed: {type}', ['type' => get_class($exception)]);
            throw new RuntimeException('ارسال پیامک انجام نشد.', 0, $exception);
        }
    }
}
