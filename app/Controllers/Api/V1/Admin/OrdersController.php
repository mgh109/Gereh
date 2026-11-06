<?php

namespace App\Controllers\Api\V1\Admin;

use App\Controllers\BaseController;
use App\Services\AdminOrdersService;
use CodeIgniter\API\ResponseTrait;
use InvalidArgumentException;
use Throwable;

final class OrdersController extends BaseController
{
    use ResponseTrait;

    private function respondEnvelope(bool $success, $data, array $meta = [], ?string $message = null, int $status = 200)
    {
        $this->response->setHeader('Cache-Control', 'no-store, private');
        return $this->respond([
            'success' => $success, 'data' => $data,
            'meta' => $meta ?: new \stdClass(), 'message' => $message, 'errors' => null,
        ], $status);
    }

    public function index()
    {
        try {
            $filters = $this->request->getGet();
            $result = (new AdminOrdersService())->paginate(is_array($filters) ? $filters : []);
            return $this->respondEnvelope(true, $result['data'], $result['meta']);
        } catch (InvalidArgumentException $exception) {
            return $this->respondEnvelope(false, null, [], $exception->getMessage(), 422);
        } catch (Throwable $exception) {
            log_message('error', 'Admin orders list failed: {type}', ['type' => get_class($exception)]);
            return $this->respondEnvelope(false, null, [], 'دریافت سفارش‌ها امکان‌پذیر نیست.', 503);
        }
    }

    public function show(int $id)
    {
        try {
            $order = (new AdminOrdersService())->find($id);
            return $order === null
                ? $this->respondEnvelope(false, null, [], 'سفارش یافت نشد.', 404)
                : $this->respondEnvelope(true, $order);
        } catch (Throwable $exception) {
            log_message('error', 'Admin order detail failed: {type}', ['type' => get_class($exception)]);
            return $this->respondEnvelope(false, null, [], 'دریافت سفارش امکان‌پذیر نیست.', 503);
        }
    }
}
