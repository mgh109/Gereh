<?php $sizeValue = $sizeValue ?? ''; $sizeValue = is_string($sizeValue) ? $sizeValue : ''; ?>
<label for="<?= esc($sizeId, 'attr') ?>" class="<?= !empty($adminSize) ? 'sr-only' : '' ?>">جست‌وجو و فیلتر سایز</label>
<input class="<?= !empty($adminSize) ? 'admin-size-filter' : '' ?>" type="search" name="size" id="<?= esc($sizeId, 'attr') ?>" list="<?= esc($sizeId, 'attr') ?>-options" value="<?= esc($sizeValue, 'attr') ?>" maxlength="60" placeholder="همه سایزها؛ مثلاً ۴۰" aria-label="جست‌وجو و فیلتر سایز" autocomplete="off">
<datalist id="<?= esc($sizeId, 'attr') ?>-options"><?php foreach (($sizes ?? []) as $sizeOption): ?><option value="<?= esc((string)$sizeOption, 'attr') ?>"></option><?php endforeach; ?></datalist>
