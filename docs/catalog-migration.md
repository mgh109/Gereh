# GEREH — Catalog Schema and Migration

## First migration
`2026-11-05-090000_CreateCatalogTables.php` creates the initial catalog and inventory tables:

- `categories`: hierarchical categories, SEO fields and display ordering.
- `brands`: brand identity and logo.
- `products`: product content, publication state, category/brand and base price.
- `product_media`: ordered product gallery and primary image.
- `product_variants`: SKU, barcode, shoe size, color and optional price override.
- `inventory_items`: current stock and low-stock threshold, one row per variant.
- `inventory_movements`: append-only stock change history with reference metadata.

All tables use InnoDB and utf8mb4. Foreign keys and indexes are created by the migration. Product variants inherit the product price when their own price is null.

## Money unit
For the application database, all `*_toman` columns store integer Toman amounts. No floating-point monetary values are permitted. The ZarinPal integration layer must convert Toman to the gateway's required unit at the payment boundary and persist both the requested amount and gateway reference in the payment record.

## Apply locally
After configuring `.env` and creating the MySQL database:

```bash
composer install
php spark migrate:status
php spark migrate
```

Do not run migrations against production until a database backup has been taken and the migration has been reviewed.

## Next catalog implementation
The next implementation step adds models, validation rules and admin-facing catalog services. Public product listing and product-detail UI will be implemented only against the approved GEREH / StepHub design, preserving its mobile UX.
