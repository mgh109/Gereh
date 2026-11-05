# GEREH — Initial Database Model

This is the logical model. The first executable catalog migration is in `app/Database/Migrations/2026-11-05-090000_CreateCatalogTables.php`. Monetary columns in the application database use integer Toman values; gateway-specific conversion belongs in the payment adapter.

## Identity and access
- customers: unique mobile, verification state, profile and status.
- customer_addresses: customer-owned addresses, recipient, phone, postal code and default flag.
- admins: independent admin accounts.
- roles, permissions, admin_role, role_permission: admin RBAC.
- otp_challenges: hashed OTP, expiry, attempt count, resend state, purpose and delivery metadata. Never store plaintext OTP.

## Catalog
- categories: hierarchy, slug, status and SEO fields.
- brands: name, slug, logo and status.
- products: title, slug, description, brand, category, publication state and SEO.
- product_media: path, alt text, sort order and media role.
- product_variants: SKU, barcode, size, color, optional price override and active state. A null variant price inherits the product base price.
- inventory_items: one current stock row per variant and low-stock threshold.
- inventory_movements: auditable stock changes and source references.

## Cart and orders
- carts, cart_items: customer or guest cart and selected variant/quantity.
- wishlists, wishlist_items: customer favorites.
- orders: customer, immutable totals, address snapshot, shipping snapshot and order state.
- order_items: immutable product/variant/price/title snapshot.
- order_status_history: auditable status transitions.
- shipments: shipping method snapshot, tracking code and dispatch/delivery timestamps.

## Payment and promotion
- payments: gateway, merchant reference, authority, amount, status, verification code and timestamps.
- coupons: code, validity, limits and discount configuration.
- coupon_redemptions: usage records linked to customers/orders.

## Shipping configuration
- shipping_methods: title, enabled state, fee mode, base fee, delivery estimate and sort order.
- shipping_rules: optional region/threshold conditions and fee overrides.

Shipping configuration is maintained through the admin panel. Orders must store a snapshot of the selected method and fee so future configuration changes do not rewrite historical orders.

## Editorial content
- articles, article_categories, article_media: blog and buying guides.
- SEO metadata and canonical slugs for indexable content.

## Conventions
- MySQL InnoDB and utf8mb4.
- Integer Toman monetary values; no floating point prices. Convert to the gateway-required unit only at the payment boundary.
- Unique indexes for slugs, SKUs, mobile numbers, coupon codes and gateway references where appropriate.
- Index product filters, order lookup, customer history and payment callback lookup.
- Foreign keys with explicit deletion policies.
- All schema changes through migrations; seed only non-sensitive development data.
