<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use Config\Database;
use DomainException;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class CustomerAuthService
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null, private ?SmsSender $sms = null)
    {
        $this->db = $db ?? Database::connect();
        $this->sms ??= new SmsSender();
    }

    public static function validMobile(string $mobile): bool
    {
        return preg_match('/^09[0-9]{9}$/D', $mobile) === 1;
    }

    private function secret(): string
    {
        $secret = (string) env('GEREH_OTP_HASH_SECRET', '');
        if (strlen($secret) < 32) {
            throw new RuntimeException('کلید امن OTP روی سرور تنظیم نشده است.');
        }
        return $secret;
    }

    private function codeHash(string $mobile, string $code): string
    {
        return hash_hmac('sha256', $mobile . ':' . $code, $this->secret());
    }

    public function request(string $mobile, string $ip): void
    {
        if (! self::validMobile($mobile)) {
            throw new InvalidArgumentException('شماره موبایل معتبر نیست.');
        }
        // Fail before writing a challenge if provider or hashing is not configured.
        $this->secret();
        if (env('GEREH_SMS_WEBHOOK_URL') === null || env('GEREH_SMS_WEBHOOK_TOKEN') === null) {
            throw new RuntimeException('سرویس پیامک فعال نیست.');
        }

        $throttler = service('throttler');
        if (! $throttler->check('otp_mobile_' . hash('sha256', $mobile), 3, 900)
            || ! $throttler->check('otp_ip_' . hash('sha256', $ip), 15, 900)) {
            throw new DomainException('تعداد درخواست‌ها زیاد است. کمی بعد تلاش کنید.');
        }

        $now = date('Y-m-d H:i:s');
        $recent = $this->db->table('otp_challenges')->select('created_at')
            ->where('mobile', $mobile)->orderBy('id', 'DESC')->get(1)->getRowArray();
        $cooldown = max(30, min(300, (int) env('GEREH_OTP_RESEND_COOLDOWN_SECONDS', 60)));
        if ($recent !== null && strtotime((string) $recent['created_at']) > time() - $cooldown) {
            throw new DomainException('برای دریافت کد جدید کمی صبر کنید.');
        }

        $code = (string) random_int(100000, 999999);
        $ttl = max(60, min(600, (int) env('GEREH_OTP_TTL_SECONDS', 120)));
        // A newly requested code supersedes all previous outstanding codes.
        $this->db->table('otp_challenges')->where('mobile', $mobile)
            ->where('status', 'sent')->update(['status' => 'superseded']);
        $created = $this->db->table('otp_challenges')->insert([
            'mobile' => $mobile, 'code_hash' => $this->codeHash($mobile, $code),
            'attempts' => 0, 'status' => 'pending',
            'expires_at' => date('Y-m-d H:i:s', time() + $ttl), 'created_at' => $now,
        ]);
        if (! $created) {
            throw new RuntimeException('ثبت درخواست ورود انجام نشد.');
        }
        $id = (int) $this->db->insertID();
        try {
            $this->sms->sendOtp($mobile, $code);
            $this->db->table('otp_challenges')->where('id', $id)->update(['status' => 'sent']);
        } catch (Throwable $exception) {
            $this->db->table('otp_challenges')->where('id', $id)->update(['status' => 'failed']);
            throw $exception;
        }
    }

    public function verify(string $mobile, string $code, string $ip): array
    {
        if (! self::validMobile($mobile) || preg_match('/^[0-9]{6}$/D', $code) !== 1) {
            throw new InvalidArgumentException('شماره موبایل یا کد ورود نامعتبر است.');
        }
        $throttler = service('throttler');
        if (! $throttler->check('otp_verify_' . hash('sha256', $mobile . ':' . $ip), 10, 900)) {
            throw new DomainException('تلاش‌های تأیید بیش از حد مجاز است.');
        }
        $hash = $this->codeHash($mobile, $code);
        $customer = null;
        $valid = false;
        $this->db->transBegin();
        try {
            $challenge = $this->db->query(
                'SELECT * FROM otp_challenges WHERE mobile = ? AND status = ? ORDER BY id DESC LIMIT 1 FOR UPDATE',
                [$mobile, 'sent']
            )->getRowArray();
            if ($challenge !== null && strtotime($challenge['expires_at']) > time()
                && (int) $challenge['attempts'] < 5) {
                if (hash_equals($challenge['code_hash'], $hash)) {
                    $this->db->table('otp_challenges')->where('id', $challenge['id'])
                        ->update(['status' => 'used', 'used_at' => date('Y-m-d H:i:s')]);
                    $customer = $this->db->table('customers')->where('mobile', $mobile)->get()->getRowArray();
                    if ($customer === null) {
                        $this->db->table('customers')->insert([
                            'mobile' => $mobile, 'status' => 'active',
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                        $customer = $this->db->table('customers')->where('mobile', $mobile)->get()->getRowArray();
                    }
                    if ($customer === null || $customer['status'] !== 'active') {
                        throw new DomainException('حساب کاربری غیرفعال است.');
                    }
                    $valid = true;
                } else {
                    $attempts = (int) $challenge['attempts'] + 1;
                    $this->db->table('otp_challenges')->where('id', $challenge['id'])->update([
                        'attempts' => $attempts,
                        'status' => $attempts >= 5 ? 'locked' : 'sent',
                    ]);
                }
            }
            if ($this->db->transStatus() === false) {
                throw new RuntimeException('خطای ثبت اطلاعات ورود.');
            }
            $this->db->transCommit();
        } catch (Throwable $exception) {
            $this->db->transRollback();
            throw $exception;
        }

        if (! $valid || $customer === null) {
            throw new DomainException('کد نامعتبر یا منقضی شده است.');
        }
        session()->regenerate(true);
        session()->set('customer_id', (int) $customer['id']);
        return [
            'id' => (int) $customer['id'], 'mobile' => $customer['mobile'],
            'first_name' => $customer['first_name'], 'last_name' => $customer['last_name'],
        ];
    }
}
