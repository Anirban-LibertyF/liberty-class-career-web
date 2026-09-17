# Deployment and release checklist

1. Create a database backup and upload backup.
2. Deploy to a versioned release directory; keep the previous release intact.
3. Run Composer with optimized production autoloading.
4. Apply reviewed migrations inside a maintenance window.
5. Point the web server only at `public/`; block PHP execution under `storage/uploads`.
6. Enable HTTPS, secure cookies, HSTS, CSP, `X-Content-Type-Options`, clickjacking protection and sensible request-size limits.
7. During the approved demo phase keep `PAYMENT_DRIVER=demo` and show the demo disclosure. Before accepting money, configure the real payment adapter and webhook secret. Verify transaction ID, signature, order, amount and INR currency on the server. Process callbacks idempotently.
8. Run `composer test`, then test admin publish, free enrollment, paid sandbox enrollment, refresh/reconnect, timeout and matching result/PDF totals.
9. Verify 360 px, tablet and desktop layouts.
10. Switch the release symlink/document root only after checks pass.
11. Configure the one-minute `scripts/finalize-expired.php` cron described in `SETUP.md` and verify its log is writable.

Rollback: restore the previous release and, only when a migration is incompatible, restore the pre-release database and uploads together. Never restore one without the other when questions or tests reference uploaded images.

## Production payment adapter boundary

`App\Services\PaymentService` is the only provider-facing service. Preserve the `payments.provider_order_id` and `provider_payment_id` unique constraints. A browser redirect is never proof of payment; only a verified callback/webhook activates enrollment.

The current `demo` adapter is intentionally non-financial and must not be presented as a successful real payment. Provider credentials belong in environment secrets, never source control.


## Razorpay Test Mode environment
Set `PAYMENT_DRIVER=razorpay`, `RAZORPAY_KEY_ID`, `RAZORPAY_KEY_SECRET`, and optionally `RAZORPAY_WEBHOOK_SECRET` in the persistent production `shared/.env`; never place values in Git or release archives. Configure `scripts/finalize-expired.php` as a once-per-minute server cron so attempts finalize even when the browser is closed.
