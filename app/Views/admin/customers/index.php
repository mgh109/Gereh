<?= view('admin/orders/partials/top',['title'=>'مشتریان']) ?>
<main class="mx-auto max-w-6xl p-5">
<h1 class="mb-5 text-2xl font-bold">مدیریت مشتریان</h1>
<form class="mb-4 flex gap-3" method="get"><input class="w-full max-w-sm rounded-lg border p-3" name="q" type="search" placeholder="جست‌وجوی نام یا موبایل" value="<?= esc(service('request')->getGet('q')??'','attr') ?>"><button class="rounded-lg bg-[#012f24] px-5 text-white" type="submit">جست‌وجو</button></form>
<div class="overflow-auto rounded-xl bg-white p-4"><table class="w-full min-w-[600px] text-right text-sm"><thead><tr><th class="p-3">شناسه</th><th>نام و نام خانوادگی</th><th>موبایل</th><th>وضعیت</th><th>مشاهده</th></tr></thead><tbody>
<?php foreach($data as $customer): ?><tr class="border-b"><td class="p-3"><?= (int)$customer['id'] ?></td><td><?= esc(trim(($customer['first_name']??'').' '.($customer['last_name']??''))) ?></td><td dir="ltr"><?= esc($customer['mobile']) ?></td><td><?= esc($customer['status']) ?></td><td><a href="<?= site_url('admin/customers/'.(int)$customer['id']) ?>">جزئیات</a></td></tr><?php endforeach; ?>
<?php if(!$data): ?><tr><td colspan="5" class="p-6 text-center">مشتری ثبت نشده است.</td></tr><?php endif; ?>
</tbody></table></div>
<?php $page=(int)$meta['page']; $last=(int)$meta['last_page']; $q=trim((string)(service('request')->getGet('q')??'')); ?>
<?php if($last>1): ?><nav class="mt-5 flex justify-between text-sm" aria-label="صفحه‌بندی مشتریان"><span>صفحه <?= $page ?> از <?= $last ?></span><div class="flex gap-4">
<?php if($page>1): ?><a href="<?= site_url('admin/customers?'.http_build_query(['page'=>$page-1,'q'=>$q])) ?>">قبلی</a><?php endif; ?>
<?php if($page<$last): ?><a href="<?= site_url('admin/customers?'.http_build_query(['page'=>$page+1,'q'=>$q])) ?>">بعدی</a><?php endif; ?>
</div></nav><?php endif; ?>
</main></body></html>