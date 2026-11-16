<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use Config\Database;
use Throwable;

class InventoryController extends BaseController
{
    public function index()
    {
        $filters=$this->request->getGet();
        $filters=is_array($filters)?$filters:[];
        $page=max(1,min(100000,(int)($filters['page']??1)));
        $rows=[]; $sizes=[]; $total=0; $low=0; $error=null;
        try {
            $db=Database::connect();
            $query=$db->table('product_variants v')->join('products p','p.id=v.product_id')
                ->join('inventory_items i','i.variant_id=v.id','left')->where('p.deleted_at',null);
            $term=\App\Services\SizeFilter::text($filters['q']??'',100);
            $filters['q']=$term;
            $filters['size']=\App\Services\SizeFilter::normalize($filters['size']??'');
            if($filters['size']!=='')$query->where(\App\Services\SizeFilter::column('v.size_label'),$filters['size']);
            $sizes=\App\Services\SizeFilter::options($db);
            if($term!=='')$query->groupStart()->like('p.name',$term)->orLike('v.sku',$term)->orLike('v.color_name',$term)->groupEnd();
            if(!empty($filters['product_id'])&&ctype_digit((string)$filters['product_id']))$query->where('p.id',(int)$filters['product_id']);
            if(($filters['stock']??'')==='low')$query->where('COALESCE(i.quantity,0) <= COALESCE(i.low_stock_threshold,2)',null,false);
            if(($filters['stock']??'')==='out')$query->where('COALESCE(i.quantity,0) = 0',null,false);
            $total=$query->countAllResults(false);
            $rows=$query->select('v.*,p.name AS product_name,COALESCE(i.quantity,0) AS quantity,COALESCE(i.low_stock_threshold,2) AS low_stock_threshold',false)->orderBy('p.id','DESC')->orderBy('v.id','ASC')->limit(20,($page-1)*20)->get()->getResultArray();
            $low=$db->table('inventory_items i')->join('product_variants v','v.id=i.variant_id')->join('products p','p.id=v.product_id')->where('p.deleted_at',null)->where('i.quantity <= i.low_stock_threshold',null,false)->countAllResults();
        } catch(Throwable $exception) {
            log_message('error','Admin inventory listing failed: {type}',['type'=>get_class($exception)]);
            $error='دریافت موجودی فعلاً ممکن نیست.';
        }
        return view('admin/inventory/index',['variants'=>$rows,'sizes'=>$sizes,'filters'=>$filters,'low'=>$low,'error'=>$error,'pagination'=>['page'=>$page,'per_page'=>20,'total'=>$total,'last_page'=>(int)ceil($total/20)]]);
    }
}
