<?php if (empty($hideFooter)): ?><?= view('store/partials/home_footer') ?><?php endif; ?>
<?php if (empty($hideBottomNav)): ?><?= view('store/partials/bottom_nav') ?><?php endif; ?>
<?= view('store/partials/favorites') ?>
<?= view('store/partials/cart_drawer') ?><script>
(()=>{const load=image=>{image.src=image.dataset.lazySrc;image.removeAttribute('data-lazy-src')};const images=document.querySelectorAll('img[data-lazy-src]');if(!('IntersectionObserver' in window)){images.forEach(load);return}const observer=new IntersectionObserver(entries=>{entries.forEach(entry=>{if(entry.isIntersecting){load(entry.target);observer.unobserve(entry.target)}})},{rootMargin:'150px',threshold:0.01});images.forEach(image=>observer.observe(image));})();
</script>
</body></html>
