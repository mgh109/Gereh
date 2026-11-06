<?php

namespace App\Controllers\Api\V1;

use App\Controllers\BaseController;
use App\Services\CartService;
use CodeIgniter\API\ResponseTrait;
use DomainException;
use InvalidArgumentException;
use Throwable;

class CartController extends BaseController
{
    use ResponseTrait;

    private function reply(array $summary)
    {
        $this->response->setHeader('Cache-Control', 'no-store, private');
        return $this->respond([
            'success' => true, 'data' => $summary, 'meta' => new \stdClass(),
            'message' => null, 'errors' => null,
        ]);
    }

    private function failure(string $message, int $code)
    {
        $this->response->setHeader('Cache-Control', 'no-store, private');
        return $this->respond([
            'success' => false, 'data' => null, 'meta' => new \stdClass(),
            'message' => $message, 'errors' => null,
        ], $code);
    }

    private function payload(): array
    {
        $data = $this->request->getJSON(true);
        return is_array($data) ? $data : [];
    }

    public function index()
    {
        try {
            return $this->reply((new CartService())->summary());
        } catch (Throwable $exception) {
            log_message('error', 'Cart read: {message}', ['message' => $exception->getMessage()]);
            return $this->failure('دریافت سبد خرید ممکن نیست.', 500);
        }
    }

    public function add()
    {
        $data = $this->payload();
        if (! isset($data['variant_id'], $data['quantity']) || ! is_int($data['variant_id']) || ! is_int($data['quantity'])) {
            return $this->failure('شناسه کالا و تعداد باید عدد صحیح باشند.', 422);
        }
        try {
            return $this->reply((new CartService())->add($data['variant_id'], $data['quantity']));
        } catch (InvalidArgumentException $exception) {
            return $this->failure($exception->getMessage(), 422);
        } catch (DomainException $exception) {
            return $this->failure($exception->getMessage(), 409);
        } catch (Throwable $exception) {
            log_message('error', 'Cart add: {message}', ['message' => $exception->getMessage()]);
            return $this->failure('افزودن کالا ممکن نیست.', 500);
        }
    }

    public function update(int $variantId)
    {
        $data = $this->payload();
        if (! isset($data['quantity']) || ! is_int($data['quantity'])) {
            return $this->failure('تعداد باید عدد صحیح باشد.', 422);
        }
        try {
            return $this->reply((new CartService())->setQuantity($variantId, $data['quantity']));
        } catch (InvalidArgumentException $exception) {
            return $this->failure($exception->getMessage(), 422);
        } catch (DomainException $exception) {
            return $this->failure($exception->getMessage(), 409);
        } catch (Throwable $exception) {
            log_message('error', 'Cart update: {message}', ['message' => $exception->getMessage()]);
            return $this->failure('ویرایش سبد خرید ممکن نیست.', 500);
        }
    }

    public function delete(int $variantId)
    {
        try {
            return $this->reply((new CartService())->remove($variantId));
        } catch (InvalidArgumentException $exception) {
            return $this->failure($exception->getMessage(), 422);
        } catch (Throwable $exception) {
            log_message('error', 'Cart remove: {message}', ['message' => $exception->getMessage()]);
            return $this->failure('حذف کالا ممکن نیست.', 500);
        }
    }
}
