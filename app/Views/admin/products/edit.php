<?= view('admin/partials/start',['title'=>$product?'افزودن و ویرایش محصول':'افزودن محصول','subtitle'=>$product?($product['name'].' • شناسه محصول: #'.$product['id']):'اطلاعات، قیمت و وضعیت محصول جدید','active'=>'products']) ?>
<main>
<?php if (session()->getFlashdata('errors')): ?><div class="admin-notice admin-error" role="alert"><?php foreach ((array)session()->getFlashdata('errors') as $error): ?><p><?= esc($error) ?></p><?php endforeach; ?></div><?php endif; ?>
<?php if (session()->getFlashdata('success')): ?><p class="admin-notice admin-success" role="status"><?= esc(session()->getFlashdata('success')) ?></p><?php endif; ?>
<div class="admin-form-actions"><a class="admin-button admin-button-soft" href="<?= site_url('admin/products') ?>">بازگشت به محصولات</a><?php if ($product && $product['status']==='active'): ?><a class="admin-button admin-button-soft" href="<?= site_url('product/'.$product['slug']) ?>" target="_blank" rel="noopener">پیش‌نمایش محصول</a><?php endif; ?><button type="submit" form="product-editor">ذخیره محصول</button></div>
<div class="admin-editor">
<div>
<section class="admin-panel"><h2>اطلاعات اصلی</h2>
<form id="product-editor" class="admin-form" method="post" action="<?= $product?site_url('admin/products/'.$product['id'].'/edit'):site_url('admin/products/create') ?>"><?= csrf_field() ?>
<div class="admin-fields">
<label class="admin-field-full">نام محصول<input required name="name" maxlength="220" value="<?= esc(old('name',$product['name']??''),'attr') ?>"></label>
<label>دسته‌بندی<select name="category_id"><option value="">بدون دسته</option><?php foreach($categories as $item): ?><option value="<?= (int)$item['id'] ?>" <?= (string)old('category_id',$product['category_id']??'')===(string)$item['id']?'selected':'' ?>><?= esc($item['name']) ?></option><?php endforeach; ?></select></label>
<label>برند<select name="brand_id"><option value="">بدون برند</option><?php foreach($brands as $item): ?><option value="<?= (int)$item['id'] ?>" <?= (string)old('brand_id',$product['brand_id']??'')===(string)$item['id']?'selected':'' ?>><?= esc($item['name']) ?></option><?php endforeach; ?></select></label>
<label>قیمت فروش (تومان)<input type="number" min="0" required name="price_toman" value="<?= esc((string)old('price_toman',$product['price_toman']??0),'attr') ?>"></label>
<label>نشانی انگلیسی (slug)<input required name="slug" pattern="[a-z0-9-]+" maxlength="220" dir="ltr" value="<?= esc(old('slug',$product['slug']??''),'attr') ?>"></label>
<label class="admin-field-full">توضیح کوتاه<textarea name="short_description" rows="3"><?= esc(old('short_description',$product['short_description']??'')) ?></textarea></label>
<label class="admin-field-full">توضیحات کامل<textarea name="description" rows="5"><?= esc(old('description',$product['description']??'')) ?></textarea></label>
</div></form>
<?php if ($product): ?>
<div class="admin-panel-heading" style="margin-top:24px"><h2>رنگ، سایز و موجودی</h2><a href="<?= site_url('admin/inventory?product_id='.(int)$product['id']) ?>">مدیریت تنوع‌ها</a></div>
<div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>رنگ</th><th>سایز</th><th>SKU</th><th>موجودی</th></tr></thead><tbody>
<?php foreach ($variants as $v): ?><tr><td><?= esc($v['color_name']??'—') ?></td><td><?= esc($v['size_label']??'—') ?></td><td><?= esc($v['sku']) ?></td><td><form class="admin-stock-form" method="post" action="<?= site_url('admin/products/'.$product['id'].'/variants/'.$v['id'].'/stock') ?>"><?= csrf_field() ?><input name="quantity" type="number" min="0" required aria-label="موجودی <?= esc($v['sku'],'attr') ?>" value="<?= (int)($v['quantity']??0) ?>"><button type="submit">ثبت</button></form></td></tr><?php endforeach; ?>
<?php if (!$variants): ?><tr><td colspan="4" class="admin-empty">تنوعی برای این محصول ثبت نشده است.</td></tr><?php endif; ?></tbody></table></div>
<details><summary>+ افزودن تنوع</summary><form class="admin-form" method="post" action="<?= site_url('admin/products/'.$product['id'].'/variants') ?>"><?= csrf_field() ?><div class="admin-fields"><label>SKU<input required name="sku" maxlength="80" dir="ltr"></label><label>سایز<input name="size_label" maxlength="60"></label><label>رنگ<input name="color_name" maxlength="80"></label><label>موجودی اولیه<input required type="number" min="0" name="quantity" value="0"></label></div><button type="submit">ثبت تنوع و موجودی</button></form></details>
<?php endif; ?></section>
</div>
<aside class="admin-editor-side">
<section class="admin-panel"><h2>وضعیت انتشار</h2><label class="admin-form">وضعیت<select name="status" form="product-editor"><?php foreach (['draft'=>'پیش‌نویس','active'=>'منتشر شده','archived'=>'بایگانی'] as $value=>$label): ?><option value="<?= $value ?>" <?= old('status',$product['status']??'draft')===$value?'selected':'' ?>><?= $label ?></option><?php endforeach; ?></select></label><p class="admin-muted" style="margin-top:16px">محصول منتشر شده در فروشگاه نمایش داده می‌شود.</p></section>
<section class="admin-panel"><h2>گالری محصول</h2>
<?php if ($media): ?><img class="admin-gallery-main" src="<?= esc(base_url(ltrim($media[0]['file_path'],'/')),'attr') ?>" alt="<?= esc($media[0]['alt_text']??$product['name'],'attr') ?>"><div class="admin-gallery-thumbs"><?php foreach ($media as $image): ?><img src="<?= esc(base_url(ltrim($image['file_path'],'/')),'attr') ?>" alt="<?= esc($image['alt_text']??'تصویر محصول','attr') ?>" width="72" height="72"><?php endforeach; ?></div><?php else: ?><div class="admin-gallery-empty">تصویری ثبت نشده است.</div><?php endif; ?>
<?php if ($product): ?><form class="admin-form" method="post" enctype="multipart/form-data" action="<?= site_url('admin/products/'.$product['id'].'/media') ?>"><?= csrf_field() ?><label>افزودن تصویر<input type="file" name="image" accept="image/jpeg,image/png,image/webp" required></label><label>متن جایگزین<input name="alt_text" maxlength="255"></label><span class="admin-muted">JPG، PNG یا WebP تا ۵ مگابایت</span><button type="submit">آپلود تصویر</button></form><?php else: ?><p class="admin-muted">پس از ذخیره محصول، تصویر و تنوع‌ها را اضافه کنید.</p><?php endif; ?>
</section></aside></div></main><?= view('admin/partials/end') ?>
