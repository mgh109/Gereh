<?php

namespace App\Controllers\Api\V1;

use App\Controllers\BaseController;
use App\Services\OrderService;
use CodeIgniter\API\ResponseTrait;
use Throwable;

class OrderController extends BaseController
{
    use ResponseTrait;

    private function customerId(): int
    {
        // Never accept a customer ID from query strings, body, or headers.
        return (int) (session()->get('customer_id') ?? 0);
    }

    private function envelope($data, int $status = 200)
    {
        $this->response->setHeader('Cache-Control', 'no-store, private');
        return $this->respond([
            'success' => true, 'data' => $data, 'meta' => new \stdClass(),
            'message' => null, 'errors' => null,
        ], $status);
    }

    private function error(string $message, int $status)
    {
        $this->response->setHeader('Cache-Control', 'no-store, private');
        return $this->respond([
            'success' => false, 'data' => null, 'meta' => new \stdClass(),
            'message' => $message, 'errors' => null,
        ], $status);
    }

    public function index()
    {
        $customerId = $this->customerId();
        if ($customerId < 1) {
            return $this->error('برای مشاهده سفارش‌ها وارد شوید.', 401);
        }
        try {
            return $this->envelope((new OrderService())->listForCustomer($customerId));
        } catch (Throwable $exception) {
            log_message('error', 'Orders list: {message}', ['message' => $exception->getMessage()]);
            return $this->error('دریافت سفارش‌ها امکان‌پذیر نیست.', 500);
        }
    }

    public function show(int $id)
    {
        $customerId = $this->customerId();
        if ($customerId < 1) {
            return $this->error('برای مشاهده سفارش وارد شوید.', 401);
        }
        try {
            $order = (new OrderService())->findForCustomer($customerId, $id);
            return $order === null ? $this->error('سفارش پیدا نشد.', 404) : $this->envelope($order);
        } catch (Throwable $exception) {
            log_message('error', 'Order detail: {message}', ['message' => $exception->getMessage()]);
            return $this->error('دریافت سفارش امکان‌پذیر نیست.', 500);
        }
    }

    public function checkout()
    {
        // Feature flag defaults OFF. Do not activate on production until
        // real provider, callback and stock/concurrency tests pass.
        if (! filter_var(env('GEREH_CHECKOUT_ENABLED', false), FILTER_VALIDATE_BOOLEAN)) {
            return $this->error('پرداخت تا زمان تأیید درگاه واقعی غیرفعال است.', 503);
        }
        $customerId = $this->customerId();
        if ($customerId < 1) {
            return $this->error('برای ثبت سفارش ابتدا وارد شوید.', 401);
        }
        $data = $this->request->getJSON(true);
        $key = $this->request->getHeaderLine('Idempotency-Key');
        if (! is_array($data) || ! is_array($data['recipient'] ?? null) || $key === '') {
            return $this->error('مشخصات گیرنده و کلید یکتای درخواست الزامی هستند.', 422);
        }
        if (($data['shipping_method'] ?? 'standard') !== 'standard') {
            return $this->error('روش ارسال انتخاب‌شده فعلاً پشتیبانی نمی‌شود.', 422);
        }
        try {
            $order = (new OrderService())->create(
                $customerId,
                (array) session()->get('store_cart'),
                $data['recipient'],
                $key
            );
            $payment = (new \App\Services\Payments\PaymentInitiationService())->initiate(
                $customerId,
                (int) $order['id']
            );
            return $this->envelope([
                'order_id' => (int) $order['id'],
                'order_number' => $order['order_number'],
                'total_toman' => (int) $order['total_toman'],
                'payment' => $payment,
            ], 201);
        } catch (\InvalidArgumentException $exception) {
            return $this->error($exception->getMessage(), 422);
        } catch (\DomainException $exception) {
            return $this->error($exception->getMessage(), 409);
        } catch (\Throwable $exception) {
            log_message('error', 'Checkout failed: {type}', ['type' => get_class($exception)]);
            return $this->error('ثبت سفارش یا ارتباط با درگاه ممکن نیست.', 503);
        }
    }
}
