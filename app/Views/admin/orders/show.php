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
<dl class="admin-summary"><div><dt>جمع محصولات</dt><dd><?= admin_number(array_sum(array_column($order['items'],'subtotal_toman'))) ?> تومان</dd></div><div><dt>هزینه ارسال</dt><dd><?= admin_number((int)$order['shipping_toman']) ?> تومان</dd></div><div><dt>مبلغ نهایی</dt><dd><?= admin_number((int)$order['total_toman']) ?> تومان</dd></div><div><dt>وضعیت پرداخت</dt><dd><?= esc(['paid'=>'پرداخت موفق','unpaid'=>'پرداخت نشده','review_required'=>'نیازمند بررسی','failed'=>'ناموفق'][$order['payment_status']]??$order['payment_status']) ?></dd></div></dl>
</section></div>
<?php if($order['payment_status']==='unpaid' && $order['order_status']==='pending_payment' && empty($order['payments'])): ?>
<section class="admin-panel admin-detail-wide"><h2>لغو سفارش پرداخت‌نشده</h2><form method="post" action="<?= site_url('admin/orders/'.(int)$order['id'].'/fulfillment') ?>" onsubmit="return confirm('سفارش پرداخت‌نشده لغو شود؟')"><?= csrf_field() ?><input type="hidden" name="action" value="cancel"><button type="submit">لغو و ثبت اعلان مشتری</button></form></section>
<?php endif; ?>
<?php if($order['payment_status']==='paid' && in_array($order['order_status'],['processing','shipped'],true)): ?>
<section class="admin-panel admin-detail-wide"><h2>وضعیت ارسال و اطلاع‌رسانی مشتری</h2><form class="admin-form" method="post" action="<?= site_url('admin/orders/'.(int)$order['id'].'/fulfillment') ?>"><?= csrf_field() ?>
<?php if($order['order_status']==='processing'): ?><input type="hidden" name="action" value="ship"><label>کد رهگیری مرسوله<input type="text" name="tracking_code" required minlength="4" maxlength="100" pattern="[a-zA-Z0-9_-]+" placeholder="کد رهگیری" dir="ltr"></label><button type="submit">ثبت ارسال و صف پیامک</button>
<?php else: ?><input type="hidden" name="action" value="deliver"><p>کد رهگیری: <bdi><?= esc($order['tracking_code']??'—') ?></bdi></p><button type="submit">تأیید تحویل و صف پیامک</button><?php endif; ?>
</form><p class="admin-muted" style="margin-top:16px">اطلاع‌رسانی در صورت فعال‌بودن الگوی رویداد، توسط پردازشگر صف انجام می‌شود.</p></section><?php endif; ?>
<section class="admin-panel admin-detail-wide"><h2>تاریخچه پرداخت و پیگیری</h2><div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>درگاه</th><th>وضعیت</th><th>مبلغ (تومان)</th><th>شناسه پیگیری</th><th>زمان ثبت</th></tr></thead><tbody>
<?php foreach($order['payments'] as $payment): ?><tr><td><?= esc($payment['provider']) ?></td><td><?= esc($payment['status']) ?></td><td><?= admin_number((int)$payment['amount_toman']) ?></td><td><bdi><?= esc($payment['ref_id']??'—') ?></bdi></td><td><?= esc(admin_date($payment['created_at']??null,true)) ?></td></tr><?php endforeach; ?>
<?php if(!$order['payments']): ?><tr><td colspan="5" class="admin-empty">تراکنشی ثبت نشده است.</td></tr><?php endif; ?></tbody></table></div></section>
</main><?= view('admin/partials/end') ?>
