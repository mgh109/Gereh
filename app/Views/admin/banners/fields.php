<form class="admin-form" action="<?= site_url('admin/banners') ?>" method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$banner['id'] ?>"><div class="admin-fields">
<label>جایگاه<select name="slot"><?php foreach(['hero'=>'بنر اصلی','women'=>'زنانه','men'=>'مردانه','promo'=>'تخفیف'] as $value=>$label): ?><option value="<?= $value ?>" <?= $banner['slot']===$value?'selected':'' ?>><?= $label ?></option><?php endforeach; ?></select></label>
<label>عنوان<input name="title" maxlength="220" required value="<?= esc($banner['title'],'attr') ?>"></label>
<label>زیرعنوان<input name="subtitle" maxlength="500" value="<?= esc($banner['subtitle']??'','attr') ?>"></label>
<label>مسیر لینک<input name="link_path" required value="<?= esc($banner['link_path'],'attr') ?>" dir="ltr"></label>
<label style="display:block"><input type="checkbox" name="show_text" value="1" <?= $banner['show_text']?'checked':'' ?>> نمایش متن روی تصویر</label>
<label style="display:block"><input type="checkbox" name="is_active" value="1" <?= $banner['is_active']?'checked':'' ?>> فعال</label>
</div><div><button type="submit">ذخیره اطلاعات</button></div></form>
