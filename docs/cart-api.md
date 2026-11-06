# GEREH cart API (current implementation)

The cart is session-backed for guest web clients. No checkout, payment, customer account, or mobile bearer-token support is claimed here.

## Endpoints

- `GET /api/v1/cart` — current session's cart totals and lines.
- `POST /api/v1/cart/items` — JSON body `{"variant_id": 1, "quantity": 1}`.
- `PATCH /api/v1/cart/items/{id}` — JSON body `{"quantity": 2}`. Zero removes the item.
- `DELETE /api/v1/cart/items/{id}` — remove the item.

`id` is the **variant ID** for this implementation, not a separate cart-line ID.

Response envelope: `success`, `data`, `meta`, `message`, `errors`. On success, `data` includes `lines`, `total` and `count`; money values are in **toman**.

## Security and behavior

Mutating calls **must** include the same-origin session cookie and the CSRF token expected by CodeIgniter. CSRF is checked through route filters. Do not disable this filter to make cross-origin mobile clients work; those clients will need a separately authenticated, token-based cart design.

The server validates active variant/product, quantity 1–20 and current stock. Price is loaded from the database, never from client input. Cart quantities are not reservations; stock can change between adding to cart and checkout. Checkout must revalidate stock under a DB transaction.

Errors: `422` invalid payload/quantity, `409` unavailable item or insufficient stock, `500` unexpected server error. The built-in CSRF filter's error representation may differ from the API envelope and needs separate integration verification.

## Verification still needed

- Session persistence and CSRF integration test in an installed CI4 environment
- MySQL integration tests covering price changes, removed products, overselling and concurrent requests
- Accessibility and Figma comparison for cart drawer
- Order creation and ZarinPal integration

No production checkout is available at this stage.
