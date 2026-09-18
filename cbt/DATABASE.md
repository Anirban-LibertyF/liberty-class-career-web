# Database Contract

## Option media
Migration `007_option_images.sql` adds nullable `question_options.image_path`. An option is valid when it has text, an image, or both; existing question and option rows remain unchanged. Question/option media paths reference private optimized WebP files in the `questions` upload namespace.

## Exam and subject catalogue
Migration `006_catalogue_controls.sql` adds `subjects.is_active` and the `exam_catalogue` table (`name`, `is_active`). Catalogue removal is a soft deactivation; test records and their subject relationships remain intact. Creating a test with an inactive name reactivates that catalogue row.

Canonical install: `database/schema.sql`; production history: `database/migrations/`.

| Table | Purpose | Key invariant |
| --- | --- | --- |
| `admins`, `students` | identities/profiles | unique email; student phone/status |
| `subjects`, `topics` | catalogue taxonomy | unique slug/topic per subject |
| `tests` | exam/category/thumbnail/schedule/fee/marks/status | end > start; catalogue index; subject/admin FKs |
| `questions`, `question_options` | ordered content/answers | unique position and A-D key |
| `cbt_import_sources` | idempotent DOCX import provenance | one stable source key and one source row per imported test |
| `enrollments`, `payments` | access/commerce | unique student-test/order/payment |
| `attempts` | timer/state/result | server deadline + owner |
| `attempt_responses`, `response_options` | saved answers | unique attempt-question/selection |
| `audit_logs`, `rate_limits` | activity/abuse control | indexed time/type/action |
| `remember_tokens` | persistent login | random selector; hashed validator; expiry index |

Relationships: admin->test->question->option; subject->test/topic; student->enrollment->test->attempt->response->selected options; enrollment->payment.

Statuses: student `active|blocked`; test `draft|published|archived`; enrollment `payment_pending|active|cancelled|refunded`; payment `pending|paid|failed|refunded`; attempt `in_progress|submitted|expired`; question `single|multiple|typed`.

## Truth
- Test schedule/duration + attempt `started_at/deadline_at` control access.
- DB responses are answer truth; localStorage is never authoritative.
- Final summary is stored only after server scoring; paid status only after verification.

## Migration rules
1. Add immutable zero-padded migration, e.g. `002_add_gateway_reference.sql`.
2. Never edit applied `001_initial_schema.sql`.
3. Prefer additive/backward-compatible changes; backup/review destructive changes.
4. No secrets/environment values in SQL; test `php scripts/migrate.php` on disposable DB.
5. Update this contract/tests when constraints/statuses change.

Migration `004_subject_catalogue.sql` safely adds common science, competitive-exam and nursing subjects with `INSERT IGNORE`.

Migration `003_test_thumbnails.sql` adds nullable `tests.thumbnail_path` for private WebP thumbnails.

Competitive DOCX comprehension content is stored directly in `questions.question_text` as `[COMPREHENSION PASSAGE]`, the complete source passage, `[QUESTION]`, and the individual question. The existing `TEXT` capacity is sufficient for the inspected source maximum (10,385 bytes), so no question-column conversion is required.

Admin CBT migration `011_competitive_mcq_content.sql` is the portable baseline for the validated competitive catalogue: 33 tests, 970 questions and 3,880 options. Running the normal CBT migrations on a fresh machine installs this dataset without rerunning the DOCX importer.

Migration `002_catalogue_and_remember_tokens.sql` adds `tests.exam_name`, `tests.category`, the catalogue index and hashed persistent-login tokens.

## Internal record keys
- Tests and questions have unique, required CHAR(8) internal identifiers.
- Migration 005_record_unique_keys.sql safely backfills existing rows and adds unique indexes.
