<?php

namespace App\Commands;

use App\Services\OrderService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;
use DomainException;
use RuntimeException;

final class OrderSmoke extends BaseCommand
{
    protected $group = 'GEREH Tests';
    protected $name = 'gereh:smoke-orders';
    protected $description = 'CI-only real MySQL order creation and idempotency integration checks';

    public function run(array $params)
    {
        if ((string)env('GEREH_CI_SMOKE','') !== '1' || ENVIRONMENT === 'production') {
            throw new RuntimeException('Integration smoke is restricted to isolated CI.');
        }

        $db=Database::connect();
        $suffix=bin2hex(random_bytes(5));
        $mobile='099'.random_int(10000000,99999999);
        $now=date('Y-m-d H:i:s');

        $db->table('customers')->insert([
            'mobile'=>$mobile,'status'=>'active','created_at'=>$now,'updated_at'=>$now,
        ]);
        $customerId=(int)$db->insertID();
        $db->table('products')->insert([
            'name'=>'CI Smoke Shoe','slug'=>'ci-smoke-'.$suffix,
            'status'=>'active','price_toman'=>120000,
            'created_at'=>$now,'updated_at'=>$now,
        ]);
        $productId=(int)$db->insertID();
        $db->table('product_variants')->insert([
            'product_id'=>$productId,'sku'=>'CI-'.$suffix,
            'size_label'=>'42','color_name'=>'green',
            'is_active'=>1,'created_at'=>$now,'updated_at'=>$now,
        ]);
        $variantId=(int)$db->insertID();
        $db->table('inventory_items')->insert(['variant_id'=>$variantId,'quantity'=>2,'updated_at'=>$now]);

        $recipient=[
            'receiver_name'=>'Test Customer','receiver_mobile'=>$mobile,
            'province'=>'تهران','city'=>'تهران',
            'address'=>'آدرس ویژه تست CI','postal_code'=>'1234567890',
        ];
        $service=new OrderService($db);
        $idempotencyKey='ci-smoke-'.bin2hex(random_bytes(16));
        $first=$service->create($customerId,[$variantId=>2],$recipient,$idempotencyKey);
        $again=$service->create($customerId,[$variantId=>2],$recipient,$idempotencyKey);
        if ((int)$first['id']!==(int)$again['id']) {
            throw new RuntimeException('Duplicate checkout unexpectedly created another order.');
        }
        if ((int)$first['subtotal_toman']!==240000 || (int)$first['total_toman']!==265000) {
            throw new RuntimeException('Order totals do not match DB-backed prices and shipping.');
        }
        $stored=$db->table('order_items')->where('order_id',$first['id'])->get()->getRowArray();
        if (!$stored || (int)$stored['quantity']!==2 || (int)$stored['unit_price_toman']!==120000) {
            throw new RuntimeException('Order snapshot was not persisted correctly.');
        }
        $stock=$db->table('inventory_items')->where('variant_id',$variantId)->get()->getRowArray();
        if ((int)$stock['quantity']!==2) {
            throw new RuntimeException('Unpaid order incorrectly deducted stock.');
        }
        try {
            $service->create($customerId,[$variantId=>3],$recipient,'ci-overstock-'.bin2hex(random_bytes(16)));
            throw new RuntimeException('Oversell was not rejected.');
        } catch (DomainException $expected) {
            // Stock validation worked.
        }
        file_put_contents(WRITEPATH . 'cache/ci-variant-id.txt', (string) $variantId);
        CLI::write('PASS: order totals, price snapshot, idempotency, unpaid stock and oversell rejection.');
    }
}