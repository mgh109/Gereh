<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\AdminCustomersService;
use CodeIgniter\Exceptions\PageNotFoundException;
use Throwable;

class CustomersController extends BaseController
{
    public function index()
    {
        try {
            $filters=$this->request->getGet();
            $result=(new AdminCustomersService())->paginate(is_array($filters)?$filters:[]);
            return view('admin/customers/index',$result);
        } catch(Throwable $exception) {
            log_message('error','Customers list failed: {type}',['type'=>get_class($exception)]);
            return view('store/unavailable');
        }
    }

    public function show(int $id)
    {
        $customer=(new AdminCustomersService())->find($id);
        if($customer===null)throw PageNotFoundException::forPageNotFound();
        return view('admin/customers/show',['customer'=>$customer]);
    }
}