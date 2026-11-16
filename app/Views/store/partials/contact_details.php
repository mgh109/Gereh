<?php $contact = $contact ?? (new \App\Services\ContactSettingsService())->read(); ?>
<address class="contact-details text-sm" style="font-style:normal;line-height:2.2;overflow-wrap:anywhere">
<p>تلفن: <a dir="ltr" href="tel:<?= esc($contact['phone'], 'attr') ?>"><?= esc($contact['phone']) ?></a></p>
<p>ایمیل: <a dir="ltr" href="mailto:<?= esc($contact['email'], 'attr') ?>"><?= esc($contact['email']) ?></a></p>
<p>آدرس: <?= esc($contact['address']) ?></p>
<p>کدپستی: <span dir="ltr"><?= esc($contact['postal_code']) ?></span></p>
</address>
