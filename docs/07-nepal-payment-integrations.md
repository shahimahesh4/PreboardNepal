# Nepal payment integrations

Updated 14 September 2026. Implementation status: eSewa ePay v2 and Khalti adapters are implemented and tested with mocked responses. Checkout remains disabled by default. No live money movement or merchant acceptance test has been performed.

## Payment choices

| Option | Application status | Next requirement |
| --- | --- | --- |
| eSewa ePay v2 | Signed payment form, signed return validation, server status lookup, order history, access grants and refund-status handling implemented | Merchant product code and secret, sandbox acceptance tests, approval for live use |
| Khalti | Existing server initiation and lookup integration retained; selectable alongside eSewa | Merchant secret and acceptance tests |
| connectIPS | Recommended next option for bank-account checkout; not implemented | Merchant onboarding and current integration contract from NCHL/bank |
| Fonepay dynamic QR | Candidate for mobile-banking QR checkout; not implemented | Acquiring bank/provider onboarding and authenticated order-specific QR/status API specification |
| NEPALPAY QR | Candidate for dynamic QR or gateway payments; not implemented | Enrollment through bank/NCHL and approved acquiring integration |

connectIPS supports checkout through linked bank accounts ([official payment processor overview](https://www.connectips.com/index.php/component/content/article/33-payment-processor)). Fonepay offers QR generated for individual bills ([official business overview](https://fonepay.com/business)). NCHL offers dynamic QR integration and directs merchants to their bank or NCHL for enrollment ([NEPALPAY QR](https://nchl.com.np/nepalpay-qr/)). These options are recommendations, not enabled payment methods in this application.

IME Pay is not a separate new integration: its official site announces the merger and migration into Khalti ([IME Pay notice](https://imepay.com.np/)). Do not advertise it as an independent supported checkout option.

## Configuration

Keep secrets in server environment variables, never in JavaScript or the database's public product data. The existing Khalti variable names remain compatible.

```dotenv
PAYMENTS_ENABLED=false

KHALTI_ENABLED=true
KHALTI_ENVIRONMENT=sandbox
KHALTI_SECRET_KEY=

ESEWA_ENABLED=false
ESEWA_ENVIRONMENT=sandbox
ESEWA_PRODUCT_CODE=
ESEWA_SECRET_KEY=
```

Each provider appears only when the global switch, its own switch, supported environment and required credentials are configured. Products also must be active. Sandbox methods are rejected when APP_ENV is production. The gateway's environment and eSewa merchant code are stored with the order; changing configuration cannot silently verify or pay an old order against another environment/merchant code. Keep the same Khalti merchant account for its existing orders; account/key migration requires an operational plan.

For local acceptance testing, configure official sandbox credentials privately, turn on the desired method and global switch, and create an explicitly labelled test offer in Filament. Visit its checkout as a verified test learner. Do not copy sandbox setup to production. After changing environment configuration, clear/rebuild Laravel's configuration cache as appropriate.

## User experience

1. Learner chooses an active study pass.
2. Checkout shows the exact NPR total, duration, no automatic renewal, and configured payment methods.
3. Learner chooses eSewa or Khalti and creates an order. A request key prevents duplicate order creation; that key cannot change providers.
4. The order page opens the chosen provider using a server-generated form or validated hosted checkout URL. Preboard Nepal never asks for wallet passwords, PINs or OTPs.
5. Return, manual status check and scheduled reconciliation all use server verification before granting access.
6. Order history retains provider, amount, reference and status. A failed browser return can be recovered from order history.

The eSewa form contains a signature, never the signing secret or Laravel CSRF token. Requests to Preboard Nepal retain CSRF protection. Tax/service/delivery fields are currently zero, with the offer price as the total; configure pricing and any required tax breakdown with finance before live activation. This increment does not issue tax invoices.

## eSewa implementation contract

Implementation uses the published [ePay v2 specification](https://developer.esewa.com.np/pages/Epay-V2). Request signatures use HMAC-SHA256 and Base64 over the required ordered fields. Signed returns are checked before lookup; a valid return alone cannot grant access. Lookup uses stored order details and requires matching merchant, reference, exact amount and a completed transaction with a reference ID. Monetary comparisons use integer paisa with strict decimal parsing.

The currently documented sandbox form host is `rc-epay.esewa.com.np`; status enquiries use `uat.esewa.com.np`. Production uses `epay.esewa.com.np`. Endpoint paths are fixed in the adapter. Confirm the merchant's assigned environment during acceptance testing; do not substitute third-party proxy endpoints.

The eSewa documentation also offers an Intent integration. This build uses ePay v2; an Intent migration is a separate adapter change and requires its own merchant acceptance tests.

## Shared integrity rules

- Orders snapshot provider, price, duration and environment from trusted configuration/product data. Checkout fields cannot override them.
- Transaction and provider reference uniqueness is scoped by gateway and environment. Existing orders migrate as Khalti.
- Verification grants one entitlement per order. Renewal starts after the latest non-revoked entitlement ends.
- Refund reports with matching identifiers/amount revoke the associated order entitlement. Partial refunds require review; no refund is initiated by this application.
- Pending, unavailable, unknown and mismatched responses do not grant access. A canceled browser return alone does not mark an order paid or canceled.
- Reconciliation checks pending orders and the last 30 days of paid orders. Older refunds require an explicit staff check. Mismatched configurations are skipped.
- Khalti initiation timeouts are not retried automatically. eSewa uses the same stored order UUID when continuing an existing order.
- Static QR images, payment screenshots and user-entered reference codes are not proof of payment. Adding QR later must use authenticated provider verification before access is granted.

## Acceptance work before enabling live payments

Use the merchant's sandbox to complete success, cancellation, insufficient balance, interrupted return, delayed confirmation, duplicate refresh, refund and amount mismatch scenarios. Confirm settlement/account reporting, callback URLs, live credentials, support ownership and offer terms. Run concurrency tests on the intended production database. Establish alerts for unresolved orders and a documented refund/support process.

No fees or settlement-time guarantees are assumed here; obtain those from each merchant agreement. Bank-transfer review, cards, connectIPS and QR integrations are not implemented in this increment.
