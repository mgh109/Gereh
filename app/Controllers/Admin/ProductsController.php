<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ProductModel;

class ProductsController extends BaseController
{
    public function index()
    {
        $products = [];
        $categories = $brands = $sizes = [];
        $filters = $this->request->getGet();
        $filters = is_array($filters) ? $filters : [];
        $page = max(1, min(100000, (int) ($filters['page'] ?? 1)));
        $total = 0;
        $databaseReady = true;
        try {
            $db = \Config\Database::connect();
            $query = $db->table('products p')->where('p.deleted_at', null);
            $term = \App\Services\SizeFilter::text($filters['q'] ?? '', 100);
            $filters['q'] = $term;
            $filters['size'] = \App\Services\SizeFilter::normalize($filters['size'] ?? '');
            if ($term !== '') $query->groupStart()->like('p.name', $term)->orLike('p.slug', $term)
                ->orWhereIn('p.id', \App\Services\SizeFilter::variants($db, false)->like('sv.sku', $term))->groupEnd();
            if ($filters['size'] !== '') $query->whereIn('p.id', \App\Services\SizeFilter::productIds($db, $filters['size'], false));
            if (in_array($filters['status'] ?? '', ['active','draft','archived'], true)) $query->where('p.status', $filters['status']);
            foreach (['category_id','brand_id'] as $field) {
                if (!empty($filters[$field]) && ctype_digit((string)$filters[$field])) $query->where('p.'.$field, (int)$filters[$field]);
            }
            if($db->tableExists('product_daily_views'))$query->select('(SELECT COALESCE(SUM(pv.views),0) FROM product_daily_views pv WHERE pv.product_id=p.id) AS product_views',false);
            if($db->tableExists('product_comments'))$query->select('(SELECT COUNT(*) FROM product_comments pc WHERE pc.product_id=p.id AND pc.status=\'approved\') AS comment_count',false);
            $total = $query->countAllResults(false);
            $products = $query->select('p.*, b.name AS brand_name')
                ->select('(SELECT COALESCE(SUM(i.quantity),0) FROM product_variants v JOIN inventory_items i ON i.variant_id=v.id WHERE v.product_id=p.id AND v.is_active=1) AS stock', false)
                ->select('(SELECT m.file_path FROM product_media m WHERE m.product_id=p.id ORDER BY m.is_primary DESC,m.sort_order ASC,m.id ASC LIMIT 1) AS image_path', false)
                ->join('brands b','b.id=p.brand_id','left')->orderBy('p.id','DESC')->limit(20, ($page-1)*20)->get()->getResultArray();
            $categories = (new \App\Models\CategoryModel())->orderBy('name')->findAll();
            $brands = (new \App\Models\BrandModel())->orderBy('name')->findAll();
            $sizes = \App\Services\SizeFilter::options($db);
        } catch (\Throwable $exception) {
            log_message('warning', 'Product listing unavailable: {message}', ['message' => $exception->getMessage()]);
            $databaseReady = false;
        }

        return view('admin/products/index', [
            'products' => $products,
            'categories' => $categories, 'brands' => $brands, 'sizes' => $sizes, 'filters' => $filters,
            'pagination' => ['page'=>$page,'per_page'=>20,'total'=>$total,'last_page'=>(int)ceil($total/20)],
            'databaseReady' => $databaseReady,
            'adminName' => session()->get('admin_name') ?: 'مدیر فروشگاه',
        ]);
    }
    public function status(string $id)
    {
        if (!$this->validate(['status'=>'required|in_list[active,draft,archived]'])) return redirect()->back()->with('error','وضعیت انتخاب‌شده معتبر نیست.');
        try {
            (new \App\Services\CatalogService())->setProductStatus((int)$id,(string)$this->request->getPost('status'));
            return redirect()->back()->with('success','وضعیت انتشار محصول تغییر کرد.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error','تغییر وضعیت انجام نشد؛ وجود محصول و قیمت بیشتر از صفر برای انتشار را بررسی کنید.');
        }
    }

}
