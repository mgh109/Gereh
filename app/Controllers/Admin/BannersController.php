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

    public function upload(int $id)
    {
        $model=new HomeBannerModel();
        if(!$model->find($id))throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        $file=$this->request->getFile('image');
        if(!$file || !$file->isValid() || $file->hasMoved() || $file->getSize()>5*1024*1024)
            return redirect()->back()->with('banner_error','تصویر معتبر تا ۵ مگابایت انتخاب کنید.');
        $mime=(string)$file->getMimeType();
        $extensions=['image/jpeg'=>'.jpg','image/png'=>'.png','image/webp'=>'.webp'];
        $dimensions=@getimagesize($file->getTempName());
        if(!isset($extensions[$mime]) || !$dimensions || $dimensions[0]>8000 || $dimensions[1]>8000)
            return redirect()->back()->with('banner_error','ساختار یا ابعاد تصویر معتبر نیست.');
        $directory=FCPATH.'uploads/banners/';
        if(!is_dir($directory) && !mkdir($directory,0755,true) && !is_dir($directory))
            return redirect()->back()->with('banner_error','مسیر ذخیره قابل نوشتن نیست.');
        $filename=bin2hex(random_bytes(16)).$extensions[$mime];
        try{
            $file->move($directory,$filename);
            $model->update($id,['image_path'=>'uploads/banners/'.$filename]);
            return redirect()->to(site_url('admin/banners'))->with('banner_success','تصویر ذخیره شد.');
        }catch(\Throwable $exception){
            if(is_file($directory.$filename))@unlink($directory.$filename);
            return redirect()->back()->with('banner_error','آپلود تصویر ناموفق بود.');
        }
    }
}