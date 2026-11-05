# GEREH — Initial Database Model

This is the logical model. Concrete columns, indexes, foreign keys and migrations will follow after currency and variant rules are finalized.

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
- product_variants: SKU, size, color, price, compare-at price and active state.
- inventory_items: variant stock and low-stock threshold.
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
- Integer monetary values; no floating point prices.
- Unique indexes for slugs, SKUs, mobile numbers, coupon codes and gateway references where appropriate.
- Index product filters, order lookup, customer history and payment callback lookup.
- Foreign keys with explicit deletion policies.
- All schema changes through migrations; seed only non-sensitive development data.
