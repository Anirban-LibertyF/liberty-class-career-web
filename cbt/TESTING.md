# Testing

Unified checks also cover `/`, `/cbt`, `/cbt/login`, `/cbt/assets`, student redirects, rejection of `/admin`, 401 for invalid admin tokens, idempotent sync, and 409 after attempts exist.

- Timezone/start regression: with PHP `APP_TIMEZONE=Asia/Kolkata` and a database server using UTC, create a published active-enrollment test whose local window contains the current time; verify My Tests shows Live and Start creates/resumes the attempt instead of returning “This test is not available to start.”

## Command gate
```bash
php tests/run.php
php -l public/index.php
composer validate --no-check-publish
git diff --check
```
Current `tests/run.php` is static architecture/security coverage; add DB integration/E2E coverage over time.

Optional disposable-database contract check:
```bash
APP_ENV=test DB_DATABASE=lcc_cbt_test php scripts/migrate.php
APP_ENV=test DB_DATABASE=lcc_cbt_test php tests/database-smoke.php
```
The `_test` suffix guard prevents this smoke script from running against a normal production database.

## Smoke matrix
- Auth: valid admin/student redirects, invalid/blocked generic rejection, Remember Me restore/revoke, no role override, CSRF/logout.
- Admin: reference dashboard renders DB totals, current-month test count, enrollment growth, live/ending-today count, activity/modal and both real quick links; All Tests offers only Draft/Published filters, no Archive action, labelled eye-icon Result, red Logout states; image accept/reject; invalid/valid schedule; all question types; preview/publish/search/actions/results/PDF.
- Student: independent dashboard exam-wise/subject-wise filters and counts; 360 px modal schedule/fee/note/scroll; free + paid-demo/server-confirmed enrollment; Live & ready/Upcoming/Completed counts; live Start Exam; resumable saved-progress/paused remaining time/Resume Exam; completed Result.
- Exam: reference Instructions metrics/rules/acknowledgement; reject early/late/non-enrolled/cross-owner; Live Exam save each type + refresh, sync message, palette progress, persist review/position, offline queue/reconnect, block Final Submit while pending answers remain; pause at last heartbeat and resume <=30 min only; scheduled-end expiry; submit twice same result; reference Result filters/legend/per-question answers/PDF.
- Scoring: single correct/wrong/skipped; multiple exact/partial/extra; typed normalization/tolerance; decimals/negative; zero-answer accuracy; UI/PDF totals match.
- UI: 360/768/1280, keyboard focus, no overflow/clipping, state not color-only, reduced motion.

## Deployment
GitHub Actions green; `/health` app+DB OK; `/login` and both dashboards; `current` points new release; no browser secret/stack trace; rollback available.

Use disposable DB/sandbox accounts. Never destructive-test real payment/student data or commit credentials/dumps.
- Create Test: confirm empty numeric fields show placeholders; end minimum updates from start plus duration; an earlier end is blocked in JS and independently rejected by PHP with an in-form flash alert.
- Migration: verify existing/new tests and questions have distinct eight-character keys.
- Upload: verify valid image inputs up to 10 MB produce authorized WebP output no larger than 1 MB; invalid MIME/oversize/decode failures show alerts.
- Create Test: Exam and Subject native datalist comboboxes filter saved suggestions while typing and still accept new names.
- Question Builder: Single answer permits exactly one correct-option radio selection; Multiple answer permits multiple checkbox selections; changing Multiple to Single keeps at most one selection; Publish test text does not wrap. Save questions 1 and 2 and confirm serial 2 is green immediately on question 3; Preview must list each position exactly once with compact 86 × 62 px media; Publish starts disabled, Preview enables it, and any subsequent builder input/change disables it again; Previous opens question 2 with server-saved data; unsaved edits survive serial navigation/refresh from the per-test/per-position local draft.
- Create Test thumbnail: use the visible Choose File control, confirm View opens the optimized selection and Remove clears it without reopening the file picker at 360/768/1280 px.
- All media controls: upload/view/replace/remove admin and student profile images, create/view/remove test thumbnails, and upload/view/replace/remove question and A-D option images. Verify removals require the correct role and CSRF, brand assets have no remove action, and files are unlinked only through validated stored paths.
- Builder media: upload/view/replace/remove a question image and A-D option images; verify text-only, image-only and text+image options, at least-two-option validation, private student authorization, optimized WebP storage, Preview/Live Exam/Result rendering and failed-save cleanup.
- Trigger a validation message such as fewer than two options and verify a top popup opens with readable copy and an OK button at 360/768/1280 px. Verify the contained 220 px Publish panel has no overflow and ends just beyond its one-column buttons, collapses below 980 px, and Logout is faded gold with red text.

- Instructions UI: verify its left navbar brand matches the student dashboard and the metrics show Correct answer immediately before Negative marking at 360/768/1280 px.

- Live Exam review control: verify there is no visible review checkbox; the button stays between Previous and Save & Next, toggles `aria-pressed`/purple state per question, persists through navigation, and updates the palette at 360/768/1280 px.

- Razorpay Test Mode: create/reuse a paid pending enrollment, open Checkout, verify success activates it, failure/cancel does not, invalid signature/order/amount/status is rejected, and repeated verified callback is idempotent. Use only environment test credentials.
- Result PDF: compare score stats, every question, state, marks, selected/correct answers and permitted media with the student result page.
- Exam expiry: with the page open verify zero triggers submit and result redirect; with it closed run `scripts/finalize-expired.php` and verify the expired attempt is finalized. Verify selected-option tick is at the option row’s far-right edge at 360/768/1280 px.

- Dashboard discovery: type a title/exam/subject keyword and verify the debounced preview; click the magnifier or press Enter and verify `/tests?q=...` shows only matches. Selecting Exam or Subject must submit immediately to `/tests`, preserve the selected control, and return only matching cards at 360/768/1280 px.

- Dashboard search icon positioning: at 360/768/1280 px confirm the yellow magnifier remains entirely inside the input’s right edge with no overflow, clipping or text overlap.

- Test card/admin delete: verify exam/category metadata is golden and readable; the 34 px search icon stays inside the field at 360/768/1280 px; Admin All Tests has no thumbnail icons. Confirm/cancel deletion of a test with attempts, verify cancel changes nothing, confirm removes it from admin and every student listing, and historical result/attempt records remain accessible to authorized owners.

- Soft-delete dashboard regression: delete a test with enrollments/submissions and verify All Tests, total enrollments, live count and all recent-activity groups exclude it while authorized historical result records remain stored.
- Resume boundary: start and answer a test, leave it with known remaining time, verify My Tests holds the paused value, then resume before 30 minutes and verify the same attempt/answers/current position plus countdown continuing from that value. After 30 minutes or the scheduled test end, resume must fail and saved answers must finalize (with the expiry cron configured).

- Direct checkout regression: submit a paid test enrollment modal and verify the native enrollment dialog closes and Razorpay opens above all page content, no intermediate payment page is shown, cancel restores the enrollment dialog and re-enables Continue, successful Checkout posts to the JSON verification endpoint, and only verified captured payment activates enrollment. Verify free enrollment still redirects to My Tests.
# Public catalogue checks

1. As a logged-out visitor, `/` lists only published, non-deleted tests whose end time is in the future.
2. Search, exam and subject filters return the expected database rows without requiring login.
3. Exam detail shows schedule, duration, marks and fee but no questions or answers.
4. Enroll sends a logged-out visitor to the single login; successful student login returns to the selected exam and starts enrollment/payment.
5. A malicious external or protocol-relative login return path is ignored.
6. Verify the catalogue at 360 px, 768 px and 1280 px.
