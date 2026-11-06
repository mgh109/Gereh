<?php

namespace App\Controllers\Api\V1;

use App\Controllers\BaseController;
use App\Services\CartService;
use App\Services\ShippingService;
use CodeIgniter\API\ResponseTrait;
use Throwable;

final class ShippingController extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        $this->response->setHeader('Cache-Control','no-store, private');
        try {
            $province=trim((string)($this->request->getGet('province')??''));
            if(mb_strlen($province)>100)return $this->respond([
                'success'=>false,'data'=>null,'message'=>'استان نامعتبر است.','errors'=>null,'meta'=>new \stdClass(),
            ],422);
            $cart=(new CartService())->summary();
            $subtotal=(int)$cart['total'];
            $methods=(new ShippingService())->quotes($subtotal,$province);
            return $this->respond([
                'success'=>true,
                'data'=>['subtotal_toman'=>$subtotal,'methods'=>$methods],
                'meta'=>new \stdClass(),'message'=>null,'errors'=>null,
            ]);
        }catch(Throwable $exception){
            log_message('error','Shipping quote: {type}',['type'=>get_class($exception)]);
            return $this->respond([
                'success'=>false,'data'=>null,'meta'=>new \stdClass(),
                'message'=>'محاسبه هزینه ارسال اکنون ممکن نیست.','errors'=>null,
            ],503);
        }
    }
}
