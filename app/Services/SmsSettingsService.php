<?php

namespace App\Services;

use Config\Database;
use InvalidArgumentException;
use RuntimeException;

final class SmsSettingsService
{
    public function read(): array
    {
        $row = Database::connect()->table('sms_settings')->where('id',1)->get()->getRowArray();
        if ($row === null) {
            throw new RuntimeException('تنظیمات سرویس پیامک یافت نشد. Migration را اجرا کنید.');
        }
        return $row;
    }

    public function credentialsConfigured(): bool
    {
        return trim((string)env('GEREH_MELIPAYAMAK_USERNAME','')) !== ''
            && trim((string)env('GEREH_MELIPAYAMAK_PASSWORD','')) !== '';
    }

    public function configured(): bool
    {
        $settings=$this->read();
        if ((int)$settings['enabled'] !== 1) return false;
        if ($settings['provider']==='melipayamak') {
            return $this->credentialsConfigured() && (int)$settings['otp_body_id'] > 0;
        }
        if ($settings['provider']==='webhook') {
            return str_starts_with((string)env('GEREH_SMS_WEBHOOK_URL',''),'https://')
                && trim((string)env('GEREH_SMS_WEBHOOK_TOKEN',''))!=='';
        }
        return false;
    }

    public function update(array $input): void
    {
        $provider=$input['provider']??null;
        if (!in_array($provider,['melipayamak','webhook'],true)) {
            throw new InvalidArgumentException('ارائه‌دهنده پیامک معتبر نیست.');
        }
        $enabled=($input['enabled']??false)===true;
        $bodyId=$input['otp_body_id']??null;
        if ($bodyId === '' || $bodyId === null) $bodyId=null;
        if ($bodyId !== null && (!ctype_digit((string)$bodyId) || (int)$bodyId<1 || (int)$bodyId>2147483647)) {
            throw new InvalidArgumentException('شناسه الگوی خدماتی معتبر نیست.');
        }
        if ($enabled && $provider==='melipayamak' && (!$this->credentialsConfigured() || $bodyId===null)) {
            throw new InvalidArgumentException('برای فعال‌سازی، نام کاربری و رمز پنل باید در .env و شناسه الگو در پنل تنظیم شود.');
        }
        if ($enabled && $provider==='webhook' && (
            !str_starts_with((string)env('GEREH_SMS_WEBHOOK_URL',''),'https://')
            || trim((string)env('GEREH_SMS_WEBHOOK_TOKEN',''))===''
        )) {
            throw new InvalidArgumentException('آدرس امن و توکن Webhook در سرور تنظیم نشده‌اند.');
        }
        $updated=Database::connect()->table('sms_settings')->where('id',1)->update([
            'provider'=>$provider,
            'enabled'=>$enabled?1:0,
            'otp_body_id'=>$bodyId,
            'updated_at'=>date('Y-m-d H:i:s'),
        ]);
        if (!$updated) throw new RuntimeException('ذخیره تنظیمات پیامک ناموفق بود.');
    }

    public function publicStatus(): array
    {
        $s=$this->read();
        return [
            'provider'=>$s['provider'],
            'enabled'=>(int)$s['enabled']===1,
            'otp_body_id'=>$s['otp_body_id'],
            'credentials_configured'=>$this->credentialsConfigured(),
            'operational'=>$this->configured(),
        ];
    }
}
