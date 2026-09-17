# Security controls

## External administration

The student deployment has no CBT admin UI or admin login. External admin requests use a constant-time checked bearer token from the root `.env`; an empty token denies access. Sync is transactional and refuses question replacement after attempts exist.

## User-managed media controls
Profile and test-thumbnail removal actions require the authenticated owner/admin role and CSRF. File deletion is restricted to validated random WebP paths. Brand/system assets never expose removal controls.

## Question and option media
Question and option images use the existing validated 10 MB input/1 MB optimized WebP pipeline and random private filenames. Admins may view builder media; students may access option media only when the parent question belongs to an actively enrolled test. Remove operations validate stored path format before unlinking.

## Catalogue actions
Catalogue deletion is admin-only, POST-only and CSRF-protected. It uses parameterized updates and soft deactivation, preserving existing test data and foreign-key relationships.

- Passwords use `password_hash`/`password_verify`; blocked students are rejected and login messages do not reveal account existence.
- Remember Me uses a random selector/validator pair, stores only the validator hash, expires after 30 days and uses an HttpOnly, SameSite=Lax cookie (Secure in production).
- Sessions use strict mode, HttpOnly, SameSite=Lax, Secure in production and regenerate ID after login.
- Every write validates CSRF. API identity is read from the server session, never from request `user_id`.
- Every attempt query checks the authenticated owner. Saves check attempt state, question ownership, deadline and option ownership.
- The server's `deadline_at` controls expiry. Browser countdown/localStorage cannot extend it.
- `(attempt_id, question_id)` is unique and responses use UPSERT. Submission uses a transaction plus row lock and returns the existing result when repeated.
- Login, start, save and submit endpoints have configurable database-backed rate limits; 429 responses include `Retry-After`.
- Profile, question and test-thumbnail uploads are stored outside `public`, limited to 1 MB, inspected with `finfo`, decoded and re-encoded to WebP with random names. Test thumbnails are served only for existing published tests (or to admins). Media delivery validates folder/name and applies role/resource authorization.
- Output is escaped by default with `e()`, and PDO emulated prepares are disabled.

Recommended production additions: a strict Content Security Policy, centralized structured logs, alerting, external malware scanning where required, gateway-specific signature verification, backup encryption and periodic access review.
## Image processing
- Image uploads are limited to 10 MB input, verified by MIME and GD decoding, randomized, re-encoded to WebP and adaptively resized/encoded to a maximum 1 MB output.
- Storage remains private and is served only through authorization checks; Cloudflare object storage must not be enabled until production credentials are configured.

## Razorpay verification
- Razorpay credentials are environment-only. Direct modal Checkout receives only the public key ID and server-created order data.
- The completion endpoint is authenticated, CSRF-protected and rate-limited. It compares the returned order with the database order, verifies HMAC-SHA256 using the secret, fetches the payment from Razorpay, and requires matching captured status, amount and currency before activating enrollment.

## Test deletion
Admin test deletion remains authenticated and CSRF-protected. It is a confirmed soft delete: catalogue visibility is removed while related attempts, results, enrollments, payments and audit history remain intact.


## Paused exam resume
Paused time is calculated only from server-owned `deadline_at` and `last_seen_at`; browser values cannot grant extra time. Resume is limited to 30 minutes of inactivity, and every rebuilt effective deadline is capped by the test's scheduled end.
# Public catalogue boundary

- Anonymous visitors may read only published, non-deleted test catalogue metadata (title, taxonomy, schedule, counts, marks, duration and fee).
- Published test thumbnails may be read publicly for catalogue cards. Draft/deleted test images, profiles, questions and answer media retain authentication and authorization checks.
- Questions, answers, attempts, enrollment state and payment creation remain protected by student authentication and existing ownership checks.
- Post-login return targets must be same-origin absolute paths and must not begin with `//`.
