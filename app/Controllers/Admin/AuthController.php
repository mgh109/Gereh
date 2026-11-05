<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AdminUserModel;

class AuthController extends BaseController
{
    public function login()
    {
        if (session()->get('admin_id')) {
            return redirect()->to('/admin');
        }

        return view('admin/auth/login');
    }

    public function attempt()
    {
        $rules = [
            'email' => 'required|valid_email|max_length[190]',
            'password' => 'required|min_length[8]|max_length[200]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $admin = (new AdminUserModel())->where('email', strtolower(trim((string) $this->request->getPost('email'))))->first();

        if (
            ! $admin ||
            $admin['status'] !== 'active' ||
            ! password_verify((string) $this->request->getPost('password'), $admin['password_hash'])
        ) {
            return redirect()->back()->withInput()->with('error', 'اطلاعات ورود صحیح نیست.');
        }

        session()->regenerate(true);
        session()->set([
            'admin_id' => (int) $admin['id'],
            'admin_name' => $admin['name'],
            'admin_role' => $admin['role'],
        ]);

        (new AdminUserModel())->update($admin['id'], ['last_login_at' => date('Y-m-d H:i:s')]);

        return redirect()->to('/admin');
    }

    public function logout()
    {
        session()->remove(['admin_id', 'admin_name', 'admin_role']);
        session()->regenerate(true);

        return redirect()->to('/admin/login');
    }
}