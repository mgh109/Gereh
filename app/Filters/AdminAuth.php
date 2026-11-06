<?php

namespace App\Filters;

use App\Models\AdminUserModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class AdminAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $adminId = (int) session()->get('admin_id');
        $isApi = $request->isAJAX() || str_contains($request->getPath(), 'api/');
        if ($adminId < 1) {
            return $this->deny($isApi);
        }

        try {
            $admin = (new AdminUserModel())->find($adminId);
        } catch (Throwable $exception) {
            log_message('error', 'Admin auth database unavailable: {type}', ['type' => get_class($exception)]);
            return service('response')->setStatusCode(503)->setJSON([
                'success' => false, 'data' => null, 'meta' => new \stdClass(),
                'message' => 'بررسی دسترسی مدیریت در دسترس نیست.', 'errors' => null,
            ]);
        }

        if ($admin === null || $admin['status'] !== 'active') {
            session()->remove(['admin_id', 'admin_name', 'admin_role']);
            session()->regenerate(true);
            return $this->deny($isApi);
        }

        // Refresh role from DB to ensure role changes take effect immediately.
        session()->set('admin_role', $admin['role']);
        return null;
    }

    private function deny(bool $isApi)
    {
        if (! $isApi) {
            return redirect()->to('/admin/login');
        }
        return service('response')->setStatusCode(401)->setJSON([
            'success' => false, 'data' => null, 'meta' => new \stdClass(),
            'message' => 'برای ادامه ابتدا وارد پنل مدیریت شوید.', 'errors' => null,
        ]);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
