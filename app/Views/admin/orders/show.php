<?= view('admin/orders/partials/top', ['title'=>'جزئیات سفارش']) ?>
<main class="mx-auto max-w-5xl p-5">
<a href="<?= site_url('admin/orders') ?>" class="text-sm text-stone-500">بازگشت به سفارش‌ها</a>
<h1 class="my-5 text-2xl font-bold">سفارش <?= esc($order['order_number']) ?></h1>
<?php if(session()->getFlashdata('order_error')): ?><p role="alert" class="my-4 rounded-xl bg-red-50 p-4 text-sm text-red-700"><?= esc(session()->getFlashdata('order_error')) ?></p><?php endif; ?>
<?php if(session()->getFlashdata('order_success')): ?><p role="status" class="my-4 rounded-xl bg-green-50 p-4 text-sm text-green-800"><?= esc(session()->getFlashdata('order_success')) ?></p><?php endif; ?>
<?php if($order['payment_status']==='paid' && in_array($order['order_status'],['processing','shipped'],true)): ?>
<section class="mb-5 rounded-xl bg-white p-5">
<h2 class="mb-3 font-bold">وضعیت ارسال و اطلاع‌رسانی مشتری</h2>
<form method="post" action="<?= site_url('admin/orders/'.(int)$order['id'].'/fulfillment') ?>" class="grid gap-3 sm:grid-cols-2">
<?= csrf_field() ?>
<?php if($order['order_status']==='processing'): ?>
<input type="hidden" name="action" value="ship">
<label class="grid gap-2 text-sm">کد رهگیری مرسوله<input type="text" name="tracking_code" required minlength="4" maxlength="100" pattern="[a-zA-Z0-9_-]+" class="rounded-lg border border-stone-200 p-3" placeholder="کد رهگیری"></label>
<button type="submit" class="rounded-lg bg-[#012f24] p-3 text-white">ثبت ارسال و صف پیامک</button>
<?php else: ?>
<input type="hidden" name="action" value="deliver">
<p class="text-sm">کد رهگیری: <b dir="ltr"><?= esc($order['tracking_code']??'—') ?></b></p>
<button type="submit" class="rounded-lg bg-[#012f24] p-3 text-white">تأیید تحویل و صف پیامک</button>
<?php endif; ?>
</form>
<p class="mt-3 text-xs text-stone-500">اطلاع‌رسانی فقط در صورت فعال‌بودن الگوی این رویداد و ارسال توسط پردازشگر صف انجام می‌شود.</p>
</section>
<?php endif; ?>
<div class="mb-5 grid gap-4 md:grid-cols-2">
<section class="rounded-xl bg-white p-5"><h2 class="mb-3 font-bold">اطلاعات تحویل</h2><p><?= esc($order['receiver_name']) ?> — <?= esc($order['receiver_mobile']) ?></p><p class="mt-2 text-sm"><?= esc($order['province']) ?>، <?= esc($order['city']) ?>، <?= esc($order['address']) ?></p><p class="mt-2 text-sm">کد پستی: <?= esc($order['postal_code']) ?></p></section>
<section class="rounded-xl bg-white p-5"><h2 class="mb-3 font-bold">وضعیت مالی و سفارش</h2><p>وضعیت سفارش: <?= esc($order['order_status']) ?></p><p class="mt-2">وضعیت پرداخت: <?= esc($order['payment_status']) ?></p><p class="mt-2">جمع نهایی: <strong><?= number_format((int)$order['total_toman']) ?> تومان</strong></p></section>
</div>
<section class="mb-5 overflow-x-auto rounded-xl bg-white p-5"><h2 class="mb-3 font-bold">اقلام خریداری‌شده</h2><table class="w-full min-w-[550px] text-right text-sm"><thead><tr><th class="p-2">محصول</th><th>SKU</th><th>سایز / رنگ</th><th>تعداد</th><th>قیمت</th></tr></thead><tbody><?php foreach($order['items'] as $item): ?><tr class="border-t"><td class="p-2"><?= esc($item['product_name']) ?></td><td><?= esc($item['sku']) ?></td><td><?= esc($item['size_label']) ?> / <?= esc($item['color_name']) ?></td><td><?= (int)$item['quantity'] ?></td><td><?= number_format((int)$item['subtotal_toman']) ?> تومان</td></tr><?php endforeach; ?></tbody></table></section>
<section class="overflow-x-auto rounded-xl bg-white p-5"><h2 class="mb-3 font-bold">تراکنش‌های پرداخت</h2><table class="w-full min-w-[550px] text-right text-sm"><thead><tr><th class="p-2">درگاه</th><th>وضعیت</th><th>مبلغ</th><th>شناسه پیگیری</th></tr></thead><tbody><?php foreach($order['payments'] as $payment): ?><tr class="border-t"><td class="p-2"><?= esc($payment['provider']) ?></td><td><?= esc($payment['status']) ?></td><td><?= number_format((int)$payment['amount_toman']) ?> تومان</td><td><?= esc($payment['ref_id']??'—') ?></td></tr><?php endforeach; ?></tbody></table></section>
</main></body></html>