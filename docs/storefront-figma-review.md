# Storefront Figma review — 2026-11-10

Reference: GEREH file `ESo39DZfDlnXxD742Tciza`, page `89:82`.

The existing customer-facing views now share the approved header, footer and five-item bottom navigation. Navigation uses local Figma SVGs, route-specific active states, the 380 × 60 mobile geometry and safe-area spacing. Product detail uses its own 104px purchase bar; login, checkout and editorial screens omit the bottom navigation as shown in Figma.

Updated screens: home, catalog, product, cart (populated and empty), account (guest and signed in), mobile login/verification, checkout, magazine, article and size guide. Added `/favorites`, using browser-local saved products with removal and links to their current product pages. The guest account landing is public; profile and order APIs retain authentication requirements. Signed-in profile and orders are revealed by their dashboard links.

Catalog cards reuse the homepage product component. The filter opens as a bottom sheet on mobile. Cart and its quick panel use real catalog imagery where available; cart images are fetched in one batch. Magazine search and topic links filter published article titles/excerpts and preserve the search across pagination.

## Verification

- `npm.cmd run build:css` passed.
- PHP syntax checks for all storefront views and modified controllers/service/routes passed; JavaScript syntax passed.
- `node writable/ui-check/storefront-check.cjs`: 13 screen states at 320, 390, 700, 701 and 1440px; no horizontal overflow, broken images or JavaScript errors. Checks shared chrome, menu/cart dismissal, persisted favorites/removal, filters, and available/unavailable variants.
- `node writable/ui-check/products.cjs`: homepage cards remain 178 × 292 and 134 × 219; promotion starts at y=841 and footer at y=1450, matching the original home frame. Favorite persistence and navigation to the new page passed.
- Real application HTTP checks passed: home, shop, login, guest account, favorites and size guide return 200; guest checkout redirects (302); unauthenticated `/api/v1/me` returns 401.
- Browser screenshots are in `writable/ui-check/storefront-*-390.png` and `storefront-*-1440.png`. Fixtures are isolated from the database and served only by the local preview router.

## Limits

The browser checks use explicitly isolated fixture data. Live catalog, signed-in customer data, the cart media query, article search, SMS and payment integration still require an available application database/provider configuration. PHPUnit could not run because this checkout has no installed PHPUnit executable. No migrations or production provider calls were performed.

The Figma file also contains dedicated address-management, order-detail/tracking and payment-result screens that do not have corresponding storefront implementations in this checkout. This change aligns the existing pages and adds the missing favorites destination; it does not implement those additional commerce workflows or claim a pixel-exact match for their states. Product and article content/images remain data-driven.

This workspace has no `.git` directory, so no commit was created.
