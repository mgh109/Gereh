<?php
namespace App\Controllers\Admin;
use App\Controllers\BaseController;
use App\Models\ProductModel;
use App\Models\CategoryModel;
use App\Models\BrandModel;
use App\Models\ProductVariantModel;
use App\Models\InventoryItemModel;
use App\Services\CatalogService;

class CatalogEditorController extends BaseController {
    public function edit(?string $id=null) {
        $product=$id ? (new ProductModel())->find((int)$id) : null;
        if ($id && !$product) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        return view('admin/products/edit',[
            'product'=>$product,
            'categories'=>(new CategoryModel())->orderBy('name')->findAll(),
            'brands'=>(new BrandModel())->orderBy('name')->findAll(),
            'variants'=>$product?(new ProductVariantModel())->where('product_id',$product['id'])->findAll():[],
        ]);
    }
    public function save(?string $id=null) {
        $rules=['name'=>'required|max_length[220]','slug'=>'required|regex_match[/^[a-z0-9-]+$/]|max_length[220]','price_toman'=>'required|is_natural','status'=>'required|in_list[draft,active,archived]'];
        if (!$this->validate($rules)) return redirect()->back()->withInput()->with('errors',$this->validator->getErrors());
        $data=[
            'name'=>trim((string)$this->request->getPost('name')),
            'slug'=>trim((string)$this->request->getPost('slug')),
            'short_description'=>(string)$this->request->getPost('short_description'),
            'description'=>(string)$this->request->getPost('description'),
            'price_toman'=>(int)$this->request->getPost('price_toman'),
            'status'=>(string)$this->request->getPost('status'),
            'category_id'=>$this->request->getPost('category_id') ?: null,
            'brand_id'=>$this->request->getPost('brand_id') ?: null,
        ];
        try {
            $service=new CatalogService();
            if ($id) $service->updateProduct((int)$id,$data);
            else $id=(string)$service->createProduct($data);
            return redirect()->to(site_url('admin/products/'.$id.'/edit'))->with('success','محصول ذخیره شد.');
        } catch (\Throwable $e) {
            log_message('error','Catalog save failed: {message}',['message'=>$e->getMessage()]);
            return redirect()->back()->withInput()->with('errors',['save'=>$e->getMessage()]);
        }
    }
    public function addVariant(string $id) {
        $product=(new ProductModel())->find((int)$id);
        if (!$product) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        if (!$this->validate(['sku'=>'required|max_length[80]','size_label'=>'permit_empty|max_length[60]','color_name'=>'permit_empty|max_length[80]','quantity'=>'required|is_natural'])) return redirect()->back()->with('errors',$this->validator->getErrors());
        $db=\Config\Database::connect();$db->transBegin();
        try {
            $variantId=(new ProductVariantModel())->insert(['product_id'=>$id,'sku'=>trim((string)$this->request->getPost('sku')),'size_label'=>$this->request->getPost('size_label'),'color_name'=>$this->request->getPost('color_name'),'is_active'=>1],true);
            if (!$variantId) throw new \RuntimeException('شناسه SKU تکراری یا نامعتبر است.');
            if (!(new InventoryItemModel())->insert(['variant_id'=>$variantId,'quantity'=>(int)$this->request->getPost('quantity'),'low_stock_threshold'=>2,'updated_at'=>date('Y-m-d H:i:s')])) throw new \RuntimeException('ثبت موجودی انجام نشد.');
            if ($db->transStatus()===false) throw new \RuntimeException('خطای تراکنش.');
            $db->transCommit();return redirect()->back()->with('success','تنوع ثبت شد.');
        } catch (\Throwable $e) {
            $db->transRollback();return redirect()->back()->with('errors',['variant'=>'ثبت تنوع ناموفق بود. SKU را بررسی کنید.']);
        }
    }
}
