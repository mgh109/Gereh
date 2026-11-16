# Product search and size filters

- Storefront `/shop`: product search by name, slug or active in-stock variant SKU; exact size filter in the filter panel, with searchable suggestions drawn from actual available variants. An active-size indicator allows clearing size alone.
- Admin `/admin/products`: product name/slug/SKU search and exact size filter, including drafts, inactive variants and unavailable sizes for management.
- Admin `/admin/inventory`: size filter combines with product/name/SKU/color search, selected product and stock status.
- Public `/api/v1/products` and `/api/v1/search`: `size=40` combines with existing `q`, category, brand, price, sorting and pagination. Invalid array or overlong size input receives 422.

Persian and Arabic digits normalize to ASCII, including fractional sizes (`۴۲٫۵` → `42.5`). Stored size labels are normalized for matching without rewriting catalog data. Public size matches require an active variant with positive stock belonging to an active, non-deleted product. Product queries use subqueries so multiple matching colors do not duplicate products or inflate pagination totals. Admin size filters include unavailable variants but exclude deleted products. Search, sort, category and brand navigation preserve the selected size; changing filters resets the current page.

Validation: `php tests/Integration/size-filter-check.php` passes against isolated in-memory SQLite and is included in CI. It covers normalized/exact and fractional matches, stock/publication/deletion boundaries, duplicate variants, SKU search, combined filters, injection escaping, pagination totals, size suggestions and both admin controllers. Requires `intl`, `mbstring`, `sqlite3`; it never connects to the application database. Responsive browser checks also passed for storefront form submissions and both admin forms at 320, 390 and 1440px. CSS build and PHP syntax checks passed. Live MySQL production data has not been exercised.
