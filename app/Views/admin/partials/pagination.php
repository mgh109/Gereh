<?php
$page = max(1, (int) ($pagination['page'] ?? 1));
$last = max(1, (int) ($pagination['last_page'] ?? 1));
$total = (int) ($pagination['total'] ?? 0);
$perPage = max(1, (int) ($pagination['per_page'] ?? 25));
$query = service('request')->getGet();
$query = is_array($query) ? $query : [];
$pageUrl = static fn ($n) => site_url($path.'?'.http_build_query(array_merge($query, ['page' => $n])));
?>
<nav class="admin-pagination" aria-label="صفحه‌بندی">
<span><?= $total ? admin_number(($page - 1) * $perPage + 1).' تا '.admin_number(min($page * $perPage, $total)).' از '.admin_number($total).' مورد' : 'موردی برای نمایش وجود ندارد' ?></span>
<?php if ($last > 1): ?><div>
<?php if ($page > 1): ?><a href="<?= esc($pageUrl($page - 1), 'attr') ?>">قبلی</a><?php endif; ?>
<?php for ($n = max(1, $page - 2); $n <= min($last, $page + 2); $n++): ?><a href="<?= esc($pageUrl($n), 'attr') ?>" <?= $n === $page ? 'aria-current="page"' : '' ?>><?= $n ?></a><?php endfor; ?>
<?php if ($page < $last): ?><a href="<?= esc($pageUrl($page + 1), 'attr') ?>">بعدی</a><?php endif; ?>
</div><?php endif; ?>
</nav>
