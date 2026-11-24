<?php
$title = $paid ? 'پرداخت موفق' : ($retry ? 'پرداخت ناموفق' : 'نتیجه پرداخت');
$heading = $paid ? 'سفارشت ثبت شد!' : ($retry ? 'پرداخت کامل نشد' : 'پرداخت تأیید نشد');
$description = $paid
    ? 'ممنون که گره را برای قدم‌هایت انتخاب کردی.'
    : ($retry ? 'می‌توانی دوباره پرداخت کنی یا به سبد خرید برگردی.' : $message);
?>
<?= view('store/partials/header', ['title' => $title . ' | گره', 'robots' => 'noindex, nofollow']) ?>
<style>
.payment-result{max-width:390px;min-height:calc(100svh - 93px);margin:0 auto;padding:19px 18px 36px;color:#012f24}
.payment-result h1{display:flex;align-items:center;min-height:40px;margin:0;font-size:23px;font-weight:700}
.payment-result-mark{display:grid;place-items:center;width:88px;height:88px;border-radius:50%;margin:53px auto 24px;background:#a75d4b}
.payment-result-mark.is-paid{background:#012f24}
.payment-result h2{display:grid;place-items:center;min-height:44px;margin:0;text-align:center;font-size:25px;font-weight:700}
.payment-result-description{display:grid;place-items:center;min-height:50px;margin:11px 6px 0;text-align:center;color:#736c66;font-size:12px}
.payment-result-summary{display:grid;align-content:start;gap:10px;min-height:102px;margin:28px 0 0;padding:11px 18px;border-radius:13px;background:#efe3d4;font-size:13px}
.payment-result-summary p{display:flex;align-items:center;gap:4px;min-height:31px;margin:0}
.payment-result-summary p + p{color:#736c66}
.payment-result-actions{display:grid;gap:20px;margin-top:38px}
.payment-result-actions a{display:grid;place-items:center;min-height:50px;border-radius:11px;background:#012f24;color:#efe3d4;font-size:13px;font-weight:700}
.payment-result-actions a + a{background:#efe3d4;color:#012f24}
</style>
<main class="payment-result" aria-labelledby="payment-result-title">
    <h1 id="payment-result-title"><?= esc($title) ?></h1>
    <div class="payment-result-mark<?= $paid ? ' is-paid' : '' ?>">
        <img src="<?= base_url('assets/figma/' . ($paid ? 'e2622.svg' : '78310.svg')) ?>" alt="" width="44" height="44">
    </div>
    <h2><?= esc($heading) ?></h2>
    <p class="payment-result-description"><?= esc($description) ?></p>
    <?php if ($order !== null): ?>
        <section class="payment-result-summary" aria-label="اطلاعات سفارش">
            <p><strong>شماره سفارش</strong><bdi dir="ltr"><?= esc($order['order_number']) ?></bdi></p>
            <p>مبلغ سفارش <bdi><?= esc(strtr(number_format((int) $order['amount_toman']), [
                '0'=>'۰', '1'=>'۱', '2'=>'۲', '3'=>'۳', '4'=>'۴', '5'=>'۵', '6'=>'۶', '7'=>'۷', '8'=>'۸', '9'=>'۹', ','=>'٬',
            ])) ?></bdi> تومان</p>
        </section>
    <?php endif; ?>
    <nav class="payment-result-actions" aria-label="ادامه پس از پرداخت">
        <?php if ($paid): ?>
            <a href="<?= site_url('account') ?>">مشاهده سفارش</a>
            <a href="<?= site_url('shop') ?>">ادامه خرید</a>
        <?php elseif ($retry): ?>
            <a href="<?= site_url('checkout') ?>">تلاش مجدد برای پرداخت</a>
            <a href="<?= site_url('cart') ?>">بازگشت به سبد خرید</a>
        <?php else: ?>
            <a href="<?= site_url('account') ?>">مشاهده سفارش‌ها</a>
            <a href="<?= site_url('shop') ?>">بازگشت به فروشگاه</a>
        <?php endif; ?>
    </nav>
</main>
<?= view('store/partials/footer', ['hideBottomNav' => true, 'hideFooter' => true]) ?>
