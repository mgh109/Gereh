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
        $engagement=new \App\Services\ProductEngagementService();
        return view('admin/products/edit',[
            'engagementStats'=>$product&&$engagement->ready()?$engagement->stats((int)$product['id']):null,
            'product'=>$product,
            'badgeFieldReady'=>\Config\Database::connect()->fieldExists('image_badge', 'products'),
            'editorFieldsReady'=>\Config\Database::connect()->fieldExists('item_condition','products'),
            'categories'=>(new CategoryModel())->orderBy('name')->findAll(),
            'brands'=>(new BrandModel())->orderBy('name')->findAll(),
            'variants'=>$product?(new ProductVariantModel())->select('product_variants.*, COALESCE(inventory_items.quantity,0) AS quantity',false)->join('inventory_items','inventory_items.variant_id=product_variants.id','left')->where('product_id',$product['id'])->findAll():[],
            'media'=>$product?(new \App\Models\ProductMediaModel())->where('product_id',$product['id'])->orderBy('is_primary','DESC')->orderBy('sort_order')->findAll():[],
        ]);
    }
    public function save(?string $id=null) {
        $rules=['name'=>'required|max_length[220]','slug'=>'permit_empty|regex_match[/^[a-z0-9-]+$/]|max_length[220]','price_toman'=>'required|is_natural|less_than_equal_to[100000000000]','sale_price_toman'=>'permit_empty|is_natural_no_zero|less_than_equal_to[100000000000]','status'=>'required|in_list[draft,active,archived]','new_brand'=>'permit_empty|max_length[160]','product_code'=>'permit_empty|max_length[80]','image_badge'=>'permit_empty|max_length[40]','item_condition'=>'permit_empty|in_list[new,used,stock]','category_id'=>'permit_empty|is_natural_no_zero','brand_id'=>'permit_empty|is_natural_no_zero','meta_title'=>'permit_empty|max_length[255]','meta_description'=>'permit_empty|max_length[320]','alt_text'=>'permit_empty|max_length[255]' ];
        if (!$this->validate($rules)) return redirect()->back()->withInput()->with('errors',$this->validator->getErrors());
        $regularPrice=(int)$this->request->getPost('price_toman');
        $saleValue=$this->request->getPost('sale_price_toman');
        $hasSale=$saleValue!==null && $saleValue!=='';
        $salePrice=$hasSale?(int)$saleValue:null;
        if ($hasSale && ($salePrice<=0 || $salePrice >= $regularPrice)) {
            return redirect()->back()->withInput()->with('errors',['sale_price_toman'=>'قیمت با تخفیف باید بیشتر از صفر و کمتر از قیمت اصلی باشد.']);
        }
        $data=[
            'name'=>trim((string)$this->request->getPost('name')),
            'slug'=>trim((string)$this->request->getPost('slug')),
            'short_description'=>(string)$this->request->getPost('short_description'),
            'description'=>(string)$this->request->getPost('description'),
            'price_toman'=>$hasSale?$salePrice:$regularPrice,
            'compare_at_price_toman'=>$hasSale?$regularPrice:null,
            'status'=>(string)$this->request->getPost('status'),
            'meta_title'=>trim((string)$this->request->getPost('meta_title')) ?: null,
            'meta_description'=>trim((string)$this->request->getPost('meta_description')) ?: null,
            'category_id'=>$this->request->getPost('category_id') ?: null,
            'brand_id'=>$this->request->getPost('brand_id') ?: null,
        ];
        $moved=[];
        try {
            // Works on existing hosts too; the form explains when the optional columns are missing.
            $db=\Config\Database::connect();
            foreach (['product_code','item_condition'] as $field) {
                if ($db->fieldExists($field,'products')) $data[$field]=trim((string)$this->request->getPost($field)) ?: null;
                elseif (trim((string)$this->request->getPost($field)) !== '') throw new \InvalidArgumentException('برای ذخیره کد کالا و وضعیت نو/کارکرده، ابتدا بروزرسانی دیتابیس فرم محصول را اجرا کنید.');
            }
            $badge = $this->request->getPost('image_badge');
            if ($badge !== null) {
                if (!$db->fieldExists('image_badge', 'products')) {
                    throw new \InvalidArgumentException('برای ذخیره برچسب عکس، بروزرسانی دیتابیس را اجرا کنید.');
                }
                $data['image_badge'] = trim((string) $badge) ?: null;
            }
            $rows=$this->request->getPost('variants') ?? [];
            if (!is_array($rows) || count($rows)>100) throw new \InvalidArgumentException('حداکثر ۱۰۰ تنوع در هر بار ذخیره مجاز است.');
            $files=$this->request->getFileMultiple('images') ?? [];
            $files=array_values(array_filter($files,static fn($file)=>$file->getError()!==UPLOAD_ERR_NO_FILE));
            if (count($files)>10) throw new \InvalidArgumentException('در هر بار ذخیره حداکثر ۱۰ عکس انتخاب کنید.');
            // Validate every file before moving any file or writing any database row.
            foreach ($files as $file) {
                if (!$file->isValid() || $file->hasMoved()) throw new \InvalidArgumentException('دریافت عکس کامل نشد؛ حجم مجاز آپلود هاست و فایل انتخابی را بررسی کنید.');
                $dimensions=@getimagesize($file->getTempName());
                if ($file->getSize()>5*1024*1024 || !in_array($file->getMimeType(),['image/jpeg','image/png','image/webp'],true) || !$dimensions || max($dimensions[0],$dimensions[1])>8000) throw new \InvalidArgumentException('تصاویر باید JPG، PNG یا WebP، حداکثر ۵ مگابایت و ۸۰۰۰ پیکسل باشند.');
            }
            $media=[];$directory=FCPATH.'uploads/products/';
            if ($files && !is_dir($directory) && !mkdir($directory,0755,true) && !is_dir($directory)) throw new \RuntimeException('مسیر آپلود قابل نوشتن نیست.');
            foreach ($files as $file) {
                $extension=['image/jpeg'=>'.jpg','image/png'=>'.png','image/webp'=>'.webp'][$file->getMimeType()];
                $name=bin2hex(random_bytes(16)).$extension;
                $file->move($directory,$name);$moved[]=$directory.$name;
                $media[]=['file_path'=>'uploads/products/'.$name,'alt_text'=>(string)($this->request->getPost('alt_text') ?: $data['name'])];
            }
            $id=(string)(new \App\Services\ProductEditorService($db))->save($id?(int)$id:null,$data,$rows,trim((string)$this->request->getPost('new_brand')),$media);
            return redirect()->to(site_url('admin/products/'.$id.'/edit'))->with('success','محصول، تصاویر و تنوع‌ها با موفقیت ذخیره شدند.');
        } catch (\Throwable $e) {
            foreach($moved as $path) if(is_file($path)) @unlink($path);
            log_message('error','Product editor save failed: {message}',['message'=>$e->getMessage()]);
            $message=$e instanceof \InvalidArgumentException ? $e->getMessage() : 'ذخیره محصول انجام نشد؛ اطلاعات را بررسی و دوباره تلاش کنید.';
            return redirect()->back()->withInput()->with('errors',['save'=>$message]);
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
    public function addMedia(string $id) {
        if (!(new ProductModel())->find((int)$id)) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        $file=$this->request->getFile('image');
        if (!$file || !$file->isValid() || $file->hasMoved() || $file->getSize()>5*1024*1024) return redirect()->back()->with('errors',['image'=>'تصویر معتبر با حجم حداکثر ۵ مگابایت انتخاب کنید.']);
        $mime=$file->getMimeType();
        if (!in_array($mime,['image/jpeg','image/png','image/webp'],true)) return redirect()->back()->with('errors',['image'=>'فقط تصاویر JPG، PNG و WebP مجاز هستند.']);
        $directory=FCPATH.'uploads/products/';
        if (!is_dir($directory) && !mkdir($directory,0755,true) && !is_dir($directory)) return redirect()->back()->with('errors',['image'=>'مسیر آپلود قابل نوشتن نیست.']);
        $imageSize=@getimagesize($file->getTempName());
        if (!$imageSize || $imageSize[0]<1 || $imageSize[1]<1 || $imageSize[0]>8000 || $imageSize[1]>8000)
            return redirect()->back()->with('errors',['image'=>'ساختار یا ابعاد تصویر معتبر نیست.']);
        $extension=['image/jpeg'=>'.jpg','image/png'=>'.png','image/webp'=>'.webp'][$mime];
        $name=bin2hex(random_bytes(16)).$extension;
        try {
            $file->move($directory,$name);
            $model=new \App\Models\ProductMediaModel();
            if (!$model->insert(['product_id'=>(int)$id,'file_path'=>'uploads/products/'.$name,'alt_text'=>(string)$this->request->getPost('alt_text'),'media_type'=>'image','sort_order'=>0,'is_primary'=>0])) throw new \RuntimeException('DB insert failed');
            return redirect()->back()->with('success','تصویر ذخیره شد.');
        } catch (\Throwable $e) {
            if (is_file($directory.$name)) @unlink($directory.$name);
            log_message('error','Media upload failed: {message}',['message'=>$e->getMessage()]);
            return redirect()->back()->with('errors',['image'=>'ذخیره تصویر ناموفق بود.']);
        }
    }
    public function setStock(string $id, string $variantId) {
        $variant=(new ProductVariantModel())->where('product_id',(int)$id)->find((int)$variantId);
        if (!$variant) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        if (!$this->validate(['quantity'=>'required|is_natural'])) return redirect()->back()->with('errors',['stock'=>'موجودی نامعتبر است.']);
        (new \App\Services\InventoryService())->setAbsolute((int)$variantId,(int)$this->request->getPost('quantity'));
        return redirect()->back()->with('success','موجودی به‌روزرسانی شد.');
    }

}
