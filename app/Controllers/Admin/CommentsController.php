<?php
namespace App\Controllers\Admin;
use App\Controllers\BaseController;
class CommentsController extends BaseController
{
    public function index()
    {
        $service=new \App\Services\ProductEngagementService();$ready=$service->ready();$rows=[];$total=0;
        $status=$this->request->getGet('status');$status=is_string($status)&&in_array($status,['pending','approved','rejected',''],true)?$status:'pending';
        $productId=max(0,(int)$this->request->getGet('product_id'));$page=max(1,min(100000,(int)$this->request->getGet('page')));
        if($ready){$q=\Config\Database::connect()->table('product_comments c')->select('c.*,p.name AS product_name')->join('products p','p.id=c.product_id');if($status!=='')$q->where('c.status',$status);if($productId)$q->where('c.product_id',$productId);$total=$q->countAllResults(false);$rows=$q->orderBy('c.id','DESC')->limit(20,($page-1)*20)->get()->getResultArray();}
        return view('admin/comments/index',['rows'=>$rows,'ready'=>$ready,'status'=>$status,'productId'=>$productId,'pagination'=>['page'=>$page,'per_page'=>20,'total'=>$total,'last_page'=>(int)ceil($total/20)]]);
    }
    public function status(string $id)
    {
        if(!$this->validate(['status'=>'required|in_list[approved,rejected,pending]']))return redirect()->back()->with('error','وضعیت نامعتبر است.');
        try{(new \App\Services\ProductEngagementService())->moderate((int)$id,(string)$this->request->getPost('status'));return redirect()->back()->with('success','وضعیت دیدگاه تغییر کرد.');}
        catch(\Throwable $e){return redirect()->back()->with('error','تغییر وضعیت انجام نشد.');}
    }
}
