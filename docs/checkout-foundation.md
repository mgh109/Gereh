# GEREH checkout foundation — deployment notes

## Current state (not a production payment flow)

The following changes are implemented:
- `customers`, `orders`, `order_items`, `payment_transactions` migrations.
- `OrderService::create()` can atomically save a **pending, unpaid order** from a customer session cart, using live database prices and a snapshot of ordered variants.
- Stock is checked with row locks, but **not reserved or decremented** when an unpaid order is created. Never mark the order paid or fulfill it without a verified payment and a second stock check inside the settlement transaction.
- Customer order reads are owner-scoped: `GET /api/v1/orders` and `GET /api/v1/orders/{id}` require an authenticated `customer_id` session value.
- `POST /api/v1/checkout` intentionally returns HTTP 503. There is currently **no trusted OTP login mechanism and no implemented payment adapter**; order creation is not exposed through this endpoint.

## Production prerequisites

1. Implement OTP request/verify with provider-backed delivery, expiry, rate limits, attempt limits, hashed OTP storage, session regeneration and CSRF. Set `customer_id` on the server only after successful verification.
2. Configure delivery pricing and allowed shipping methods. `OrderService` currently supports only `standard` and refuses to create orders until `GEREH_STANDARD_SHIPPING_TOMAN` is a non-negative integer in the server environment.
3. Implement ZarinPal adapter with merchant credentials stored exclusively in environment. Persist payment attempts and unique provider identifiers.
4. On a **server-verified** success callback, perform a single atomic settlement transaction: lock payment + order, reject/replay previously settled states, recheck and decrement inventory, log movements, mark order paid, then commit. Failed stock verification must not silently mark an order paid; it needs a reconciliation/refund flow.
5. Add order-state auditing, checkout form/UX, shipping configuration, address management, cancellation/expiry, payment retry policy, and integration tests against actual MySQL.
6. Run database migrations in staging before enabling checkout and back up the DB. Do not run seed/dummy orders in production.

## API contracts currently available

- `GET /api/v1/orders`: 401 if no authenticated customer session.
- `GET /api/v1/orders/{id}`: 401 if unauthenticated; 404 if order is not owned by caller.
- `POST /api/v1/checkout`: 503 fail-closed regardless of payload, protected by CSRF.

The `OrderService` create method is internal-only for now. It expects `customerId`, `cart` (variant ID to quantity), validated recipient fields (`receiver_name`, `receiver_mobile`, `province`, `city`, `address`, `postal_code`), and a unique idempotency key. Customer ownership and the idempotency uniqueness constraint are enforced by the database.

## Verification

Code syntax and route registration are checked in GitHub Actions. Migration execution, concurrent transaction behavior, OTP, real SMS delivery, gateway callbacks and Figma-level UI have **not** been verified. This is a foundation, not a completed checkout.
