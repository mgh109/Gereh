# SEO, product views, comments and list publication — 2026-11-16

Deploy all changed application files, then import `database/updates/2026-11-16-product-engagement.sql` into the store database using phpMyAdmin. The SQL is repeatable and only creates two tables. Existing product imports and inventory are preserved. With CLI access, use `php spark migrate` instead. Keep the earlier product-editor columns migration too.

Production `.env`:

```ini
CI_ENVIRONMENT = production
app.baseURL = 'https://gerehshoe.ir/'
app.forceGlobalSecureRequests = true
GEREH_DEBUG_ENABLED = false
```

HTTPS GET was available during inspection. Check the deployed domain's SSL and page loading before switching existing HTTP links. Canonical URLs and sitemap use the configured domain. HTTP/HTTPS and www/apex consolidation ultimately depend on host configuration. No credentials or Search Console account settings are changed by this patch.

Implemented:
- Server-rendered title, description, robots, canonical, Open Graph, Twitter and home Organization/WebSite data.
- Category/brand landing titles; paginated listing pages canonicalize to their own page. Search, size, sort/price filters, combined facets and personal pages are noindex. Tracking parameters do not change canonical URLs.
- Product Offer schema uses true variant prices, IRR (toman × 10), honest InStock/OutOfStock and known condition only. No fabricated rating, return/shipping policy or manufacturer identifier. Actual approved ratings are reflected only when present. Product image and breadcrumb data are emitted; missing pictures still need catalog images.
- BlogPosting data; public GET/HEAD routes; unavailable catalog/blog pages return 503 instead of a successful empty page. Published products/articles and populated category/brand landing pages are in sitemap; drafts are excluded.
- Public debug UI is off by default even if an old `.env` still says development. Local development may explicitly enable GEREH_DEBUG_ENABLED. CLI diagnostics remain available.
- Change product publication status in its list row using POST + CSRF. Zero-price products cannot be published.
- Daily per-product view counts displayed in product list/editor. Repeated hits in one session within 30 minutes and common crawler user agents are excluded. This is a session/time-window count, not unique people or independently verified analytics. No IP or third-party analytics identifier is stored.
- Anonymous comments and optional ratings, CSRF, a one-minute session throttle, honeypot, pending moderation. Only approved comments are public. Admin can approve, reject or return to pending at `/admin/comments`; no permanent delete action.

Validation: PHP syntax/8.3 parser, existing unit and size/editor integration suites, plus `tests/Integration/seo-engagement-check.php` on isolated SQLite. This covers canonical policy, stock/currency schema, no fake ratings, view deduplication, comment moderation, publication changes and migration. MySQL-specific concurrency, actual uploads, Search Console and rich-results eligibility need deployment verification. Search ranking/indexing is not guaranteed by code changes.

Product import package is separate: its 151 sheet records stay drafts, including sold items at quantity zero. Codes 7/70 need prices, 53/96 need model names, and all sheet records need images before publication review. The batch ledger is intentionally not cleared by code updates.
