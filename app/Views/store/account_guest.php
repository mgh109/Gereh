<?= view('store/partials/header', ['title'=>'حساب کاربری | گره']) ?>
<main class="account-screen gereh-container">
<h1 class="font-bold">حساب کاربری</h1><p class="gereh-muted mt-2">خریدها و اطلاعاتت را از اینجا مدیریت کن</p>
<section class="account-welcome"><h2>به گره خوش اومدی</h2><p>برای مشاهده سفارش‌ها وارد حساب شو</p><a class="mt-3 inline-block text-xs font-bold" href="<?= site_url('login') ?>">ورود / ثبت‌نام ‹</a></section>
<nav class="account-links" aria-label="مدیریت حساب"><a href="<?= site_url('login?return=account') ?>">سفارش‌های من</a><a href="<?= site_url('favorites') ?>">علاقه‌مندی‌ها</a><a href="<?= site_url('login?return=account') ?>">اطلاعات حساب</a><a href="<?= site_url('blog') ?>">راهنمای خرید</a><a href="<?= site_url('size-guide') ?>">راهنمای انتخاب سایز</a></nav>
<p class="gereh-muted my-8 text-center">گره؛ همراه هر قدم</p>
</main><?= view('store/partials/footer', ['hideFooter'=>true]) ?>
