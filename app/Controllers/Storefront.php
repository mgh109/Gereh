<?php

namespace App\Controllers;

use App\Services\CatalogReadService;
use CodeIgniter\Exceptions\PageNotFoundException;

class Storefront extends BaseController
{
    public function home()
    {
        try {
            $catalog = new CatalogReadService();
            return view('store/home', [
                'products' => $catalog->products(['per_page' => 6])['data'],
                'brands' => $catalog->brands(),
                'banners' => (new \App\Services\HomeContentService())->banners(),
            ]);
        } catch (\Throwable $exception) {
            log_message('error', 'Storefront home catalog: {message}', ['message' => $exception->getMessage()]);
            return $this->response->setStatusCode(503)->setHeader('Retry-After','300')->setBody(view('store/unavailable'));
        }
    }

    public function shop()
    {
        try {
            $catalog = new CatalogReadService();
            $filters = $this->request->getGet();
            $result = $catalog->products(is_array($filters) ? $filters : []);
            return view('store/shop', [
                'products' => $result['data'],
                'pagination' => $result['meta'],
                'brands' => $catalog->brands(),
                'sizes' => $catalog->sizes(),
            ]);
        } catch (\Throwable $exception) {
            log_message('error', 'Storefront shop catalog: {message}', ['message' => $exception->getMessage()]);
            return $this->response->setStatusCode(503)->setHeader('Retry-After','300')->setBody(view('store/unavailable'));
        }
    }

    public function product(string $slug)
    {
        try {
            $result = (new CatalogReadService())->product($slug);
            if ($result === null) {
                throw PageNotFoundException::forPageNotFound();
            }
            $result['comments']=[];$result['engagementReady']=false;
            try {
                $engagement=new \App\Services\ProductEngagementService();
                if($engagement->ready()) {
                    $result['engagementReady']=true;
                    if(strtoupper($this->request->getMethod())==='GET')$engagement->recordView((int)$result['product']['id'],$this->request->getHeaderLine('User-Agent'));
                    $result['comments']=$engagement->comments((int)$result['product']['id']);
                    $result['product']['engagement']=$engagement->stats((int)$result['product']['id']);
                }
            } catch (\Throwable $e) {log_message('warning','Product engagement unavailable: {type}',['type'=>get_class($e)]);}
            return view('store/product', $result);
        } catch (PageNotFoundException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            log_message('error', 'Storefront product: {message}', ['message' => $exception->getMessage()]);
            return $this->response->setStatusCode(503)->setHeader('Retry-After','300')->setBody(view('store/unavailable'));
        }
    }
}
