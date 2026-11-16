# Admin UI

The remaining existing admin screens use shared PHP partials in `app/Views/admin/partials`, `public/assets/css/admin.css`, and `public/assets/js/admin.js`. The dashboard retains its previously matched layout. Authentication and POST permissions remain unchanged.

Design source: [GEREH admin](https://www.figma.com/design/ESo39DZfDlnXxD742Tciza/?node-id=187-42).

| Screen | Figma frame | Application route |
| --- | --- | --- |
| Products | 188:241 | `/admin/products` |
| Product editor | 188:437 | `/admin/products/create`, `/admin/products/{id}/edit` |
| Orders | 188:598 | `/admin/orders` |
| Order details | 188:798 | `/admin/orders/{id}` |
| Inventory | 188:945 | `/admin/inventory` |
| Categories and brands | 188:1152 | `/admin/taxonomy` |
| Customers | 188:1327 | `/admin/customers` |
| Homepage banners | 188:1711 | `/admin/banners` |
| Articles | 188:1835 | `/admin/articles` |
| Settings foundations | 188:2004 | `/admin/settings/sms`, `/admin/shipping` |

Customer details and article editing use the same foundations. Images of products and banners remain database supplied. All sidebar and header assets are downloaded Figma originals, including their active variants.

Only existing operations are offered. The design's discounts feature, homepage section reordering, taxonomy editing, content-to-product associations, and general store/administrator settings have no existing backend. Unsupported controls and sample design data are not presented as functioning features. Discounts is explicitly disabled in navigation. Settings exposes the implemented SMS and shipping forms.

Inventory uses the existing stock mutation route and inventory service, with CSRF protection. Product filters and order search are server-side; customer statistics exclude unpaid spending. Article filtering operates on the loaded list. No records or settings were changed during verification.

## Verification

Local-only render fixtures and browser checks live in `writable/ui-check/admin-pages-*`. These are outside the public document root; production routes expose none of the fixtures. Start the fixture server from the project root:

```powershell
.\writable\ui-check\php\php.exe -S 127.0.0.1:8092 -t public writable/ui-check/admin-router.php
node writable/ui-check/admin-pages-check.cjs
```

The browser check covers 15 screens at widths 1440, 1024, 760, 390 and 320, plus empty states, image loading and geometry, overflow, active navigation, CSRF fields, article filters and fulfillment branches. Desktop and mobile screenshots are saved beside the fixtures. The Playwright runtime and Chrome paths in the script are specific to the development machine.

An isolated SQLite integration check exercises the real services/controllers without connecting to the application database:

```powershell
.\writable\ui-check\php\php.exe -d extension_dir=writable/ui-check/php/ext -d extension=mbstring -d extension=intl -d extension=sqlite3 writable/ui-check/admin-data-check.php
```

PHPUnit is not installed in this checkout. PHP syntax checks and the browser/SQLite checks were used for this change. Production MySQL and deployment have not been exercised.
