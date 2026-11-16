# Homepage design

Reference: Figma file `ESo39DZfDlnXxD742Tciza`, page `89:82`, home frame `110:111`.

New home styling uses Tailwind utilities in `app/Views/store/home.php` and the `home_*` partials. There is no added custom stylesheet. Legacy inline styles remain restricted to the other storefront screens. Build the deployable bundle with `npm run build:css`.

## Local artwork

Files in `public/assets/figma/` are the original assets returned by Figma design context. Temporary Figma URLs are never used at runtime.

| Slot | Asset |
| --- | --- |
| Hero | `78d8d.png` |
| Men / women | `8186a.png` / `6dacd.png` |
| Promotion | `8f210.png` |
| Header / footer logo | `b7853.png` / `4b920.png` |
| Menu / header cart | `0ee4b.svg` / `3067c.svg` |
| Category / section arrows | `f23e1.svg` / `530b8.svg` |
| Product favorite / bag | `c37a2.svg` / `e7fef.svg` |
| Bottom navigation | `1b037.svg`, `3828f.svg`, `c8836.svg`, `ee3d5.svg`, `6e816.svg` |
| Brand logos | `59dd8.svg`, `dc431.svg`, `83fb6.svg` |
| Brand navigation | `5d4d8.svg`, `bc13b.svg` |

Admin-uploaded banner images, links, text and text visibility continue to override the reference defaults. Product photos, prices, sale prices and featured selection use catalog data. Ratings render only when supplied with a nonzero review count. Favorites are stored in this browser; the product bag link opens product size selection before purchase. The operating system's clock/network indicators are not recreated in the website.

## Verification

- PHP 8.3 syntax checks and Tailwind build passed.
- Browser checks passed at widths 320, 390, 700, 701 and 1440, with no horizontal page overflow and all visible local images loaded.
- At width 390, hero: `364×245` at `(13,93)`; categories: `364×112` at `(13,352)`; promotion at `y=841`; brands at `y=983`; featured at `y=1159`; footer: `390×442` at `y=1450`.
- Isolated fixtures verified `178×292` bestseller cards, `134×219` featured cards, sale display, favorite persistence and the favorites dialog. They do not seed or change the database.
- Menu open/close, Escape dismissal, and cart drawer open/close passed. Cart retrieval and real catalog content remain unverified because the configured local database refuses connections. Existing development debug-toolbar script errors are absent in the production-mode preview.
- Only the homepage was redesigned; other storefront screens retain their existing presentation.
