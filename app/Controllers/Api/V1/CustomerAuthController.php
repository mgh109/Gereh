<?php

namespace App\Controllers\Api\V1;

use App\Controllers\BaseController;
use App\Services\CustomerAuthService;
use CodeIgniter\API\ResponseTrait;
use Config\Database;
use DomainException;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class CustomerAuthController extends BaseController
{
    use ResponseTrait;

    private function json($data = null, int $status = 200, ?string $message = null)
    {
        return $this->respond([
            'success' => $status < 400, 'data' => $data, 'meta' => new \stdClass(),
            'message' => $message, 'errors' => null,
        ], $status);
    }

    private function body(): array
    {
        $body = $this->request->getJSON(true);
        return is_array($body) ? $body : [];
    }

    public function csrf()
    {
        // Read-only bootstrap for same-origin clients using session CSRF.
        return $this->json([
            'tokenName' => csrf_token(),
            'headerName' => 'X-CSRF-TOKEN',
            'token' => csrf_hash(),
        ])->setHeader('Cache-Control', 'no-store, private');
    }

    public function requestOtp()
    {
        $body = $this->body();
        if (! isset($body['mobile']) || ! is_string($body['mobile'])) {
            return $this->json(null, 422, 'شماره موبایل الزامی است.');
        }
        try {
            (new CustomerAuthService())->request(trim($body['mobile']), (string) $this->request->getIPAddress());
            return $this->json(null, 200, 'در صورت موفقیت ارسال، کد ورود پیامک شد.');
        } catch (InvalidArgumentException $exception) {
            return $this->json(null, 422, $exception->getMessage());
        } catch (DomainException $exception) {
            return $this->json(null, 429, $exception->getMessage());
        } catch (Throwable $exception) {
            log_message('error', 'OTP request failed: {type}', ['type' => get_class($exception)]);
            return $this->json(null, 503, 'ارسال کد ورود اکنون در دسترس نیست.');
        }
    }

    public function verifyOtp()
    {
        $body = $this->body();
        if (! isset($body['mobile'], $body['code']) || ! is_string($body['mobile']) || ! is_string($body['code'])) {
            return $this->json(null, 422, 'شماره موبایل و کد ورود الزامی هستند.');
        }
        try {
            $customer = (new CustomerAuthService())->verify(trim($body['mobile']), $body['code'], (string) $this->request->getIPAddress());
            return $this->json($customer);
        } catch (InvalidArgumentException $exception) {
            return $this->json(null, 422, $exception->getMessage());
        } catch (DomainException $exception) {
            return $this->json(null, 401, $exception->getMessage());
        } catch (Throwable $exception) {
            log_message('error', 'OTP verify failed: {type}', ['type' => get_class($exception)]);
            return $this->json(null, 503, 'ورود موقتاً در دسترس نیست.');
        }
    }

    public function me()
    {
        $id = (int) session()->get('customer_id');
        if ($id < 1) {
            return $this->json(null, 401, 'برای مشاهده پروفایل وارد شوید.');
        }
        try {
            $customer = Database::connect()->table('customers')
                ->select('id, mobile, first_name, last_name')
                ->where('id', $id)->where('status', 'active')->get()->getRowArray();
            return $customer === null
                ? $this->json(null, 401, 'حساب کاربری معتبر نیست.')
                : $this->json($customer);
        } catch (Throwable $exception) {
            log_message('error', 'Profile read: {type}', ['type' => get_class($exception)]);
            return $this->json(null, 500, 'دریافت پروفایل ناموفق بود.');
        }
    }

    public function updateMe()
    {
        $id = (int) session()->get('customer_id');
        if ($id < 1) {
            return $this->json(null, 401, 'ابتدا وارد شوید.');
        }
        $body = $this->body();
        $data = [];
        foreach (['first_name', 'last_name'] as $field) {
            if (! array_key_exists($field, $body)) {
                continue;
            }
            if (! is_string($body[$field]) || mb_strlen(trim($body[$field])) > 100) {
                return $this->json(null, 422, 'مقدار فیلد ' . $field . ' نامعتبر است.');
            }
            $data[$field] = trim($body[$field]);
        }
        if ($data === []) {
            return $this->json(null, 422, 'فیلدی برای ویرایش ارسال نشده است.');
        }
        try {
            $db = Database::connect();
            $updated = $db->table('customers')->where('id', $id)->where('status', 'active')->update($data + ['updated_at' => date('Y-m-d H:i:s')]);
            if (! $updated || $db->affectedRows() === 0) {
                return $this->json(null, 404, 'حساب کاربری پیدا نشد.');
            }
            return $this->me();
        } catch (Throwable $exception) {
            log_message('error', 'Profile update: {type}', ['type' => get_class($exception)]);
            return $this->json(null, 500, 'ویرایش پروفایل ناموفق بود.');
        }
    }

    public function logout()
    {
        session()->remove('customer_id');
        session()->regenerate(true);
        return $this->json(null, 200, 'از حساب کاربری خارج شدید.');
    }
}
