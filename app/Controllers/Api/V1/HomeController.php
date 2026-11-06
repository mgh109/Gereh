<?php

namespace App\Controllers\Api\V1;

use App\Controllers\BaseController;
use App\Services\HomeContentService;
use CodeIgniter\API\ResponseTrait;
use Throwable;

final class HomeController extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        try {
            return $this->respond([
                'success'=>true,'data'=>['banners'=>(new HomeContentService())->banners()],
                'meta'=>new \stdClass(),'message'=>null,'errors'=>null,
            ]);
        }catch(Throwable $exception){
            log_message('error','Home API failed: {type}',['type'=>get_class($exception)]);
            return $this->respond([
                'success'=>false,'data'=>null,'meta'=>new \stdClass(),
                'message'=>'محتوای صفحه اصلی در دسترس نیست.','errors'=>null,
            ],503);
        }
    }
}