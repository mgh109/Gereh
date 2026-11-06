<?= view('admin/orders/partials/top',['title'=>'جزئیات مشتری']) ?>
<main class="mx-auto max-w-5xl p-5"><a href="<?= site_url('admin/customers') ?>">بازگشت به مشتریان</a>
<h1 class="my-5 text-2xl font-bold">پرونده مشتری #<?= (int)$customer['id'] ?></h1>
<section class="mb-5 grid gap-3 rounded-xl bg-white p-5 text-sm sm:grid-cols-2">
<p>نام: <?= esc($customer['first_name']??'') ?> <?= esc($customer['last_name']??'') ?></p>
<p>شماره تماس: <b dir="ltr"><?= esc($customer['mobile']) ?></b></p>
<p>وضعیت: <?= esc($customer['status']) ?></p><p>عضویت: <?= esc($customer['created_at']) ?></p>
</section>
<h2 class="mb-4 font-bold">سفارش‌های این مشتری</h2>
<div class="overflow-auto rounded-xl bg-white p-4"><table class="w-full min-w-[550px] text-right text-sm"><thead><tr><th class="p-3">شماره سفارش</th><th>مبلغ</th><th>پرداخت</th><th>وضعیت</th><th>جزئیات</th></tr></thead><tbody>
<?php foreach($customer['orders'] as $order): ?><tr class="border-b"><td class="p-3"><?= esc($order['order_number']) ?></td><td><?= number_format((int)$order['total_toman']) ?> تومان</td><td><?= esc($order['payment_status']) ?></td><td><?= esc($order['order_status']) ?></td><td><a href="<?= site_url('admin/orders/'.(int)$order['id']) ?>">مشاهده</a></td></tr><?php endforeach; ?>
<?php if(!$customer['orders']): ?><tr><td colspan="5" class="p-6">سفارشی ثبت نشده است.</td></tr><?php endif; ?>
</tbody></table></div></main></body></html>