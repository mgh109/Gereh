<?= view('admin/partials/start',['title'=>'مدیران','subtitle'=>'تعریف مدیر و مدیریت دسترسی به پنل','active'=>'users']) ?>
<main>
<?php foreach(['success','error'] as $notice): if(session()->getFlashdata($notice)): ?><p role="status" class="admin-notice"><?= esc(session()->getFlashdata($notice)) ?></p><?php endif; endforeach; ?>
<p class="admin-notice">مدیر اصلی می‌تواند مدیران را مدیریت کند. مدیر فروشگاه به بخش‌های عملیاتی پنل دسترسی دارد و اجازه مدیریت حساب مدیران را ندارد.</p>
<section class="admin-panel"><div class="admin-panel-heading"><h2>مدیران فروشگاه</h2><a href="#new-admin">+ افزودن مدیر</a></div>
<?php foreach($users as $user): ?>
<details style="border-bottom:1px solid #eee;padding:16px 0"><summary style="cursor:pointer"><strong><?= esc($user['name']) ?></strong> — <?= $user['role']==='super_admin'?'مدیر اصلی':'مدیر فروشگاه' ?> — <?= $user['status']==='active'?'فعال':'غیرفعال' ?></summary>
<form class="admin-form" method="post" action="<?= site_url('admin/users/'.(int)$user['id']) ?>" style="margin-top:16px"><?= csrf_field() ?>
<label>نام<input name="name" value="<?= esc($user['name'],'attr') ?>" maxlength="160" required></label>
<label>ایمیل ورود<input type="email" name="email" value="<?= esc($user['email'],'attr') ?>" maxlength="190" dir="ltr" required autocomplete="off"></label>
<label>نقش<select name="role"><option value="admin" <?= $user['role']==='admin'?'selected':'' ?>>مدیر فروشگاه</option><option value="super_admin" <?= $user['role']==='super_admin'?'selected':'' ?>>مدیر اصلی</option></select></label>
<label>وضعیت<select name="status"><option value="active" <?= $user['status']==='active'?'selected':'' ?>>فعال</option><option value="inactive" <?= $user['status']!=='active'?'selected':'' ?>>غیرفعال</option></select></label>
<label>رمز جدید (برای حفظ رمز فعلی خالی بگذار)<input type="password" name="password" minlength="12" maxlength="72" autocomplete="new-password" dir="ltr"></label>
<label>تکرار رمز جدید<input type="password" name="password_confirmation" minlength="12" maxlength="72" autocomplete="new-password" dir="ltr"></label>
<button type="submit">ذخیره تغییرات</button>
</form></details><?php endforeach; ?></section>
<section class="admin-panel" id="new-admin" style="margin-top:24px"><h2>افزودن مدیر</h2><form class="admin-form" method="post" action="<?= site_url('admin/users') ?>"><?= csrf_field() ?>
<label>نام<input name="name" maxlength="160" required autocomplete="off"></label><label>ایمیل ورود<input name="email" type="email" maxlength="190" dir="ltr" required autocomplete="off"></label>
<label>نقش<select name="role"><option value="admin">مدیر فروشگاه</option><option value="super_admin">مدیر اصلی</option></select></label><input name="status" type="hidden" value="active">
<label>رمز ورود (حداقل ۱۲ کاراکتر)<input name="password" type="password" minlength="12" maxlength="72" required autocomplete="new-password" dir="ltr"></label><label>تکرار رمز<input name="password_confirmation" type="password" minlength="12" maxlength="72" required autocomplete="new-password" dir="ltr"></label><button type="submit">افزودن مدیر</button></form></section>
</main><?= view('admin/partials/end') ?>
