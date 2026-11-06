<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\MelipayamakClient;
use App\Services\SmsSettingsService;
use InvalidArgumentException;
use Throwable;

final class SmsSettingsController extends BaseController
{
    public function index()
    {
        try {
            return view('admin/settings/sms', [
                'settings'=>(new SmsSettingsService())->publicStatus(),
            ]);
        } catch(Throwable $exception) {
            log_message('error','SMS settings read: {type}',['type'=>get_class($exception)]);
            return view('store/unavailable');
        }
    }

    public function save()
    {
        try {
            (new SmsSettingsService())->update([
                'provider'=>(string)$this->request->getPost('provider'),
                'otp_body_id'=>(string)$this->request->getPost('otp_body_id'),
                'enabled'=>$this->request->getPost('enabled')==='1',
            ]);
            return redirect()->to(site_url('admin/settings/sms'))
                ->with('sms_success','تنظیمات پیامک ذخیره شد.');
        } catch(InvalidArgumentException $exception) {
            return redirect()->back()->with('sms_error',$exception->getMessage());
        } catch(Throwable $exception) {
            log_message('error','SMS settings save: {type}',['type'=>get_class($exception)]);
            return redirect()->back()->with('sms_error','ذخیره تنظیمات پیامک ممکن نیست.');
        }
    }

    public function check()
    {
        $throttler=service('throttler');
        $adminId=(int)session()->get('admin_id');
        if (!$throttler->check('sms_credit_admin_'.$adminId,3,300)) {
            return redirect()->back()->with('sms_error','درخواست‌های بررسی اتصال زیاد است؛ کمی بعد تلاش کنید.');
        }
        try {
            $credit=(new MelipayamakClient())->credit();
            return redirect()->to(site_url('admin/settings/sms'))
                ->with('sms_success','اتصال ملی پیامک برقرار است. اعتبار گزارش‌شده: '.number_format($credit));
        } catch(Throwable $exception) {
            return redirect()->back()->with('sms_error','بررسی اتصال ملی پیامک ناموفق بود؛ اطلاعات سرور و پنل را بررسی کنید.');
        }
    }
}
