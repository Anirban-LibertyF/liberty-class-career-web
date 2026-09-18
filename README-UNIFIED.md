# Liberty Class & Career — Unified Student Application

This package has one document root, one URL and one root `.env`.

## Routes

- `/` — unchanged Liberty Class & Career student website
- `/cbt` — public CBT catalogue
- `/cbt/login` — CBT student login
- `/cbt/dashboard` — CBT student dashboard
- `/cbt/exam/...` — existing secure exam workflow
- `/cbt/api/admin/health` — protected external-admin connection check
- `/cbt/api/admin/tests/sync` — protected external-admin test/question upsert

The former CBT admin pages and routes are not included. The separate admin application manages the final CBT database by calling the protected API with `Authorization: Bearer <CBT_ADMIN_API_TOKEN>`. The browser must never receive this token.

## XAMPP setup

1. Put this folder inside `C:\xampp\htdocs\`.
2. Copy `.env.example` to `.env` and set the final database, API token and Razorpay test credentials.
3. Create/import the database with `cbt/database/schema.sql` (or run migration `008_external_admin_api.sql` on an existing CBT database).
4. Ensure the service row referenced by `CBT_SYNC_ADMIN_ID` exists in `admins`; it is a database ownership record, not a login to this application.
5. Enable Apache `mod_rewrite` and `AllowOverride All`.
6. Point one local virtual host (for example `lcc.local`) directly to this folder and open `http://lcc.local/`.

The package intentionally uses one origin with root-relative routes. A virtual host is therefore required for the clean `/cbt` URLs.

## External admin API

Send `POST /cbt/api/admin/tests/sync` as JSON. Required top-level fields are `external_id`, `title`, `exam_name`, `category`, `subject`, `full_marks`, `duration_minutes`, `correct_mark`, `starts_at`, `ends_at`, `fee`, `status`, and `questions`. Each question supports `type`, `text`, `correct_mark`, `negative_mark`, `accepted_text`, `numeric_tolerance`, and `options`. Each option supports `key`, `text`, and `is_correct`. The optional `thumbnail` field accepts a JPEG, PNG or WebP data URL; omit it to preserve the current thumbnail or send `null` to remove it.

The sync is transactional. It updates the test and replaces its question set only when no student attempt exists. Once attempts exist, question replacement returns HTTP 409 to protect historical results.
