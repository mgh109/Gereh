<?php

namespace App\Controllers;

use App\Services\CartService;
use App\Services\OrderService;
use Throwable;

class CustomerPages extends BaseController
{
    public function login()
    {
        if (session()->get('customer_id')) {
            return redirect()->to(site_url('account'));
        }

        return view('store/login');
    }

    public function account()
    {
        $customerId = (int) session()->get('customer_id');
        if ($customerId < 1) {
            return view('store/account_guest');
        }

        try {
            $customer = \Config\Database::connect()->table('customers')
                ->select('id, mobile, first_name, last_name')
                ->where('id', $customerId)->where('status', 'active')->get()->getRowArray();
            if ($customer === null) {
                session()->remove('customer_id');
                return redirect()->to(site_url('login'));
            }
            $orders = (new OrderService())->listForCustomer($customerId);
            return view('store/account', ['customer' => $customer, 'orders' => $orders]);
        } catch (Throwable $exception) {
            log_message('error', 'Customer account error: {type}', ['type' => get_class($exception)]);
            return view('store/unavailable');
        }
    }

    public function checkout()
    {
        $customerId = (int) session()->get('customer_id');
        if ($customerId < 1) {
            return redirect()->to(site_url('login?return=checkout'));
        }
        try {
            $cart = (new CartService())->summary();
            if ($cart['count'] < 1) {
                return redirect()->to(site_url('cart'));
            }
            $db = \Config\Database::connect();
            $customer = $db->table('customers')->select('mobile, first_name, last_name')
                ->where('id', $customerId)->where('status', 'active')->get()->getRowArray();
            if ($customer === null) {
                session()->remove('customer_id');
                return redirect()->to(site_url('login?return=checkout'));
            }

            $delivery = $db->table('orders')
                ->select('receiver_name, receiver_mobile, province, city, address, postal_code')
                ->where('customer_id', $customerId)->orderBy('id', 'DESC')->get(1)->getRowArray() ?? [];
            $delivery['receiver_name'] = ($delivery['receiver_name'] ?? '') ?: trim(
                ($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? '')
            );
            $delivery['receiver_mobile'] = ($delivery['receiver_mobile'] ?? '') ?: $customer['mobile'];
            $locations = \App\Services\IranLocations::all();
            if (!isset($locations[$delivery['province'] ?? ''])) {
                $delivery['province'] = '';
            }

            $this->response->setHeader('Cache-Control', 'private, no-store');
            return view('store/checkout', [
                'cart' => $cart,
                'delivery' => $delivery,
                'iranLocations' => $locations,
                'checkoutEnabled' => filter_var(env('GEREH_CHECKOUT_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
                'shippingMethods' => (new \App\Services\ShippingService())->quotes((int) $cart['total'], $delivery['province'] ?? ''),
            ]);
        } catch (Throwable $exception) {
            log_message('error', 'Checkout page error: {type}', ['type' => get_class($exception)]);
            return view('store/unavailable');
        }
    }
}
