<?php
namespace App\Controllers\Admin;
use App\Controllers\BaseController;
use App\Models\CategoryModel;
use App\Models\BrandModel;

class TaxonomyController extends BaseController {
    public function index(): string {
        return view('admin/taxonomy/index',['categories'=>(new CategoryModel())->orderBy('sort_order')->findAll(300),'brands'=>(new BrandModel())->orderBy('name')->findAll(300)]);
    }
    public function save(string $type) {
        if (!in_array($type,['categories','brands'],true)) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        if (!$this->validate(['name'=>'required|max_length[160]','slug'=>'required|regex_match[/^[a-z0-9-]+$/]|max_length[190]'])) return redirect()->back()->with('error','نام یا شناسه انگلیسی معتبر نیست.');
        $model=$type==='brands'?new BrandModel():new CategoryModel();
        $data=['name'=>trim((string)$this->request->getPost('name')),'slug'=>trim((string)$this->request->getPost('slug'))];
        if ($type==='brands') $data['is_active']=1;else $data['status']='active';
        try {
            if (!$model->insert($data)) return redirect()->back()->with('error','ثبت ناموفق بود؛ احتمالاً شناسه تکراری است.');
            return redirect()->back()->with('success','اطلاعات ثبت شد.');
        } catch (\Throwable $e) {
            log_message('warning','Taxonomy save failed: {message}',['message'=>$e->getMessage()]);
            return redirect()->back()->with('error','ثبت انجام نشد؛ شناسه تکراری یا خطای دیتابیس.');
        }
    }
}
