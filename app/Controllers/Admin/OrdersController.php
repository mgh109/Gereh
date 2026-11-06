<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\AdminOrdersService;
use InvalidArgumentException;
use Throwable;

final class OrdersController extends BaseController
{
    public function index()
    {
        try {
            $filters = $this->request->getGet();
            $result = (new AdminOrdersService())->paginate(is_array($filters) ? $filters : []);
            return view('admin/orders/index', [
                'orders' => $result['data'],
                'pagination' => $result['meta'],
                'error' => null,
            ]);
        } catch (InvalidArgumentException $exception) {
            return view('admin/orders/index', [
                'orders' => [],
                'pagination' => ['page' => 1, 'total' => 0, 'last_page' => 0],
                'error' => $exception->getMessage(),
            ]);
        } catch (Throwable $exception) {
            log_message('error', 'Admin orders view: {type}', ['type' => get_class($exception)]);
            return view('admin/orders/index', [
                'orders' => [],
                'pagination' => ['page' => 1, 'total' => 0, 'last_page' => 0],
                'error' => 'دریافت سفارش‌ها فعلاً ممکن نیست.',
            ]);
        }
    }

    public function show(int $id)
    {
        try {
            $order = (new AdminOrdersService())->find($id);
            if ($order === null) {
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
            }
            return view('admin/orders/show', ['order' => $order]);
        } catch (\CodeIgniter\Exceptions\PageNotFoundException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            log_message('error', 'Admin order view: {type}', ['type' => get_class($exception)]);
            return view('store/unavailable');
        }
    }
}
