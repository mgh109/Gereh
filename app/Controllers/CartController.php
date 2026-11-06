<?php

namespace App\Controllers;

use App\Services\CartService;
use DomainException;
use InvalidArgumentException;

class CartController extends BaseController
{
    public function index(): string
    {
        $summary = (new CartService())->summary();
        return view('store/cart', $summary);
    }

    public function add()
    {
        if (! $this->validate(['variant_id' => 'required|is_natural_no_zero', 'quantity' => 'required|is_natural_no_zero|less_than_equal_to[20]'])) {
            return redirect()->back()->with('cart_error', 'انتخاب محصول یا تعداد معتبر نیست.');
        }
        try {
            (new CartService())->add((int) $this->request->getPost('variant_id'), (int) $this->request->getPost('quantity'));
            return redirect()->to(site_url('cart'))->with('cart_success', 'محصول به سبد خرید اضافه شد.');
        } catch (DomainException|InvalidArgumentException $exception) {
            return redirect()->back()->with('cart_error', $exception->getMessage());
        }
    }

    public function update()
    {
        if (! $this->validate(['variant_id' => 'required|is_natural_no_zero', 'quantity' => 'required|is_natural|less_than_equal_to[20]'])) {
            return redirect()->to(site_url('cart'))->with('cart_error', 'تعداد یا شناسه کالا معتبر نیست.');
        }
        try {
            (new CartService())->setQuantity((int) $this->request->getPost('variant_id'), (int) $this->request->getPost('quantity'));
            return redirect()->to(site_url('cart'))->with('cart_success', 'سبد خرید به‌روزرسانی شد.');
        } catch (DomainException|InvalidArgumentException $exception) {
            return redirect()->to(site_url('cart'))->with('cart_error', $exception->getMessage());
        }
    }
}
