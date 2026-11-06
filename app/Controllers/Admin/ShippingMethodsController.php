<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\ShippingService;
use DomainException;
use InvalidArgumentException;
use Throwable;

final class ShippingMethodsController extends BaseController
{
    public function index()
    {
        try {
            return view('admin/shipping/index',[
                'methods'=>(new ShippingService())->all(),
            ]);
        }catch(Throwable $exception){
            log_message('error','Shipping settings view failed: {type}',['type'=>get_class($exception)]);
            return view('store/unavailable');
        }
    }

    public function save(int $id)
    {
        $fields=[
            'title'=>trim((string)$this->request->getPost('title')),
            'description'=>trim((string)$this->request->getPost('description')),
            'type'=>(string)$this->request->getPost('type'),
            'base_fee_toman'=>(string)$this->request->getPost('base_fee_toman'),
            'free_over_toman'=>$this->request->getPost('free_over_toman')?:null,
            'province_filter'=>$this->request->getPost('province_filter')?:null,
            'estimated_days_min'=>$this->request->getPost('estimated_days_min')?:null,
            'estimated_days_max'=>$this->request->getPost('estimated_days_max')?:null,
            'sort_order'=>(string)$this->request->getPost('sort_order'),
            'is_active'=>$this->request->getPost('is_active')==='1'?1:0,
        ];
        try{
            (new ShippingService())->save($id,$fields);
            return redirect()->to(site_url('admin/shipping'))->with('shipping_success','روش ارسال ذخیره شد.');
        }catch(InvalidArgumentException|DomainException $exception){
            return redirect()->back()->with('shipping_error',$exception->getMessage());
        }catch(Throwable $exception){
            log_message('error','Shipping method save: {type}',['type'=>get_class($exception)]);
            return redirect()->back()->with('shipping_error','ذخیره روش ارسال ناموفق بود.');
        }
    }
}
