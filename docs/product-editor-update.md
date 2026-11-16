# Product editor update — 2026-11-16

Deploy the changed PHP files and the new `public/assets/js/product-editor.js` and `public/assets/css/product-editor.css`. No CSS rebuild is required for this editor.

On hosting without a terminal, select the store database in phpMyAdmin and import `database/updates/2026-11-16-product-editor.sql`. It only adds nullable `product_code` and `item_condition` columns; it does not change existing records. It can be run again. The script assumes an empty table prefix. With terminal access, run `php spark migrate` instead. The editor works before the SQL update, but the code/condition fields remain hidden until the columns exist.

The product form submits information, an optional new brand, up to 100 variant rows and up to 10 new images together. Existing variants can be edited; removing a new unsaved row does not delete existing inventory. Blank SKU and URL values are generated on the server. The first new image is primary when no primary exists. Existing images are preserved.

Failed saves roll back product/brand/variant/media rows and remove any newly moved files. Text and variant rows survive validation errors; browsers require images to be reselected. Set PHP `upload_max_filesize` to at least 5M, `post_max_size` above the total selected images (e.g. 64M for ten 5MB images), and `max_file_uploads` to at least 10.

Stock edits are audited. Saving unchanged stock preserves stock consumed by checkout since the form was opened. An explicitly edited stock value based on an outdated original quantity is rejected and the product transaction rolls back.

Validation: `php tests/Integration/product-editor-check.php` uses isolated SQLite memory. The existing size-filter integration checks should also pass. MySQL-specific row locking and actual upload paths still need a hosting smoke test.
