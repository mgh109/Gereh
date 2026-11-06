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
        // Deliberately disabled until verified OTP and payment initiation are connected.
        // Do not allow unpaid orders to be created without a working payment path.
        return $this->error('ثبت سفارش تا اتصال احراز هویت و درگاه پرداخت فعال نیست.', 503);
    }
}
