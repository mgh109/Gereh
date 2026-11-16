<?php if (!$products): ?><div class="empty" role="status">محصولی با این جست‌وجو و فیلترها پیدا نشد. سایز یا فیلترهای دیگر را تغییر بده.</div><?php else: ?>
<?= view('store/partials/home_cards', ['products'=>$products, 'compact'=>false]) ?>
<?php endif; ?>
