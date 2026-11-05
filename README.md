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

## Status
Phase 0 architecture and security requirements are documented. A minimal CodeIgniter 4 application scaffold is now committed, including Composer dependencies, PHP 8.3 runtime checks, web/CLI entry points, initial routing, a landing page, and environment-driven MySQL configuration. Dependency installation and runtime verification are still pending.

See docs/phase-0-foundation.md, docs/database-schema.md and docs/security-baseline.md.

Never commit production credentials. Use environment configuration for database, SMS, payment and encryption secrets.

## Delivery phases and main-branch policy

All implementation commits go directly to `main`. Every phase is considered complete only after implementation, visual comparison with the corresponding Figma frames, route/access checks, and relevant CI/runtime tests. Never report a phase as finished based solely on a commit.

1. **Foundation** — CI4 bootstrap, schema, migrations, authentication/security foundation. Code exists; database migrations and runtime verification are pending.
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
