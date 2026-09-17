# Architecture

The unified root `index.php` dispatches `/cbt*` requests into this router. Static CBT assets remain unchanged under `/cbt/assets`. External administration sends complete test packages to the bearer-protected `/cbt/api/admin/tests/sync` boundary; no browser-facing CBT admin routes are shipped.

```text
Browser -> public/index.php -> Router -> Controller -> Service -> PDO/MySQL -> View/JSON
```

## Boundaries
- Controllers: request parsing, auth/role, CSRF, validation orchestration, response.
- Services: shared domain invariants/transactions (attempt, score, payment, upload).
- Core: infrastructure only; Views: escaped presentation; JS: interaction/display/pending queue only.

## Critical flows
- Public discovery reads only published, non-deleted tests whose window has not ended. It never exposes questions or protected student state. Enrollment stores a same-origin return path, authenticates the user, then continues through the existing server-authoritative payment service.
- Auth checks admin/student stores, rejects blocked students and writes the server session role; optional Remember Me uses selector/validator tokens with only the validator hash stored in MySQL.
- PDO initializes every MySQL session with the current numeric offset of `APP_TIMEZONE`, keeping SQL `NOW()`/`CURDATE()` availability checks aligned with PHP and admin-entered schedules.
- Attempt APIs validate authenticated owner, `in_progress`, effective deadline, question/test and option/question ownership. Resume is allowed only within 30 minutes of the last server heartbeat. The server preserves remaining duration as deadline minus last activity, rebuilds the effective deadline on resume, and caps it at the scheduled test end.
- Responses UPSERT by `(attempt_id,question_id)`; position/review persist. Final Submit is blocked while browser answers remain unsynced. Submit locks/transactions, scores once and returns the stored result on repeat.
- `PaymentService` is the Razorpay provider boundary: the enrollment modal requests a server-created Order and opens Checkout directly, while enrollment activates only after stored-order HMAC plus captured payment/amount/currency verification.
- `UploadService` validates <=1 MB real profile/question/test-thumbnail images, re-encodes/randomizes them as WebP in private storage; authorized `MediaController` serves it.

## Deployment
```text
main push -> tests/build -> SSH/SCP -> releases/<timestamp>
-> link shared/.env + storage -> migrate -> switch current
-> sync public/ -> /health -> rollback on failure
```
See `docs/decisions/ADR-001-release-deployment.md`.

## Extension
- Put reusable/security/transaction rules in services, not JS/views.
- Preserve route/response contracts; document changes in `API.md`.
- Schema evolution always gets a new migration; changing `schema.sql` alone is insufficient.
- PublicKeyService generates collision-checked eight-character internal keys for tests and questions.
- Browser POST exceptions use PRG flash alerts; API exceptions remain JSON.
