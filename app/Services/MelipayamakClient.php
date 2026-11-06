<?php

namespace App\Services;

use InvalidArgumentException;
use RuntimeException;
use Throwable;

final class MelipayamakClient
{
    private const BASE = 'https://rest.payamak-panel.com/api/SendSMS/';

    public static function isAccepted($response): bool
    {
        if (!is_array($response)) return false;
        $retStatus=$response['RetStatus']??$response['retStatus']??null;
        $value=$response['Value']??$response['value']??null;
        return (int)$retStatus===1 && is_scalar($value) && ctype_digit((string)$value) && (int)$value>0;
    }

    public function sendPattern(string $mobile,string $code,int $bodyId): void
    {
        if (preg_match('/^09[0-9]{9}$/D',$mobile)!==1
            ||preg_match('/^[0-9]{6}$/D',$code)!==1||$bodyId<1) {
            throw new InvalidArgumentException('پارامترهای پیامک ورود نامعتبر است.');
        }
        $username=trim((string)env('GEREH_MELIPAYAMAK_USERNAME',''));
        $password=(string)env('GEREH_MELIPAYAMAK_PASSWORD','');
        if ($username==='' || $password==='') {
            throw new RuntimeException('اطلاعات اتصال ملی پیامک تنظیم نشده است.');
        }
        $this->call('BaseServiceNumber',[
            'username'=>$username,'password'=>$password,
            'text'=>$code,'to'=>$mobile,'bodyId'=>$bodyId,
        ]);
    }

    public function credit(): ?float
    {
        $username=trim((string)env('GEREH_MELIPAYAMAK_USERNAME',''));
        $password=(string)env('GEREH_MELIPAYAMAK_PASSWORD','');
        if ($username===''||$password==='') throw new RuntimeException('اطلاعات اتصال تنظیم نشده است.');
        $result=$this->call('GetCredit',['username'=>$username,'password'=>$password],false);
        $value=$result['Value']??$result['value']??null;
        if (!is_numeric($value)) throw new RuntimeException('پاسخ اعتبار پنل نامعتبر است.');
        return (float)$value;
    }

    private function call(string $operation,array $data,bool $requireReceipt=true): array
    {
        try {
            $response=service('curlrequest')->post(self::BASE.$operation,[
                'form_params'=>$data,
                'headers'=>['Accept'=>'application/json'],
                'timeout'=>10,
                'connect_timeout'=>4,
                'http_errors'=>false,
            ]);
            if ($response->getStatusCode()!==200) {
                throw new RuntimeException('پنل پیامکی پاسخ موفق برنگرداند.');
            }
            $parsed=json_decode((string)$response->getBody(),true);
            if (!is_array($parsed)) throw new RuntimeException('ساختار پاسخ پنل پیامکی معتبر نیست.');
            if ($requireReceipt && !self::isAccepted($parsed)) {
                throw new RuntimeException('پنل پیامک درخواست ارسال را رد کرد.');
            }
            if (!$requireReceipt && (int)($parsed['RetStatus']??$parsed['retStatus']??0)!==1) {
                throw new RuntimeException('دریافت اعتبار پنل ناموفق بود.');
            }
            return $parsed;
        } catch(Throwable $exception) {
            // Never log provider response bodies or credentials.
            log_message('error','Melipayamak request failed: {type}',['type'=>get_class($exception)]);
            throw new RuntimeException('ارتباط با ملی پیامک ناموفق بود.',0,$exception);
        }
    }
}
