<?php

namespace App\Controllers\Api\V1\Admin;

use App\Controllers\BaseController;
use App\Services\AdminDashboardService;
use CodeIgniter\API\ResponseTrait;
use Throwable;

class DashboardController extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        $this->response->setHeader('Cache-Control', 'no-store, private');
        try {
            return $this->respond([
                'success' => true, 'data' => (new AdminDashboardService())->summary(),
                'meta' => new \stdClass(), 'message' => null, 'errors' => null,
            ]);
        } catch (Throwable $exception) {
            log_message('error', 'Admin summary API failed: {type}', ['type' => get_class($exception)]);
            return $this->respond([
                'success' => false, 'data' => null, 'meta' => new \stdClass(),
                'message' => 'دریافت آمار مدیریت امکان‌پذیر نیست.', 'errors' => null,
            ], 503);
        }
    }
}
