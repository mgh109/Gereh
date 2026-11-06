# Customer OTP API and SMS provider contract

The following customer endpoints are implemented in the CI4 app:

| Method | Path | Session login | CSRF |
|---|---|---|---|
| GET | `/api/v1/auth/csrf` | No | No (returns token) |
| POST | `/api/v1/auth/otp/request` | No | Yes |
| POST | `/api/v1/auth/otp/verify` | No | Yes |
| POST | `/api/v1/auth/logout` | Yes/No | Yes |
| GET | `/api/v1/me` | Yes | No |
| PATCH | `/api/v1/me` | Yes | Yes |

**Same-origin web flow:** GET CSRF token and preserve the session cookie; send `X-CSRF-TOKEN` on the JSON POST/PATCH requests. Fetch an updated token as needed, because token regeneration is enabled. Do not put the OTP or token in URLs or logs.

Request JSON:
- Request OTP: `{"mobile":"09xxxxxxxxx"}`
- Verify OTP: `{"mobile":"09xxxxxxxxx","code":"123456"}`
- Update profile: `{"first_name":"...", "last_name":"..."}`

OTP security:
- Six numeric digits from a cryptographic RNG.
- HMAC-SHA256 stored in `otp_challenges`; requires private `GEREH_OTP_HASH_SECRET` (at least 32 bytes).
- Expiry 120 seconds by default; five per-challenge verification attempts.
- Resend cooldown 60 seconds by default and throttling per mobile/IP.
- Earlier challenges are superseded by a new request.
- Verified codes are consumed inside a transaction, then session ID is regenerated.
- Customer ID is stored only server-side in the authenticated session.

## SMS adapter

The current adapter expects a *trusted HTTPS* JSON webhook at `GEREH_SMS_WEBHOOK_URL`, with an `Authorization: Bearer <GEREH_SMS_WEBHOOK_TOKEN>` header. It POSTs a JSON object with `to` and `message`, and accepts only an HTTP 2xx response with JSON `{"success": true}`.

This is an **adapter contract**, not a claim that any particular SMS vendor supports this payload. Integrate the selected vendor through an adapter with its documented request and delivery semantics; do not guess provider credentials or invent test SMS in production.

Without URL, token and HMAC secret, OTP requests fail closed with 503. The login form UI and end-to-end SMS delivery are not validated yet. API-only access from mobile apps is also not complete: current auth uses secure same-origin sessions and CSRF, not bearer tokens.

## Deployment safety

- Configure production `app.baseURL` as HTTPS, secure cookies in infrastructure/framework, and secret keys only in server-side `.env`.
- Keep `.env` and `writable/` outside the public document root.
- Remove expired OTP challenges periodically using a scheduled cleanup job (not implemented).
- Use a shared Redis/Memcached rate-limit store when deploying multiple application instances. File cache is suitable only for one node.
- Do not claim OTP ready until real gateway response, phone delivery, replay tests, security headers and operational alerting pass.


## ملی پیامک (Melipayamak)

گزینه `melipayamak` اکنون مستقیماً از REST رسمی ملی پیامک با `BaseServiceNumber` و پترن خدماتی OTP استفاده می‌کند. تنظیمات عملیاتی (فعال‌سازی و Body ID) در `/admin/settings/sms` ذخیره می‌شوند؛ نام کاربری و رمز فقط از `GEREH_MELIPAYAMAK_USERNAME` و `GEREH_MELIPAYAMAK_PASSWORD` سرور خوانده می‌شود. ارسال پیش‌فرض غیرفعال است. `webhook` همچنان به‌عنوان گزینه دوم موجود است.

آموزش کامل: `docs/melipayamak-setup.md`. تأیید واقعی تحویل پیامک به شماره تستی هنوز باید با حساب ملی پیامک شما انجام شود.
