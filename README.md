# GEREH Store

Custom shoe-store e-commerce platform built with CodeIgniter 4 and MySQL.

## Confirmed stack
- Backend: CodeIgniter 4
- Database: MySQL / InnoDB / utf8mb4
- Customer authentication: mobile OTP
- Payment gateway: ZarinPal
- Shipping: manually configured methods and fees in the admin panel
- Admin UI direction: Dokohe visual/layout approach, mapped to the GEREH admin Figma
- Storefront: approved GEREH / StepHub Figma; mobile-first UX

## Status
Phase 0 establishes architecture, configuration and security requirements. The executable CodeIgniter app starter and features will be added in implementation steps after confirming the target hosting PHP version and extensions.

See docs/phase-0-foundation.md, docs/database-schema.md and docs/security-baseline.md.

Never commit production credentials. Use environment configuration for database, SMS, payment and encryption secrets.
