<?php

namespace App\Controllers;

use App\Models\ArticleModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Throwable;

class BlogController extends BaseController
{
    public function index()
    {
        try {
            $page=max(1,min(10000,(int)$this->request->getGet('page')));
            $query=(new ArticleModel())->where('status','published')
                ->where('published_at <=',date('Y-m-d H:i:s'));
            $total=$query->countAllResults(false);
            $articles=$query->orderBy('published_at','DESC')->findAll(12,($page-1)*12);
            return view('store/blog/index',['articles'=>$articles,'page'=>$page,'pages'=>(int)ceil($total/12)]);
        } catch(Throwable $exception) {
            log_message('error','Blog error: {type}',['type'=>get_class($exception)]);
            return view('store/unavailable');
        }
    }

    public function show(string $slug)
    {
        $article=(new ArticleModel())->where('slug',$slug)->where('status','published')
            ->where('published_at <=',date('Y-m-d H:i:s'))->first();
        if($article===null)throw PageNotFoundException::forPageNotFound();
        return view('store/blog/show',['article'=>$article]);
    }
}