<?php

namespace App\Controllers;

use App\Services\Payments\PaymentGateway;
use App\Services\Payments\PaymentSettlementService;
use App\Services\Payments\ZibalGateway;
use App\Services\Payments\ZarinpalGateway;
use CodeIgniter\API\ResponseTrait;
use DomainException;
use Throwable;

/**
 * Payment providers redirect the shopper here using GET.
 * Callback parameters are untrusted hints, never payment evidence.
 */
final class PaymentCallbackController extends BaseController
{
    use ResponseTrait;

    public function handle()
    {
        $this->response->setHeader('Cache-Control', 'no-store, private');
        $authority = $this->request->getGet('Authority');
        $status = $this->request->getGet('Status');
        if (! is_string($authority) || ! is_string($status)
            || ! in_array($status, ['OK', 'NOK'], true)) {
            return $this->result(false, 'پارامترهای بازگشت پرداخت نامعتبر است.', 400);
        }
        return $this->verifyCallback(new ZarinpalGateway(), $authority, $status === 'OK');
    }

    public function zibal()
    {
        $this->response->setHeader('Cache-Control', 'no-store, private');
        $trackId = $this->request->getGet('trackId');
        $success = $this->request->getGet('success');
        if (! is_string($trackId) || ! in_array($success, ['0', '1'], true)) {
            return $this->result(false, 'پارامترهای بازگشت پرداخت نامعتبر است.', 400);
        }
        return $this->verifyCallback(new ZibalGateway(), $trackId, $success === '1');
    }

    private function verifyCallback(PaymentGateway $gateway, string $authority, bool $success)
    {
        try {
            $authority = $gateway::validateAuthority($authority);
        } catch (\InvalidArgumentException) {
            return $this->result(false, 'شناسه پرداخت نامعتبر است.', 400);
        }

        // A failed callback is not proof of payment failure; do not make irreversible
        // changes to order state based on client-controlled query parameters.
        if (! $success) {
            return $this->result(false, 'پرداخت توسط درگاه تأیید نشده است.', 202);
        }
        $throttler = service('throttler');
        if (! $throttler->check('gereh_payment_callback_' . hash('sha256', $gateway->provider() . ':' . $authority), 8, 300)) {
            return $this->result(false, 'تعداد تلاش‌های بررسی پرداخت زیاد است.', 429);
        }

        try {
            $receipt = (new PaymentSettlementService(null, $gateway))->settle($authority);
            return $this->respond([
                'success' => true, 'data' => $receipt,
                'meta' => new \stdClass(), 'message' => 'وضعیت پرداخت تأیید شد.', 'errors' => null,
            ]);
        } catch (DomainException $exception) {
            // Do not echo internal financial state or customer details.
            log_message('warning', 'Gateway callback requires review: {type}', ['type' => get_class($exception)]);
            return $this->result(false, 'پرداخت در حال بررسی است؛ در صورت کسر وجه با پشتیبانی تماس بگیرید.', 409);
        } catch (Throwable $exception) {
            log_message('error', 'Gateway callback failed: {type}', ['type' => get_class($exception)]);
            return $this->result(false, 'بررسی پرداخت موقتاً در دسترس نیست.', 503);
        }
    }

    private function result(bool $success, string $message, int $status)
    {
        return $this->respond([
            'success' => $success, 'data' => null,
            'meta' => new \stdClass(), 'message' => $message, 'errors' => null,
        ], $status);
    }
}
