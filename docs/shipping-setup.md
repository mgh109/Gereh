# GEREH — shipping setup for staging

## Available delivery methods

- `standard`: standard shipping, active by default; initial fee is **25,000 toman**. Change it before accepting real orders.
- `express`: expedited delivery, disabled until an actual service is configured.
- `pickup`: free local pickup, disabled until a pickup address and times have been communicated to customers.
- `post`: postal express service, disabled pending tariff verification.
- `tipax`: Tipax carrier, disabled pending tariff verification.
- `courier`: city courier, disabled until a coverage region and actual tariff are configured.
- Additional methods can be added using a unique English code, title, tariff, eligibility and delivery estimates.

These methods are **manually configured shipping products**, not live rate integrations with Iranian Post, Tipax or courier companies. The current application does not request rate quotes or create shipping labels with their external APIs.

## Admin setup

Go to `/admin/shipping`.

For each method configure:
1. Name, description and type (`flat` or `pickup`).
2. Base fee in toman.
3. Optional minimum cart subtotal for free shipping (only flat methods).
4. Optional comma-separated province eligibility: e.g. `تهران,البرز`. Empty means nationwide.
5. Minimum and maximum estimated business-day delivery duration.
6. Sort priority and active toggle.

New methods may be added through the same page. Changes are immediately reflected in new quotes; existing orders retain their own shipping fee and method/title snapshot.

## Customer checkout

`GET /api/v1/shipping/quotes?province=...` reads the current session cart total and returns available delivery methods plus their individual fee and resulting grand total.

The checkout page dynamically refreshes quotes when province changes. The customer selects one method. Checkout sends **only the method code**, not a shipping price. `OrderService` then:
- Reloads live product prices and stock from MySQL inside the order transaction.
- Validates that the method is active and available in the recipient province.
- Recalculates free shipping and the final delivery fee using `ShippingService`.
- Stores the method code, display title and actual charged fee in the order.
- Ensures the final amount sent to payment initiation matches the stored order total.

Customers cannot reduce fees by modifying the browser-side request.

## Shipment tracking and SMS

After a verified payment, admin can change `processing → shipped` with a tracking code and later `shipped → delivered`. If the corresponding SMS pattern is active, the notification is queued in the SMS outbox. Notifications are **not** sent synchronously during the order or stock transaction.

Configure the `order_shipped` SMS pattern using variables in this exact order: `order_number;tracking_code`. Use a separate `order_delivered` pattern.

## Launch requirements

- Run `php spark migrate --all` on staging after taking a database backup.
- Confirm all prices and province coverage are contractually correct before enabling delivery methods.
- Do not enable online payment until the real ZarinPal, SMS, refund and settlement flows have passed end-to-end checks.
- Carrier live tariffs, volumetric weights, exceptions, COD, scheduled slots and shipping-label creation are not currently integrated.
