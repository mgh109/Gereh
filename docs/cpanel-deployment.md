# GEREH: PHP 8.3 / cPanel deployment checklist

## Prerequisites

- PHP 8.3 with `intl`, `mbstring`, `mysqli`, `fileinfo` and `curl`.
- MySQL 8 or compatible MariaDB with InnoDB and utf8mb4.
- HTTPS domain, rewrite support, writable directories.
- Composer dependencies installed during deployment; a fully populated `vendor/` must be present.
- Build Tailwind CSS 4 assets **before upload**: `npm install` and `npm run build:css`; confirm `public/assets/css/app.css` exists.

## Recommended directory layout

Keep the full project under a **private directory outside public_html**; point the domain's document root to `<project>/public`. The `.env`, `app/`, `vendor/`, and `writable/` folders must not be served directly by Apache/Nginx. Do not move `index.php` without carefully updating its root paths.

## Environment

Copy `.env.example` to a server-local `.env` (never commit). Set:
- `CI_ENVIRONMENT = production`
- `app.baseURL = 'https://your-store.example/'`
- `app.forceGlobalSecureRequests = true`
- Database hostname, port, database, username, password; never use the root DB user.
- `GEREH_OTP_HASH_SECRET` private random 32+ character value.
- SMS adapter URL/token once the actual provider's request contract is implemented and tested.
- ZarinPal settings only when a verified payment adapter and callback flow are implemented.
- `GEREH_STANDARD_SHIPPING_TOMAN` is required by the internal OrderService; production checkout remains disabled until payment is integrated.

Restrict .env permissions, enable HTTPS and verify session cookies have Secure, HttpOnly and SameSite flags.

## Database migrations

After a complete DB backup and staging verification, run `php spark migrate --all` with the project root as working directory. Run `php spark migrate:status` to verify. If cPanel does not offer Terminal/SSH, deploy via CI/CD with an authorized migration step or request the host to run the commands; do not paste CI4 migration PHP files into phpMyAdmin as SQL.

## Smoke tests

- `/robots.txt` responds successfully and contains sitemap URL.
- `/sitemap.xml` is valid XML.
- `/api/v1/products`, `/api/v1/categories`, `/api/v1/brands` return JSON.
- Anonymous `/api/v1/me` and `/api/v1/orders` return HTTP 401.
- CSRF-protected routes cannot be mutated without an appropriate CSRF token.
- Admin login, product and inventory operations require staging tests with real data.
- Validate 360/390/430/768/1024/1440 widths against approved Figma screenshots.

## Must remain disabled until verified

Production checkout/payment, ZarinPal callbacks, order fulfillment, OTP delivery (unless real provider configured), SMS notification jobs, and any UI features not backed by real services. Passing automated migrations and public HTTP smoke tests is **not** proof of production readiness.
