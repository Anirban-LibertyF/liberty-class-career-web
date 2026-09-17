# Change log

## 2026-09-17 - Unified student application
- Moved public, student, exam, result and payment routes under `/cbt` without redesigning views.
- Removed CBT admin UI/routes and restricted interactive login to students.
- Added root `.env`, protected external-admin API and `tests.external_id`.

## 2026-08-26
- Fixed Razorpay Checkout appearing behind the native enrollment dialog by closing that top-layer dialog before Checkout and restoring it when payment is dismissed.
- Styled the Student Dashboard `Your week` heading in gold and opened Razorpay Checkout directly from the enrollment modal without the intermediate payment page.
- Paused in-progress exam time at the last server heartbeat, restored that remaining duration on resume within 30 minutes, and retained the scheduled test end as a hard cap.
- Removed soft-deleted tests from Admin Dashboard enrollment/live/activity data and clarified that resume preserves attempt state but never pauses or resets the exam timer.
- Styled student exam/category metadata as a golden pill, reduced and centered the dashboard search icon, removed All Tests thumbnail controls, and allowed confirmed soft deletion while preserving attempt history.
- Locked the dashboard magnifier inside the search input and changed it to the Liberty yellow/dark-red treatment.
- Fixed dashboard discovery: added a right-end search icon, routed search/Exam/Subject controls to filtered All Tests, corrected Subject ID submission and expanded keyword matching.
- Added Razorpay Test Mode Orders/Checkout with server-side signature, captured-status, amount and currency verification; upgraded student result PDF to detailed webpage parity and aligned selected-option ticks at row end.
- Replaced the Live Exam review checkbox with a synced toggle button centered between Previous and Save & Next.
- Matched the Instructions navbar brand to the student dashboard and added Correct answer before Negative marking in its metrics.
- Fixed live tests being rejected at Start when PHP used Asia/Kolkata but the hosting MySQL session used UTC; every PDO connection now aligns SQL time functions with `APP_TIMEZONE`.
- Replaced the thumbnail-wide invisible drag/drop input with a visible Choose File control, fixing selected-thumbnail View and Remove clicks.
- Standardized View/Remove icon controls across profile, test-thumbnail, question and option media; added protected profile/thumbnail removal.
- Made Preview media compact, aligned A-D selection controls, and required opening Preview after the latest edit before Publish becomes enabled.
- Added optional A-D option images with adjacent View/Remove controls and private rendering in Preview, Live Exam and Result; migration `007_option_images.sql`.
- Fixed the lingering PHP by-reference loop that duplicated the final preview question and delayed the current saved serial’s green state.
- Fixed Question Builder Save & Next/Previous navigation, green saved serials and per-question local draft restoration; contained the Preview/Publish actions in a compact 220 px panel.
- Restored Exam Name and Subject to native HTML datalist editable comboboxes while retaining typed additions and saved suggestions.
- Added searchable custom Exam and Subject menus with per-item × removal on Admin Create Test.
- Added safe catalogue deactivation/reactivation and migration `006_catalogue_controls.sql`.

## Unreleased
- Reduced the Question Builder action panel to a compact 230 px desktop width so its card ends shortly after the Publish button.
- Restyled Logout with faded gold and red text, promoted flash validation/errors to top popup alerts with OK acknowledgement, and widened the responsive Question Builder Publish panel.
- Corrected Question Builder selection controls so Single answer accepts only one correct radio, Multiple answer accepts multiple checkboxes, and Publish test remains on one line.
- Removed Archive from the Admin All Tests status filter and row actions, replaced the result glyph with a labelled eye-icon Result action, and redesigned admin Logout controls in red with hover/focus feedback.
- Rebuilt the production Admin Dashboard, Instructions, Live Exam and Result screens to the approved reference hierarchy while retaining real database stats/activity, CSRF Start Test, server timer/resume/autosave, scoring, filters and PDF behavior.
- Split dashboard discovery into independent Exam-wise and Subject-wise test sections, and rebuilt My Tests with backend-driven Live & ready/Upcoming/Completed tabs, Start Exam, resumable saved-question progress, stored-deadline remaining time and Resume Exam.
- Simplified student discovery to separate exam and subject sections, removed dashboard category filters, centered the student navigation with animated active/hover states, strengthened CBT branding, spaced profile actions, and made Create Test exam/subject menus accept new catalogue values.

- Rebuilt the student dashboard with bold navbar branding, debounced red-panel discovery, weekly enrollment-ranked trending tests, weekly completion/average score, designed View All action and database-driven horizontal filters; upgraded Create Test searchable exam/subject and category controls.

- Standardized all product branding to `Liberty Class and Career`, repaired the admin topbar/responsive navigation, added shared inner-page Back navigation, and added browser-compressed private WebP test thumbnails.

- Added file-version cache-busting for CSS/JavaScript so deployed login layout changes appear immediately, and replaced the password control with a modern outline SVG eye.

- Refined the login page with the supplied Liberty Foundation image anchored at the red panel’s upper-left, one accessible in-field password eye toggle with native duplicate controls suppressed, a 30 px Remember Me-to-button gap, `Log in` button text and `LIBERTY CLASS AND CAREER (CBT)` wording.
- Added secure persistent Remember Me tokens and blocked-student rejection.
- Prevented Final Submit while answers remain unsynced; added deadline/state and answer/position validation.
- Added resource-aware private media authorization and hardened image storage errors.
- Completed admin real-data activity modal, question prefill/validation/preview, test actions/pagination and average-score results.
- Added exam/category catalogue fields and filters, subject sections, scrollable enrollment dialogs, accurate My Tests counts and disabled upcoming/closed actions.
- Added migration `002_catalogue_and_remember_tokens.sql`, stronger static checks and guarded disposable-database smoke verification.
- Fixed PDF rendering so export templates render without the normal application layout.
- Added one-minute stale/deadline attempt finalization and stronger CI PHP linting.

## 1.1.0 - AI development context pack

- Added project-specific `AGENTS.md`, concise PRD/MEMORY, architecture, database, API, UI and testing contracts.
- Added admin/student/exam workflows, token-optimized prompts and deployment ADR.
- Added README documentation index for narrow, lower-token agent context.

## 1.0.0

- Rebranded to Liberty Class and Career (CBT) with red/yellow/orange/off-white design and supplied logo.
- Preserved the expanded approved frontend screens and converted primary flows to database-driven PHP.
- Added role-aware credential login with no student/admin toggle and no public registration route.
- Added admin/student dashboard, test creation, builder, catalogue, enrollment, instructions, secure live attempt, result/review and PDF.
- Added server timer, 30-minute interruption resume policy, local pending-answer queue, reconnect sync, scoring, authorization, rate limiting and upload protection.
## 2026-08-25
- Replaced Create Test numeric defaults with placeholders and added duration-derived End scheduling warnings plus server enforcement.
- Replaced standalone browser POST validation error pages with branded in-form flash alerts.
- Added hidden unique eight-character keys for every test and question.
- Raised validated admin image input to 10 MB with adaptive 90%-starting WebP optimization capped at 1 MB.
# Unreleased

- Added a public database-driven CBT exam/subject catalogue at the application root.
- Added fee/schedule visibility before login and preserved selected-exam continuation through login into enrollment/payment.
- Added responsive public CBT navigation and catalogue styling.
- Added the public trending-test feed used by the main dashboard and public access for published catalogue thumbnails only.
