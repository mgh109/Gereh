# GEREH Store

Custom shoe-store e-commerce platform built with CodeIgniter 4 and MySQL.

## Confirmed stack
- PHP: 8.3
- Backend: CodeIgniter 4
- Database: MySQL / InnoDB / utf8mb4
- Customer authentication: mobile OTP
- Payment gateway: ZarinPal
- Shipping: manually configured methods and fees in the admin panel
- Admin UI source of truth: GEREH Figma page `187:42` (not Dokohe or an independently designed template)
- Storefront UI source of truth: GEREH Figma page `89:82`; mobile-first UX

## Current verified status
- PHP syntax, Composer install and CI4 CLI route registration have passed GitHub Actions.
- Catalog, customer, OTP, order and payment-ledger **migrations** have passed on a temporary MySQL 8 service in CI.
- The public catalog and guest-auth boundary are exercised by real HTTP smoke tests.
- Public catalog, shared session cart, OTP/customer API and owner-scoped order reads are implemented in code.
- **Checkout is disabled by default (HTTP 503)** via `GEREH_CHECKOUT_ENABLED=false`. A ZarinPal v4 initiation/callback/settlement implementation exists, but live provider confirmation, reconciliation/refund handling and full concurrency tests remain unverified.
- **National SMS (Melipayamak) pattern adapter is implemented**, with admin controls at `/admin/settings/sms`. Credentials live only in `.env`; a provider-approved OTP Body ID must be configured in admin. Real SMS delivery still needs verification. See `docs/melipayamak-setup.md`.
- SSR login/profile, gated checkout, AJAX cart drawer, orders/customer/article admin pages, editable homepage banner slots and public blog have been added. Real image assets are uploaded through admin; approved Figma assets are not bundled.
- MySQL order integration smoke now checks actual price snapshots, idempotency, stock validation, and authenticated API cart addition (CSRF + cookies + live DB price).
- Figma pixel-accurate comparison, Lighthouse, live payment/SMS, comprehensive admin commerce and cPanel production smoke tests remain incomplete.

Refer to `docs/customer-auth.md`, `docs/checkout-foundation.md`, `docs/openapi-catalog.yaml`, `docs/openapi-customer.yaml` and `docs/cpanel-deployment.md`.

Never commit production credentials. Use environment configuration for database, SMS, payment and encryption secrets.

## Delivery phases and main-branch policy

All implementation commits go directly to `main`. Every phase is considered complete only after implementation, visual comparison with the corresponding Figma frames, route/access checks, and relevant CI/runtime tests. Never report a phase as finished based solely on a commit.

1. **Foundation** — CI4 bootstrap, schema, migrations, authentication/security foundation. Database migrations and HTTP smoke checks have passed in GitHub Actions.
2. **Storefront UI** — Figma-faithful home, catalog, product detail, navigation, search and filters.
3. **Customer journey** — cart, OTP login, checkout, order tracking and customer profile.
4. **Admin catalog** — dashboard, product list/editor, categories, brands, variants and inventory.
5. **Admin commerce** — orders, customers, promotions, shipping methods and fees.
6. **Content management** — home banners, editorial content, buying guides and settings.
7. **Integration and release** — connect APIs, test security and responsiveness, production readiness.

### Implementation rules
- Do not invent visual patterns where Figma provides an approved screen. Inspect actual frames, components, measurements and assets before implementation.
- Reuse a shared admin layout and reusable storefront components.
- Keep admin routes behind the `adminauth` filter; do not create default admin passwords.
- Store all `*_toman` values as integer tomans; convert only at the payment adapter boundary.
- Do not run database migrations before hosting and database access are ready.
- Clearly label demo data and never present mock dashboard metrics as real figures.
- Commit each cohesive change to `main`, and report the exact commit link and any unverified tests.
