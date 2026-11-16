<?= view('admin/partials/start',['title'=>$product?'ویرایش محصول':'افزودن محصول','subtitle'=>'مشخصات، تصاویر و تنوع‌ها را یکجا ثبت کنید.','active'=>'products']) ?>
<link rel="stylesheet" href="<?= base_url('assets/css/product-editor.css') ?>">
<main>
<?php if(session()->getFlashdata('errors')): ?><div class="admin-notice admin-error" role="alert"><?php foreach((array)session()->getFlashdata('errors') as $error): ?><p><?= esc($error) ?></p><?php endforeach; ?><p>اطلاعات فرم حفظ شده است؛ اگر عکس انتخاب کرده بودید، دوباره انتخاب کنید.</p></div><?php endif; ?>
<?php if(session()->getFlashdata('success')): ?><p class="admin-notice admin-success" role="status"><?= esc(session()->getFlashdata('success')) ?></p><?php endif; ?>
<form id="product-editor" class="admin-form" method="post" enctype="multipart/form-data" action="<?= $product?site_url('admin/products/'.$product['id'].'/edit'):site_url('admin/products/create') ?>">
<?= csrf_field() ?>
<div class="admin-form-actions editor-actions"><a class="admin-button admin-button-soft" href="<?= site_url('admin/products') ?>">بازگشت به محصولات</a><span id="editor-state" role="status"></span><button type="submit">ذخیره همه تغییرات</button></div>
<div class="admin-editor"><div>
<section class="admin-panel"><h2>اطلاعات اصلی</h2><div class="admin-fields">
<label class="admin-field-full">نام محصول<input required name="name" maxlength="220" value="<?= esc(old('name',$product['name']??''),'attr') ?>"></label>
<label>دسته‌بندی<select name="category_id"><option value="">بدون دسته</option><?php foreach($categories as $item): ?><option value="<?= (int)$item['id'] ?>" <?= (string)old('category_id',$product['category_id']??'')===(string)$item['id']?'selected':'' ?>><?= esc($item['name']) ?></option><?php endforeach; ?></select></label>
<label>برند<select name="brand_id" id="editor-brand"><option value="">بدون برند</option><?php foreach($brands as $item): ?><option value="<?= (int)$item['id'] ?>" <?= (string)old('brand_id',$product['brand_id']??'')===(string)$item['id']?'selected':'' ?>><?= esc($item['name']) ?></option><?php endforeach; ?></select></label>
<details class="admin-field-full" <?= old('new_brand')?'open':'' ?>><summary>+ افزودن برند جدید در همین فرم</summary><label>نام برند جدید<input name="new_brand" maxlength="160" value="<?= esc(old('new_brand',''),'attr') ?>" placeholder="مثلاً ASICS"></label><p class="admin-muted">با ذخیره محصول، برند جدید ایجاد و انتخاب می‌شود.</p></details>
<?php $hasProductSale = (int)($product['compare_at_price_toman']??0) > (int)($product['price_toman']??0); ?>
<label>قیمت اصلی (تومان)<input type="number" min="0" max="100000000000" step="1" required name="price_toman" value="<?= esc((string)old('price_toman',$hasProductSale?$product['compare_at_price_toman']:($product['price_toman']??0)),'attr') ?>"></label>
<label>قیمت با تخفیف (تومان)<input type="number" min="1" max="100000000000" step="1" name="sale_price_toman" value="<?= esc((string)old('sale_price_toman',$hasProductSale?$product['price_toman']:''),'attr') ?>" placeholder="اختیاری"></label>
<p class="admin-muted admin-field-full">قیمت با تخفیف باید از قیمت اصلی کمتر باشد. برای حذف تخفیف، کادر قیمت با تخفیف را خالی کنید.</p>
<label>نشانی انگلیسی محصول<input name="slug" pattern="[a-z0-9-]+" maxlength="220" dir="ltr" value="<?= esc(old('slug',$product['slug']??''),'attr') ?>" placeholder="خالی بگذارید تا خودکار ساخته شود"></label>
<?php if($editorFieldsReady ?? false): ?>
<label>کد کالا<input name="product_code" maxlength="80" value="<?= esc(old('product_code',$product['product_code']??''),'attr') ?>" placeholder="مثلاً ۱۳۸"></label>
<label>وضعیت کالا<select name="item_condition"><?php foreach([''=>'تعیین نشده','new'=>'نو','used'=>'کارکرده','stock'=>'استوک'] as $value=>$label): ?><option value="<?= $value ?>" <?= old('item_condition',$product['item_condition']??'')===$value?'selected':'' ?>><?= $label ?></option><?php endforeach; ?></select></label>
<?php else: ?><p class="admin-muted admin-field-full">فیلد کد کالا و نو/کارکرده پس از اجرای بروزرسانی دیتابیس این نسخه فعال می‌شود.</p><?php endif; ?>
<label class="admin-field-full">توضیح کوتاه<textarea name="short_description" rows="3"><?= esc(old('short_description',$product['short_description']??'')) ?></textarea></label>
<label class="admin-field-full">توضیحات کامل<textarea name="description" rows="5"><?= esc(old('description',$product['description']??'')) ?></textarea></label>
</div></section>
<section class="admin-panel"><div class="admin-panel-heading"><h2>رنگ، سایز و موجودی</h2><button type="button" id="add-variant">+ افزودن تنوع</button></div><p class="admin-muted">SKU خالی به‌صورت خودکار ساخته می‌شود. تغییر موجودی همراه محصول ذخیره خواهد شد.</p>
<div id="editor-variants">
<?php $editorRows=old('variants',$variants ?: [['id'=>'','size_label'=>'','color_name'=>'','sku'=>'','quantity'=>0]]); $editorRows=is_array($editorRows)?$editorRows:[]; foreach(array_values($editorRows) as $i=>$v): if(!is_array($v))continue; ?>
<div class="editor-variant" data-variant><input type="hidden" data-field="id" name="variants[<?= $i ?>][id]" value="<?= (int)($v['id']??0) ?>">
<?php if(!empty($v['id'])): ?><input type="hidden" data-field="original_quantity" name="variants[<?= $i ?>][original_quantity]" value="<?= (int)($v['original_quantity']??$v['quantity']??0) ?>"><?php endif; ?>
<label>سایز<input data-field="size_label" name="variants[<?= $i ?>][size_label]" maxlength="60" value="<?= esc($v['size_label']??'','attr') ?>" placeholder="۴۱٫۵"></label>
<label>رنگ<input data-field="color_name" name="variants[<?= $i ?>][color_name]" maxlength="80" value="<?= esc($v['color_name']??'','attr') ?>"></label>
<label>موجودی<input data-field="quantity" name="variants[<?= $i ?>][quantity]" type="number" min="0" max="100000000" required value="<?= (int)($v['quantity']??0) ?>"></label>
<label>SKU<input data-field="sku" name="variants[<?= $i ?>][sku]" maxlength="80" dir="ltr" value="<?= esc($v['sku']??'','attr') ?>" placeholder="خودکار"></label>
<?php if(empty($v['id'])): ?><button type="button" data-remove-variant aria-label="حذف ردیف تنوع جدید">×</button><?php endif; ?></div>
<?php endforeach; ?></div>
</section></div>
<aside class="admin-editor-side"><section class="admin-panel"><h2>وضعیت انتشار</h2><label>وضعیت<select name="status"><?php foreach(['draft'=>'پیش‌نویس','active'=>'منتشر شده','archived'=>'بایگانی'] as $value=>$label): ?><option value="<?= $value ?>" <?= old('status',$product['status']??'draft')===$value?'selected':'' ?>><?= $label ?></option><?php endforeach; ?></select></label><p class="admin-muted">برای آماده‌سازی محصول، پیش‌نویس را انتخاب کنید.</p></section>
<?php if(!empty($engagementStats)): ?><section class="admin-panel"><h2>آمار محصول</h2><p>بازدید کل: <?= (int)$engagementStats['views'] ?></p><p>بازدید امروز: <?= (int)$engagementStats['today_views'] ?></p><p>دیدگاه تأییدشده: <?= (int)$engagementStats['comments'] ?></p><a href="<?= site_url('admin/comments?status=&product_id='.(int)$product['id']) ?>">مدیریت دیدگاه‌ها</a><p class="admin-muted">بازدید تکراری در همان نشست تا ۳۰ دقیقه شمارش نمی‌شود؛ این عدد تعداد افراد یکتا نیست.</p></section><?php endif; ?>
<section class="admin-panel"><h2>سئوی محصول</h2><label>عنوان سئو<input name="meta_title" maxlength="255" value="<?= esc(old('meta_title',$product['meta_title']??''),'attr') ?>" placeholder="پیش‌فرض: نام محصول و گره"></label><label>توضیحات سئو<textarea name="meta_description" maxlength="320" rows="3"><?= esc(old('meta_description',$product['meta_description']??'')) ?></textarea></label><p class="admin-muted">عنوانی مشخص و توضیحی کوتاه درباره مدل، رنگ و سایز بنویسید.</p></section>
<section class="admin-panel"><h2>تصاویر محصول</h2>
<?php if($media): ?><div class="admin-gallery-thumbs"><?php foreach($media as $image): ?><img src="<?= esc(base_url(ltrim($image['file_path'],'/')),'attr') ?>" alt="<?= esc($image['alt_text']??'تصویر محصول','attr') ?>" width="72" height="72"><?php endforeach; ?></div><?php endif; ?>
<label>افزودن عکس‌ها<input type="file" name="images[]" id="editor-images" multiple accept="image/jpeg,image/png,image/webp"></label><p class="admin-muted">حداکثر ۱۰ عکس در هر بار؛ هر عکس حداکثر ۵ مگابایت. اولین عکس، تصویر اصلی محصول جدید می‌شود.</p><div id="image-previews" class="admin-gallery-thumbs" aria-live="polite"></div>
<label>متن جایگزین تصاویر جدید<input name="alt_text" maxlength="255" value="<?= esc(old('alt_text',''),'attr') ?>" placeholder="پیش‌فرض: نام محصول"></label>
</section><section class="admin-panel"><h2>پیش‌نمایش اطلاعات</h2><div id="editor-preview"><strong data-preview-name></strong><p><del data-preview-regular hidden style="color:#81766e"></del><br><strong data-preview-price></strong><small data-preview-discount hidden style="color:#cb3d2e"></small></p><p data-preview-description></p></div><p class="admin-muted">پیش‌نمایش محلی؛ اطلاعات هنوز ذخیره نشده است.</p><?php if($product && $product['status']==='active'): ?><a href="<?= site_url('product/'.$product['slug']) ?>" target="_blank" rel="noopener">مشاهده صفحه منتشرشده</a><?php endif; ?></section>
</aside></div>
<div class="admin-form-actions"><button type="submit">ذخیره همه تغییرات</button></div></form></main>
<script src="<?= base_url('assets/js/product-editor.js').'?v=20261116-pricing' ?>" defer></script>
<?= view('admin/partials/end') ?>
