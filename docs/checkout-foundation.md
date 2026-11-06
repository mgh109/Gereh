# GEREH checkout and ZarinPal integration

## Implemented code

- `OrderService`: creates an unpaid customer-owned order with immutable line price snapshots, current DB stock checks and customer-scoped idempotency keys.
- `PaymentInitiationService`: claims a payment attempt under an order-row lock, calls the provider outside the transaction and persists the returned authority. Multiple simultaneous requests cannot freely create duplicate attempts.
- `ZarinpalGateway`: HTTPS v4 request/verify calls to fixed provider hosts; internal **toman** values are converted to **rial** at the adapter boundary. Do not assume callback parameters indicate success.
- `ZarinpalCallbackController`: accepts GET `/payment/zarinpal/callback`. Unknown authorities never initiate payments. A callback with `Status=OK` triggers server-side verification.
- `PaymentSettlementService`: verifies against ZarinPal and commits order payment status, inventory decrements, movement audit rows and payment reference **atomically**. Duplicate locally settled payment attempts return a read-only result.
- `POST /api/v1/checkout`: feature-gated. **Disabled by default** via `GEREH_CHECKOUT_ENABLED=false`. When explicitly enabled, expects an authenticated customer session, CSRF, `Idempotency-Key` header and JSON body containing `recipient`, optionally `shipping_method=standard`. It creates an order and returns the provider redirect URL.
- No fake orders or successful payments are generated. Missing merchant ID, provider failures and insecure callback settings fail closed.

## Money and provider

ZarinPal v4 paths:
- `https://api.zarinpal.com/pg/v4/payment/request.json`
- `https://api.zarinpal.com/pg/v4/payment/verify.json`
- `https://www.zarinpal.com/pg/StartPay/{authority}`

Sandbox uses the sandbox.zarinpal.com host. Confirm the live merchant API's **currency contract** with the provider before enabling production; GEREH stores TOMAN and currently sends ten times that amount as RIAL.

## Known missing work: DO NOT ENABLE PRODUCTION PAYMENTS

1. Prove real ZarinPal sandbox request + redirect + callback + verification + duplicate callback, followed by real-provider verification.
2. Build a persistent **manual-review and refund workflow** for successful provider payments with insufficient inventory or local DB failures. The existing code fails closed and logs critical errors, but automatic refunds and customer support tooling are not implemented.
3. Implement recovery for interrupted `requesting` payment attempts and unknown/ambiguous provider results; never blindly retry requests that might already have succeeded.
4. Add real MySQL concurrency integration tests, including two simultaneous checkouts, expired payments, one-last-item race, duplicate authority and forced failures.
5. Verify SMS OTP login with a live provider, protected customer sessions, shipping configuration, access controls, status transitions and browser UX.
6. Build a Figma-matching checkout UI and payment result page; the callback currently responds with JSON.
7. Review production rate limiting, audit alerts, secrets, secure cookies, HTTPS and provider contract.

**Checkout must remain disabled until all these requirements are satisfied.** The currently green CI checks PHP syntax, selected unit tests, migration execution and HTTP smoke tests. It does NOT certify full money movement.

## Required server env

- `GEREH_CHECKOUT_ENABLED=false` (until release approval)
- `ZARINPAL_MERCHANT_ID`: real merchant credential, never commit
- `ZARINPAL_SANDBOX=true` for staging
- `ZARINPAL_CALLBACK_URL=https://your-domain.example/payment/zarinpal/callback`
- `GEREH_STANDARD_SHIPPING_TOMAN`: non-negative integer tariff
- HTTPS base URL, valid sessions and configured OTP provider.
