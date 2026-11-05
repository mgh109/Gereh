# GEREH — Security Baseline

## Customer OTP
- Normalize and validate mobile numbers server-side.
- Generate cryptographically secure OTPs.
- Store OTP hashes where practical.
- Enforce expiry, resend cooldown and maximum verification attempts.
- Rate-limit by mobile number and source IP.
- Invalidate a challenge after successful verification.
- Avoid account enumeration in user-facing responses.

## Admin access
- Separate admin authentication guard and routes.
- Enforce role/permission checks server-side on every protected action.
- Protect state-changing requests with CSRF validation.
- Audit sensitive admin actions such as price, stock, order and permission changes.
- UI visibility is never an authorization mechanism.

## ZarinPal
- Create payment requests server-side.
- Persist pending payment before redirecting to the gateway.
- Verify authority and amount with ZarinPal server-to-server.
- Match verified amount against the persisted payment/order.
- Make callback processing idempotent; repeated callbacks must not duplicate stock changes or order transitions.
- Never mark an order paid based only on query parameters or a successful redirect page.

## Uploads
- Allow only expected image formats for product/editorial media.
- Verify MIME and file content; do not trust original filename or browser content type.
- Generate server-side filenames and enforce size/dimension limits.
- Store uploads outside executable/public paths where hosting permits.
- Prevent path traversal and executable file handling.

## General
- Keep secrets in environment configuration.
- Require HTTPS in production.
- Escape output and validate input server-side.
- Use parameterized database access.
- Keep framework/dependencies patched.
- Never log OTP values, passwords, payment secrets or sensitive payment payloads.
