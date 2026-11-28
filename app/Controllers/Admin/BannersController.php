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
        $id=max(0, (int)$this->request->getPost('id'));
        if ($id > 0 && !$model->find($id)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        return $this->persist($model, $id, $data, $id === 0);
    }

    public function upload(int $id)
    {
        $model=new HomeBannerModel();
        if(!$model->find($id))throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        return $this->persist($model, $id, [], true);
    }

    private function persist(HomeBannerModel $model, int $id, array $data, bool $imageRequired = false)
    {
        $file = $this->request->getFile('image');
        $filename = null;
        $directory = FCPATH . 'uploads/banners/';
        try {
            if ($imageRequired || ($file && $file->getError() !== UPLOAD_ERR_NO_FILE)) {
                $filename = $this->storeImage($file, $directory);
                $data['image_path'] = 'uploads/banners/' . $filename;
            }
            $saved = $id > 0 ? $model->update($id, $data) : $model->insert($data);
            if (!$saved) {
                throw new \RuntimeException('ذخیره بنر ناموفق بود.');
            }
        } catch (\Throwable $exception) {
            if ($filename !== null && is_file($directory . $filename)) {
                @unlink($directory . $filename);
            }
            $message = $exception instanceof \InvalidArgumentException
                ? $exception->getMessage()
                : 'ذخیره بنر ناموفق بود.';
            return redirect()->back()->withInput()->with('banner_error', $message);
        }
        return redirect()->to(site_url('admin/banners'))->with('banner_success', 'بنر ذخیره شد.');
    }

    private function storeImage(?\CodeIgniter\HTTP\Files\UploadedFile $file, string $directory): string
    {
        if(!$file || !$file->isValid() || $file->hasMoved() || $file->getSize()>5*1024*1024)
            throw new \InvalidArgumentException('تصویر معتبر تا ۵ مگابایت انتخاب کنید.');
        $mime=(string)$file->getMimeType();
        $extensions=['image/jpeg'=>'.jpg','image/png'=>'.png','image/webp'=>'.webp'];
        $dimensions=@getimagesize($file->getTempName());
        if(!isset($extensions[$mime]) || !$dimensions || $dimensions[0]>8000 || $dimensions[1]>8000)
            throw new \InvalidArgumentException('تصویر باید JPG، PNG یا WebP با ابعاد حداکثر ۸۰۰۰ پیکسل باشد.');
        if(!is_dir($directory) && !mkdir($directory,0755,true) && !is_dir($directory))
            throw new \InvalidArgumentException('مسیر ذخیره تصویر قابل نوشتن نیست.');
        $filename=bin2hex(random_bytes(16)).$extensions[$mime];
        $file->move($directory, $filename);
        return $filename;
    }
}
