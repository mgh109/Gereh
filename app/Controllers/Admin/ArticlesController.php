<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ArticleModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Throwable;

final class ArticlesController extends BaseController
{
    public function index()
    {
        try {
            $articles=(new ArticleModel())->orderBy('id','DESC')->findAll(100);
            return view('admin/articles/index',['articles'=>$articles]);
        } catch(Throwable $exception) {
            log_message('error','Admin blog listing: {type}',['type'=>get_class($exception)]);
            return view('store/unavailable');
        }
    }

    public function edit(?int $id=null)
    {
        $article=$id===null?null:(new ArticleModel())->find($id);
        if($id!==null && $article===null)throw PageNotFoundException::forPageNotFound();
        return view('admin/articles/edit',['article'=>$article]);
    }

    public function save(?int $id=null)
    {
        $rules=[
            'title'=>'required|max_length[240]',
            'slug'=>'required|alpha_dash|max_length[240]',
            'excerpt'=>'permit_empty|max_length[3000]',
            'content'=>'required',
            'meta_title'=>'permit_empty|max_length[240]',
            'meta_description'=>'permit_empty|max_length[320]',
            'status'=>'required|in_list[draft,published]',
        ];
        if(!$this->validate($rules))return redirect()->back()->withInput()->with('errors',$this->validator->getErrors());
        $model=new ArticleModel();
        if($id!==null && $model->find($id)===null)throw PageNotFoundException::forPageNotFound();
        $status=(string)$this->request->getPost('status');
        $record=[];
        foreach(['title','slug','excerpt','content','meta_title','meta_description'] as $field){
            $record[$field]=trim((string)$this->request->getPost($field));
        }
        $record['status']=$status;
        $record['published_at']=$status==='published'?date('Y-m-d H:i:s'):null;
        try{
            $duplicate=(new ArticleModel())->where('slug',$record['slug'])->first();
            if($duplicate && (int)$duplicate['id']!==(int)$id)return redirect()->back()->withInput()->with('article_error','این آدرس قبلاً استفاده شده است.');
            if($id===null){$id=(int)$model->insert($record,true);}else{$model->update($id,$record);}
            return redirect()->to(site_url('admin/articles'))->with('article_success','مقاله ذخیره شد.');
        } catch(Throwable $exception) {
            log_message('error','Admin article save: {type}',['type'=>get_class($exception)]);
            return redirect()->back()->withInput()->with('article_error','ذخیره مقاله ممکن نیست.');
        }
    }
}