<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session()->get('admin_id')) {
            if ($request->isAJAX() || str_contains($request->getPath(), 'api/')) {
                return service('response')->setStatusCode(401)->setJSON([
                    'success' => false,
                    'message' => 'برای ادامه ابتدا وارد پنل مدیریت شوید.',
                ]);
            }

            return redirect()->to('/admin/login');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}