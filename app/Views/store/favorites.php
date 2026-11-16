<?= view('store/partials/header', ['title'=>'علاقه‌مندی‌ها | گره']) ?>
<main class="favorites-screen gereh-container" data-product-link="<?= esc(site_url('product/'),'attr') ?>">
<header><h1>علاقه‌مندی‌ها</h1><p class="gereh-muted"><span id="favorites-count">۰</span> محصول برای خریدهای بعدی شما</p></header>
<div id="favorites-grid" class="home-products"></div>
<div id="favorites-empty" class="gereh-card p-8 text-center" hidden><p class="gereh-muted mb-5">هنوز محصولی به علاقه‌مندی‌ها اضافه نکرده‌اید.</p><a class="gereh-button" href="<?= site_url('shop') ?>">مشاهده محصولات</a></div>
</main><?= view('store/partials/footer', ['hideFooter'=>true]) ?>
