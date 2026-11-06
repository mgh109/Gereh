<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\HomeBannerModel;

class BannersController extends BaseController
{
    public function index()
    {
        return view('admin/banners/index',['banners'=>(new HomeBannerModel())->orderBy('sort_order','ASC')->findAll(100)]);
    }

    public function save()
    {
        if (!$this->validate([
            'slot'=>'required|in_list[hero,women,men,promo]',
            'title'=>'required|max_length[220]',
            'link_path'=>'required|max_length[240]',
        ])) return redirect()->back()->with('banner_error','اطلاعات بنر نامعتبر است.');
        $link=trim((string)$this->request->getPost('link_path'));
        if (!preg_match('#^/(?!/)[a-zA-Z0-9/_?=&%-]*$#D',$link))
            return redirect()->back()->with('banner_error','لینک داخلی معتبر نیست.');
        $data=['slot'=>$this->request->getPost('slot'),'title'=>$this->request->getPost('title'),
            'subtitle'=>$this->request->getPost('subtitle'),'link_path'=>$link,
            'show_text'=>$this->request->getPost('show_text')==='1'?1:0,
            'is_active'=>$this->request->getPost('is_active')==='1'?1:0];
        $model=new HomeBannerModel();
        $id=(int)$this->request->getPost('id');
        if($id>0 && $model->find($id))$model->update($id,$data);
        else $model->insert($data);
        return redirect()->to(site_url('admin/banners'))->with('banner_success','بنر ذخیره شد.');
    }
}