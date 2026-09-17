# Liberty CBT — Current Memory

- Unified deployment: the Liberty website and CBT student experience share one document root, one origin, one root `.env`, and `/cbt` routes. CBT admin pages/login are absent; the separate admin system manages test packages through the bearer-protected transactional API.

- Create Test Exam/Subject fields use native HTML datalist editable comboboxes backed by active catalogue rows and accept typed additions. Migration `006_catalogue_controls.sql` remains deployed for catalogue storage/reactivation.

Keep concise: verified current state, not chat history or full requirements.

## Current implementation
- Public CBT entry now opens a database-driven exam catalogue instead of the login screen. Exam/subject/filter, schedule and fee browsing requires no account; enroll/pay preserves the selected exam through login and resumes checkout for authenticated students.
- The main Liberty dashboard consumes the public trending endpoint and shows up to four current CBT cards. Published test thumbnails are public catalogue media; profile and question media remain protected.
- Full-stack PHP/MySQL CBT with approved logo and Liberty red/orange/yellow/off-white UI.
- One role-resolving login; no public registration/toggle; blocked students rejected; secure 30-day Remember Me tokens. Login branding uses `Liberty Class and Career`, with the Foundation image anchored at the red panel’s upper-left, one modern outline password-eye control and expanded pre-button spacing. CSS/JS URLs are file-versioned so new releases do not retain stale browser assets.
- Admin: approved reference-style Admin Portal dashboard with real non-deleted-test totals/monthly indicators/live-ending counts and activity, icon activity/modal and quick links; fixed topbar/responsive bottom navigation, shared inner-page Back control and faded-gold/red Logout actions; private compressed WebP test thumbnails, profile upload, typed existing/new exam/subject creation, validated question builder with a contained 220 px desktop Publish panel and per-question local draft restoration, preview/publish, paginated Draft/Published management without Archive or thumbnail-media UI; confirmed soft deletion hides tests from admin/student listings even with attempts while retaining history, labelled eye-icon Result and results/PDF.
- Student: centered animated desktop navigation and database-driven dashboard with debounced discovery plus a compact 34 px yellow magnifier submit fixed inside the search field and golden exam/category card metadata and working Exam/Subject navigation to server-filtered All Tests, independent exam-wise and subject-wise card sections, current-week enrollment trending and completed/average score with a golden `Your week` heading; catalogue, scrollable enrollment dialog, demo payment boundary, and server-classified My Tests Live/Upcoming/Completed states with Start Exam, resumable saved progress/paused remaining time, instructions, attempt and detailed result/PDF.
- Exam: Instructions uses the same left navbar brand as the student dashboard and shows Correct answer before Negative marking; approved reference-style Instructions, dedicated Live Exam chrome with a per-question Mark for review button centered between Previous and Save & Next and detailed Result UI and matching detailed PDF backed by server deadline/resume ownership, immediate UPSERT, offline pending queue, Final Submit blocked until pending answers sync, exact-set scoring and idempotent submission. Resume restores the same attempt/answers/position within 30 minutes; remaining duration pauses at the last server heartbeat and the resumed deadline is capped by the scheduled test end. The open browser submits at timer zero; browser-independent expiry requires the documented once-per-minute finalizer cron. PDO aligns each MySQL session to `APP_TIMEZONE`, so Live classification and Start eligibility use the same clock.
- Create Test thumbnail uses a visible native Choose File input so View/Remove controls remain clickable; all user-managed profile/test/question/option media uses adjacent View/Remove controls, validated names and role/resource authorization; brand assets are not removable. Builder Preview uses compact 86 × 62 px media, aligns correct selectors with A-D, and unlocks Publish only after Preview is opened following the latest edit. PDF templates render outside the application layout.
- Create Test uses placeholder-only numeric fields and enforces End at least Start plus Duration in browser and server; browser POST errors return as branded flash alerts. Tests/questions have hidden eight-character unique keys. Admin media accepts up to 10 MB and stores adaptive WebP no larger than 1 MB.
- Question Builder correct-answer inputs now match the selected type: one radio for Single, multiple checkboxes for Multiple; Publish test remains single-line.
- Browser form success/errors open as top popup alerts with an OK button instead of unobtrusive inline text.

## Database/release
- Fresh schema includes catalogue fields and `remember_tokens`.
- Production upgrade migration: `002_catalogue_and_remember_tokens.sql`.
- Production baseline previously verified at `https://masterjee.guru`; GitHub Actions deploys `main` using versioned releases and health-check rollback.
- Latest changes in this working package are not production-verified until CI, migration and browser smoke tests pass.

## Payment boundary
- Razorpay Test Mode Standard Checkout closes the native enrollment dialog and opens directly above all page content and is implemented with server-created orders, HMAC verification, captured payment lookup, amount/currency checks and idempotent enrollment activation. Credentials remain environment-only; production needs the persistent shared `.env` values configured.
- Real money remains disabled while test credentials are used.

## Remaining validation
1. Run PHP syntax/static/Composer checks in local or CI environment.
2. Apply migration `002` to a disposable `_test` database and run `tests/database-smoke.php`.
3. Browser smoke admin/student/blocked/Remember Me/profile/PDF at 360/768/1280 px.
4. Complete create→preview→publish→enroll→demo-pay→attempt→offline/reconnect→submit→result/PDF test.
5. Add fully automated browser E2E coverage over time.

Never store secrets here; remove resolved limitations and put history in `CHANGELOG.md`.
