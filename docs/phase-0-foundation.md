# GEREH — Phase 0: Foundation

## Confirmed decisions

| Area | Decision |
|---|---|
| Runtime | PHP 8.3 |
| Backend | CodeIgniter 4 |
| Database | MySQL, InnoDB, utf8mb4 |
| Authentication | Customer mobile OTP; separate admin authentication |
| Payment | ZarinPal |
| Shipping | Manual methods and fees managed in admin |
| Admin UI | Dokohe visual/layout direction mapped to GEREH admin Figma |
| Storefront | GEREH / StepHub Figma; mobile-first; preserve approved UX |

## Objectives
1. Establish a clean CodeIgniter 4 application baseline on PHP 8.3.
2. Separate storefront, admin, identity, catalog, checkout, payment, shipping and content responsibilities.
3. Use environment-based configuration; keep secrets out of Git.
4. Establish database and migration conventions.
5. Define security requirements for OTP, admin access, uploads and payment callbacks.
6. Keep changes incremental and reviewable.

## Application boundaries
- Storefront: public catalog, product detail, search, filtering, cart, checkout and customer account.
- Admin: dashboard, catalog, variants/inventory, orders, customers, coupons, content, shipping and store settings.
- Identity: customer OTP lifecycle and independent admin authentication/authorization.
- Catalog: products, categories, brands, media, variants and stock.
- Checkout: cart validation, address, shipping quote, discount and order creation.
- Payments: payment request, persisted transaction, callback verification and idempotent finalization.
- Shipping: admin-managed methods, coverage, fees and shipment status.
- Content: articles, buying guides and SEO metadata.

Controllers must remain thin. Business rules belong in services; persistence belongs in models; external providers must be wrapped behind service interfaces.

## Proposed CodeIgniter organization
app/
  Config/
  Controllers/Admin/
  Controllers/Auth/
  Controllers/Store/
  Controllers/Checkout/
  Controllers/Payment/
  Database/Migrations/
  Database/Seeds/
  Filters/
  Models/
  Services/Auth/
  Services/Payment/
  Services/Shipping/
  Services/Storage/
  Validation/
  Views/admin/
  Views/auth/
  Views/store/
  Views/checkout/
public/assets/
writable/cache/
writable/logs/
writable/session/
writable/uploads/
docs/

Directories should be created alongside their implementation, not as empty placeholders.

## Configuration policy
- Local settings belong in .env; only .env.example is committed.
- Production merchant IDs, SMS credentials, database passwords and encryption keys must never be committed.
- Base URLs and payment callback URLs must be environment-specific.
- Use a dedicated database user with minimum required privileges.

## Implementation rules
- Use database transactions for order creation, stock updates and payment finalization.
- Verify ZarinPal callbacks server-to-server; a browser redirect is not proof of payment.
- Make callback processing idempotent.
- OTPs must be short-lived, rate-limited, attempt-limited and stored as hashes where practical.
- Admin authentication and permissions must be separate from customer authentication.
- Validate uploads by actual MIME/content, size and allowed extension; never execute uploaded files.
- Store money as integers, never floating point. Choose one canonical Rial/Toman unit before checkout implementation.
- Persist timestamps consistently and localize dates for Persian presentation.

## Phase 0 acceptance checklist
- [ ] PHP 8.3 runtime and required extensions verified on the target host.
- [ ] CodeIgniter 4 app starter installed and booting.
- [ ] MySQL connection verified.
- [ ] Environment configuration verified.
- [ ] Migration and seeder commands verified.
- [ ] Route groups and authentication filters established.
- [ ] Upload storage strategy verified.
- [ ] Initial migrations reviewed.
- [ ] Local setup documented and reproducible.

## Dependencies to confirm
PHP 8.3 is the confirmed target runtime. Required PHP extensions and hosting configuration still need to be verified before deployment. OTP provider and credentials must be selected before enabling live OTP delivery.
