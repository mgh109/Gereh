# GEREH — CodeIgniter 4 Bootstrap

## Runtime
- Target PHP runtime: 8.3
- Framework dependency: CodeIgniter 4 `^4.7`
- Database driver: MySQLi
- Database charset/collation: utf8mb4 / utf8mb4_unicode_ci

## Local setup
1. Install PHP 8.3 with `intl`, `mbstring`, `mysqli` and `fileinfo` extensions.
2. Install Composer 2.
3. Copy `.env.example` to `.env` and set local database credentials.
4. Install dependencies: `composer install`.
5. Create the MySQL database and a least-privilege database user.
6. Start the development server: `php spark serve`.
7. Open `http://localhost:8080/`.

## Hosting document root
The web server document root must point to the repository's `public/` directory. Do not expose `app/`, `writable/`, `.env`, `composer.json` or `vendor/` directly to the web.

## Current scope
This bootstrap includes a minimal landing page and environment-driven MySQL connection configuration. It does not yet include migrations, authentication, product/catalog features, admin screens, payment integration or shipping workflows.

## Verification status
The repository files are committed, but Composer installation, PHP CLI boot, MySQL connectivity and hosting behavior still require execution in a PHP 8.3 environment.
