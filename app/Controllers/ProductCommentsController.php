<?php
namespace App\Controllers;
class ProductCommentsController extends BaseController
{
    public function submit(string $id)
    {
        $product=(new \App\Models\ProductModel())->find((int)$id);
        if(!$product||$product['status']!=='active')throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        $url=site_url('product/'.rawurlencode($product['slug'])).'#product-comments';
        if(!$this->validate(['author_name'=>'required|max_length[100]','body'=>'required|min_length[3]|max_length[2000]','rating'=>'permit_empty|integer|greater_than_equal_to[1]|less_than_equal_to[5]','website'=>'permit_empty|max_length[0]']))return redirect()->to($url)->withInput()->with('comment_error','نام و متن دیدگاه را کامل و امتیاز را بین ۱ تا ۵ انتخاب کنید.');
        if((int)session()->get('last_product_comment')>time()-60)return redirect()->to($url)->withInput()->with('comment_error','برای ارسال دیدگاه بعدی یک دقیقه صبر کنید.');
        try{
            $service=new \App\Services\ProductEngagementService();
            if(!$service->ready())return redirect()->to($url)->with('comment_error','ثبت دیدگاه فعلاً فعال نیست.');
            $rating=$this->request->getPost('rating');
            $service->submit((int)$id,(string)$this->request->getPost('author_name'),(string)$this->request->getPost('body'),$rating!==''&&$rating!==null?(int)$rating:null);
            session()->set('last_product_comment',time());
            return redirect()->to($url)->with('comment_success','دیدگاه ثبت شد و پس از تأیید مدیر نمایش داده می‌شود.');
        }catch(\Throwable $e){log_message('error','Product comment failed: {type}',['type'=>get_class($e)]);return redirect()->to($url)->withInput()->with('comment_error','ثبت دیدگاه انجام نشد؛ دوباره تلاش کنید.');}
    }
}
