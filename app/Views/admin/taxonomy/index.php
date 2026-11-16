<?= view('admin/partials/start',['title'=>'دسته‌بندی و برندها','subtitle'=>'ساختار فروشگاه، دسته‌ها و برندها','active'=>'taxonomy']) ?>
<main>
<?php foreach(['error','success'] as $notice): ?><?php if(session()->getFlashdata($notice)): ?><p class="admin-notice <?= $notice==='error'?'admin-error':'admin-success' ?>" role="status"><?= esc(session()->getFlashdata($notice)) ?></p><?php endif; ?><?php endforeach; ?>
<div class="admin-grid">
<?php foreach([['title'=>'دسته‌بندی‌ها','type'=>'categories','items'=>$categories],['title'=>'برندها','type'=>'brands','items'=>$brands]] as $group): ?>
<section class="admin-panel"><div class="admin-panel-heading"><h2><?= esc($group['title']) ?></h2><a href="#add-<?= $group['type'] ?>">+ افزودن</a></div>
<div class="admin-table-wrap"><table class="admin-table" style="min-width:300px"><thead><tr><th>نام</th><th>شناسه انگلیسی</th></tr></thead><tbody><?php foreach($group['items'] as $item): ?><tr><td><?= esc($item['name']) ?></td><td><span dir="ltr"><?= esc($item['slug']) ?></span></td></tr><?php endforeach; ?><?php if(!$group['items']): ?><tr><td colspan="2" class="admin-empty">موردی ثبت نشده است.</td></tr><?php endif; ?></tbody></table></div>
<details id="add-<?= $group['type'] ?>" style="margin-top:20px"><summary>افزودن <?= $group['type']==='brands'?'برند':'دسته‌بندی' ?></summary><form class="admin-form" action="<?= site_url('admin/taxonomy/'.$group['type']) ?>" method="post" style="margin-top:16px"><?= csrf_field() ?><label>نام<input name="name" maxlength="160" required></label><label>شناسه انگلیسی<input name="slug" pattern="[a-z0-9-]+" maxlength="190" required dir="ltr"></label><button type="submit">افزودن</button></form></details>
</section><?php endforeach; ?></div>
<aside class="admin-notice" style="margin-top:20px"><strong>نمایش در فروشگاه</strong><p>دسته‌بندی و برند ثبت‌شده در فرم اطلاعات محصول قابل انتخاب است.</p></aside>
</main><?= view('admin/partials/end') ?>
