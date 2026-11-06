<?= view('admin/orders/partials/top', ['title'=>'سفارش‌ها']) ?>
<main class="mx-auto max-w-6xl p-5">
<h1 class="mb-5 text-2xl font-bold">سفارش‌ها (<?= number_format((int)$pagination['total']) ?>)</h1>
<?php if ($error): ?><p role="alert"><?= esc($error) ?></p><?php endif; ?>
<form method="get" class="mb-5 flex flex-wrap gap-3"><select name="payment_status"><option value="">همه پرداخت‌ها</option><option value="paid">پرداخت‌شده</option><option value="unpaid">پرداخت‌نشده</option><option value="review_required">نیازمند بررسی</option></select><button type="submit">فیلتر</button></form>
<div class="overflow-x-auto rounded-xl bg-white p-4"><table class="w-full min-w-[600px] text-right text-sm"><thead><tr><th>سفارش</th><th>مشتری</th><th>مبلغ</th><th>پرداخت</th><th>وضعیت</th><th>عملیات</th></tr></thead><tbody>
<?php foreach($orders as $order): ?><tr class="border-b"><td class="p-3"><?= esc($order['order_number']) ?></td><td><?= esc($order['receiver_name']) ?></td><td><?= number_format((int)$order['total_toman']) ?> تومان</td><td><?= esc($order['payment_status']) ?></td><td><?= esc($order['order_status']) ?></td><td><a href="<?= site_url('admin/orders/'.(int)$order['id']) ?>">جزئیات</a></td></tr><?php endforeach; ?>
<?php if(!$orders): ?><tr><td colspan="6" class="p-5 text-center">سفارشی وجود ندارد.</td></tr><?php endif; ?></tbody></table></div>
</main></body></html>