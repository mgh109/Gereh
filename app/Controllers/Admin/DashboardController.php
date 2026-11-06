<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\AdminDashboardService;
use Throwable;

class DashboardController extends BaseController
{
    public function index()
    {
        return view('admin/dashboard/index', [
            'adminName' => session()->get('admin_name'),
        ]);
    }

    public function summary()
    {
        try {
            $summary = (new AdminDashboardService())->summary();
            return $this->response->setJSON([
                'success' => true, 'data' => $summary, 'meta' => new \stdClass(),
                'message' => null, 'errors' => null,
            ]);
        } catch (Throwable $exception) {
            log_message('error', 'Admin dashboard statistics: {type}', ['type' => get_class($exception)]);
            return $this->response->setStatusCode(503)->setJSON([
                'success' => false, 'data' => null, 'meta' => new \stdClass(),
                'message' => 'آمار داشبورد موقتاً در دسترس نیست.', 'errors' => null,
            ]);
        }
    }
}
