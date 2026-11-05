# GEREH Store

Custom shoe-store e-commerce platform built with CodeIgniter 4 and MySQL.

## Confirmed stack
- PHP: 8.3
- Backend: CodeIgniter 4
- Database: MySQL / InnoDB / utf8mb4
- Customer authentication: mobile OTP
- Payment gateway: ZarinPal
- Shipping: manually configured methods and fees in the admin panel
- Admin UI direction: Dokohe visual/layout approach, mapped to the GEREH admin Figma
- Storefront: approved GEREH / StepHub Figma; mobile-first UX

## Status
Phase 0 architecture and security requirements are documented. A minimal CodeIgniter 4 application scaffold is now committed, including Composer dependencies, PHP 8.3 runtime checks, web/CLI entry points, initial routing, a landing page, and environment-driven MySQL configuration. Dependency installation and runtime verification are still pending.

See docs/phase-0-foundation.md, docs/database-schema.md and docs/security-baseline.md.

Never commit production credentials. Use environment configuration for database, SMS, payment and encryption secrets.
