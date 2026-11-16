<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\ContactSettingsService;
use Throwable;

final class ContactSettingsController extends BaseController
{
    public function index()
    {
        return view('admin/settings/contact', ['contact' => (new ContactSettingsService())->read()]);
    }

    public function save()
    {
        $data = [];
        foreach (['phone','email','postal_code','address'] as $key) {
            $value = $this->request->getPost($key);
            if (!is_string($value)) return redirect()->back()->with('contact_error', 'اطلاعات تماس نامعتبر است.');
            $data[$key] = trim(strtr($value, array_combine(preg_split('//u', '۰۱۲۳۴۵۶۷۸۹٠١٢٣٤٥٦٧٨٩', -1, PREG_SPLIT_NO_EMPTY), str_split('01234567890123456789'))));
        }
        if (!$this->validateData($data, [
            'phone' => 'required|regex_match[/^0[0-9]{10}$/]',
            'email' => 'required|valid_email|max_length[254]',
            'postal_code' => 'required|exact_length[10]|numeric',
            'address' => 'required|max_length[1000]',
        ])) return redirect()->back()->withInput()->with('contact_error', 'تلفن ۱۱ رقمی با صفر، ایمیل معتبر، کدپستی ۱۰ رقمی و آدرس را وارد کنید.');
        $enamadCode = $this->request->getPost('enamad_code');
        if (!is_string($enamadCode) || strlen($enamadCode)>10000) {
            return redirect()->back()->withInput()->with('contact_error', 'کد نماد معتبر نیست.');
        }
        try {
            $data += ContactSettingsService::parseEnamadCode($enamadCode);
            (new ContactSettingsService())->update($data);
            return redirect()->to(site_url('admin/settings/contact'))->with('contact_success', 'اطلاعات تماس ذخیره شد.');
        } catch (\InvalidArgumentException $exception) {
            return redirect()->back()->withInput()->with('contact_error', $exception->getMessage());
        } catch (Throwable $exception) {
            log_message('error', 'Contact settings save failed: {type}', ['type' => get_class($exception)]);
            return redirect()->back()->withInput()->with('contact_error', 'ذخیره اطلاعات ممکن نیست؛ بروزرسانی دیتابیس و اتصال آن را بررسی کنید.');
        }
    }
}
