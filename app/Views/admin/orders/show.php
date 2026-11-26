<?php $statusLabels=['processing'=>'در حال پردازش','pending_payment'=>'در انتظار پرداخت','shipped'=>'ارسال شده','delivered'=>'تحویل شده','cancelled'=>'لغو شده','payment_review'=>'بررسی پرداخت']; ?>
<?= view('admin/partials/start',['title'=>'جزئیات سفارش '.$order['order_number'],'subtitle'=>'سفارش‌ها / جزئیات سفارش • '.($order['created_at']??''),'active'=>'orders']) ?>
<main>
<?php foreach(['order_error','order_success'] as $notice): ?><?php if(session()->getFlashdata($notice)): ?><p role="status" class="admin-notice <?= $notice==='order_error'?'admin-error':'admin-success' ?>"><?= esc(session()->getFlashdata($notice)) ?></p><?php endif; ?><?php endforeach; ?>
<div class="admin-form-actions"><a class="admin-button admin-button-soft" href="<?= site_url('admin/orders') ?>">بازگشت به سفارش‌ها</a><span class="admin-badge <?= in_array($order['order_status'],['processing','pending_payment','payment_review'],true)?'admin-badge-warning':($order['order_status']==='cancelled'?'admin-badge-danger':'') ?>"><?= esc($statusLabels[$order['order_status']]??$order['order_status']) ?></span></div>
<div class="admin-detail-grid">
<aside class="admin-panel admin-detail-aside"><h2>اطلاعات مشتری</h2><div class="admin-contact">
<div><h3><?= esc($order['receiver_name']) ?></h3><p><bdi><?= esc($order['receiver_mobile']) ?></bdi></p></div>
<div><h3>نشانی ارسال</h3><p><?= esc($order['province']) ?>، <?= esc($order['city']) ?>، <?= esc($order['address']) ?></p><p class="admin-muted">کد پستی: <?= esc($order['postal_code']) ?></p></div>
<div><p>روش ارسال: <?= esc($order['shipping_title']??$order['shipping_method']) ?></p><?php if(!empty($order['tracking_code'])): ?><p>کد رهگیری: <bdi><?= esc($order['tracking_code']) ?></bdi></p><?php endif; ?></div>
</div></aside>
<section class="admin-panel admin-detail-main"><h2>اقلام سفارش</h2><div class="admin-table-wrap"><table class="admin-table" style="min-width:500px"><thead><tr><th>محصول</th><th>رنگ و سایز</th><th>تعداد</th><th>مبلغ (تومان)</th></tr></thead><tbody>
<?php foreach($order['items'] as $item): ?><tr><td><?= esc($item['product_name']) ?><div class="admin-muted"><bdi><?= esc($item['sku']) ?></bdi></div></td><td><?= esc($item['color_name']??'—') ?> / <?= esc($item['size_label']??'—') ?></td><td><?= (int)$item['quantity'] ?></td><td><?= admin_number((int)$item['subtotal_toman']) ?></td></tr><?php endforeach; ?></tbody></table></div>
<dl class="admin-summary"><div><dt>جمع محصولات</dt><dd><?= admin_number(array_sum(array_column($order['items'],'subtotal_toman'))) ?> تومان</dd></div><div><dt>هزینه ارسال</dt><dd><?= admin_number((int)$order['shipping_toman']) ?> تومان</dd></div><div><dt>مبلغ نهایی</dt><dd><?= admin_number((int)$order['total_toman']) ?> تومان</dd></div><div><dt>روش پرداخت</dt><dd><?= esc(admin_payment_provider($order['payment_provider'] ?? null)) ?></dd></div><div><dt>وضعیت پرداخت</dt><dd><?= esc(['paid'=>'پرداخت موفق','unpaid'=>'پرداخت نشده','review_required'=>'نیازمند بررسی','failed'=>'ناموفق'][$order['payment_status']]??$order['payment_status']) ?></dd></div></dl>
</section></div>
<?php
$statusAction = match (true) {
    $order['payment_status'] === 'paid' && $order['order_status'] === 'processing' => 'ship',
    $order['payment_status'] === 'paid' && $order['order_status'] === 'shipped' => 'deliver',
    $order['payment_status'] === 'unpaid' && $order['order_status'] === 'pending_payment'
        && array_diff(array_column($order['payments'], 'status'), ['failed']) === [] => 'cancel',
    default => null,
};
$nextStatusLabel = ['ship' => 'ارسال شده', 'deliver' => 'تحویل شده', 'cancel' => 'لغو شده'];
?>
<section class="admin-panel admin-detail-wide">
<h2>ویرایش وضعیت سفارش</h2>
<?php if ($statusAction !== null): ?>
<form class="admin-form" method="post" action="<?= site_url('admin/orders/'.(int)$order['id'].'/fulfillment') ?>" <?php if ($statusAction === 'cancel'): ?>onsubmit="return confirm('سفارش لغو شود؟')"<?php endif; ?>>
<?= csrf_field() ?>
<label>وضعیت سفارش
<select name="action" required>
<option value=""><?= esc($statusLabels[$order['order_status']]) ?> (وضعیت فعلی)</option>
<option value="<?= esc($statusAction, 'attr') ?>"><?= esc($nextStatusLabel[$statusAction]) ?></option>
</select>
</label>
<?php if ($statusAction === 'ship'): ?>
<label>کد رهگیری مرسوله<input type="text" name="tracking_code" required minlength="4" maxlength="100" pattern="[a-zA-Z0-9_-]+" dir="ltr"></label>
<?php endif; ?>
<button type="submit">ذخیرهٔ وضعیت</button>
</form>
<?php else: ?>
<p class="admin-muted">برای وضعیت فعلی این سفارش تغییری در دسترس نیست.</p>
<?php endif; ?>
</section>
<section class="admin-panel admin-detail-wide"><h2>تاریخچه پرداخت و پیگیری</h2><div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>درگاه</th><th>وضعیت</th><th>مبلغ (تومان)</th><th>شناسه پیگیری</th><th>زمان ثبت</th></tr></thead><tbody>
<?php foreach($order['payments'] as $payment): ?><tr><td><?= esc(admin_payment_provider($payment['provider'])) ?></td><td><?= esc($payment['status']) ?></td><td><?= admin_number((int)$payment['amount_toman']) ?></td><td><bdi><?= esc($payment['ref_id']??'—') ?></bdi></td><td><?= esc(admin_date($payment['created_at']??null,true)) ?></td></tr><?php endforeach; ?>
<?php if(!$order['payments']): ?><tr><td colspan="5" class="admin-empty">تراکنشی ثبت نشده است.</td></tr><?php endif; ?></tbody></table></div></section>
</main><?= view('admin/partials/end') ?>
