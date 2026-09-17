# Route/API Contract

## External admin bridge

`GET /cbt/api/admin/health` and `POST /cbt/api/admin/tests/sync` require `Authorization: Bearer <CBT_ADMIN_API_TOKEN>`. Sync upserts by `external_id` and transactionally replaces questions/options. A test with attempts returns 409. All student/public routes use `/cbt`; former `/admin*` routes are absent.

## Profile and test-thumbnail removal
Admin/student profile upload endpoints accept CSRF-protected `remove_photo=1` to clear and unlink the current profile image. `POST /admin/tests/{id}/thumbnail/remove` is admin-only and CSRF-protected; it clears and unlinks the stored test thumbnail.

## Question Builder media fields
`POST /admin/tests/{id}/questions` accepts optional `image`, `option_image_A` through `option_image_D`, `remove_question_image`, and `remove_option_image_A` through `remove_option_image_D`. Admin authentication, CSRF, MIME/decode validation and server-side text-or-image option validation apply. Removal is committed with the question update.

## Admin catalogue removal
`POST /admin/catalogue/{type}/delete`, where `type` is `exam` or `subject`, requires an authenticated admin and a valid CSRF token. The `name` form field identifies the row to deactivate. Success redirects to Create Test with a flash confirmation.

Canonical routes: `app/Config/routes.php`. All writes require CSRF; identity comes from session.

## Public/auth
`GET /health`; `GET /` and `/login`; `POST /login`; `POST /logout`.

## Student
`GET /dashboard`, `/tests`, `/my-tests`, `/test/{id}`, `/payment/{id}`; `POST /test/{id}/enroll`, `/api/tests/{id}/enroll`, `/payment/{id}/complete`, `/api/payments/{id}/complete`, `/profile-photo`.

## Admin
`GET /admin`, `/admin/tests`, `/admin/tests/create`, `/admin/tests/{id}/questions`, `/admin/tests/{id}/results`, `/admin/tests/{id}/results/pdf`.

`POST /admin/tests` accepts an optional <=1 MB JPG/JPEG/PNG thumbnail and stores a randomized WebP path; `/admin/tests/{id}/questions`, `/admin/tests/{id}/publish`, `/admin/tests/{id}/archive`, `/admin/tests/{id}/delete`, `/admin/profile-photo`.

## Exam/result
`GET /exam/{id}/instructions`, `/attempt/{id}`, `/result/{id}`, `/result/{id}/pdf`; `POST /exam/{id}/start`.

## Attempt JSON
- `POST /api/attempts/{id}/answer`: question ID, option IDs or typed answer, review flag, CSRF.
- `POST /api/attempts/{id}/position`: current position.
- `POST /api/attempts/{id}/submit`: idempotent final submission.
- `GET /api/attempts/{id}/state`: reconnect/refresh state + server time/deadline; no correct answers before submit.

Success: `{"ok":true,"data":{}}`. Error: `{"ok":false,"error":{"code":"VALIDATION_ERROR","message":"Safe message","fields":{}}}`.

Attempt state enforces ownership, active status, the 30-minute resume rule and the server deadline. Single-answer saves accept at most one option; question position must be within the test range.

Status guidance: 400 malformed, 401 unauthenticated, 403 ownership/role, 404 absent, 409 state conflict, 422 validation, 429 rate limit, 500 unexpected.

Never accept role/student ID/score/deadline/payment success as client truth. `GET /media/{folder}/{name}` must allowlist folder/name, prevent traversal and authorize private media.
## Browser validation errors
- Non-API POST failures redirect to the originating application path with a session flash alert and safe old input.
- API failures retain structured JSON and their original HTTP status.
- Create Test rejects an end earlier than start plus duration.

## Razorpay payment completion
`POST /api/tests/{test_id}/enroll` creates or reuses the pending enrollment/order and returns the safe Razorpay Checkout payload so the enrollment modal can open Checkout directly. `POST /api/payments/{enrollment_id}/complete` is its JSON verification endpoint; the non-API payment routes remain fallback-compatible.

`POST /payment/{enrollment_id}/complete` and its API equivalent accept `razorpay_payment_id`, `razorpay_order_id`, and `razorpay_signature` from Standard Checkout plus CSRF. Enrollment activates only after server-side ownership, order, HMAC, captured-status, amount and currency verification.
# Public catalogue routes

- `GET /` and `GET /catalogue`: published, non-deleted, non-expired test catalogue with optional `q`, `exam`, and `subject` filters.
- `GET /api/public/trending`: up to four current tests ordered by active enrollments from the current week; exposes catalogue metadata only and allows cross-origin reads by the Liberty dashboard.
- `GET /test/{id}`: public exam metadata and enrollment fee. Questions and answers are never returned.
- `GET /login?return=/test/{id}?enroll=1`: preserves a validated same-origin continuation for student enrollment/payment.
