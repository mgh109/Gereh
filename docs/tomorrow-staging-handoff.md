# GEREH — tomorrow staging handoff (2026-11-06)

## Implemented and verified in GitHub Actions
- PHP 8.3 syntax, Composer, CI4 route bootstrap, PHPUnit phone and gateway-unit checks.
- Tailwind build with pinned CSS 4.3.4 dependencies.
- MySQL 8 migrations for catalog, admins, customers, OTP, orders/payments, articles, home banners.
- HTTP smoke: read-only catalog/home, blog, CSRF endpoint, protected order/admin/account routes, disallowed payment callback, disabled checkout with CSRF.
- Real MySQL smoke: an active test variant with inventory, one order snapshot, identical idempotency-key replay, oversell rejection, and unchanged inventory before payment.
- Real HTTP test: add that same variant to session cart with cookie/CSRF and verify DB price and count.

## Implemented but not externally verified
- Customer OTP login and SMS through a configurable HTTPS webhook.
- Payment initiation, ZarinPal v4 verification and atomic stock deduction after provider approval.
- Customer account, checkout SSR form, AJAX cart drawer.
- Admin products, taxonomy, orders, customers, articles and home banner upload/editor pages.
- Public SEO articles and sitemap.

## Production blockers (must not be marked complete)
1. Real SMS credentials/provider mapping, live delivery, rate-limit/replay testing.
2. ZarinPal merchant ID, sandbox/full callback tests, review/refund and interrupted-payment recovery.
3. Payment inventory concurrency tests and refund/manual-review operator workflow.
4. Hosting deployment and database backups, migration execution, secure HTTPS and environment.
5. Approved Figma image exports uploaded as real media and actual browser visual comparison; reference screens alone do not establish pixel-perfect parity.
6. Additional admin commerce: shipping-method/fee management, promotions/discounts, returns, payments reconciliation, settings.
7. E2E browser tests, mobile widths, accessibility, performance and SEO audits.

## Safe staging steps tomorrow
1. Set domain document root to `public/` and PHP 8.3.
2. Install Composer dependencies and prebuilt Tailwind; verify `public/assets/css/app.css`.
3. Make database backup; configure credentials privately, then run `php spark migrate --all` and `php spark migrate:status`.
4. Create a verified admin through a controlled credential provisioning process. Never commit credentials or invent a default password.
5. Test homepage, shop, product, cart, login, account, orders, blog, admin CRUD and media uploads on staging.
6. Keep `GEREH_CHECKOUT_ENABLED=false` until live payment and refund verification is signed off.
7. Upload the approved Figma banner/artwork via the admin. Avoid temporary Figma asset URLs in published HTML.

## References
- Storefront frame: https://www.figma.com/design/ESo39DZfDlnXxD742Tciza/GEREH?node-id=89-82
- Admin frame: https://www.figma.com/design/ESo39DZfDlnXxD742Tciza/GEREH?node-id=187-42
- `docs/cpanel-deployment.md`, `docs/customer-auth.md`, `docs/checkout-foundation.md`
