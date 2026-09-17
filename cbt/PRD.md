# Liberty Class and Career (CBT) - Concise PRD

Deployment note: CBT is the `/cbt` student module of the unified Liberty Class & Career application. Its former admin UI is out of scope; a separate admin product publishes through the protected server-to-server API.

Version 2.1 implementation baseline. The original full A4 PRD remains the visual/development reference; this file is optimized for agents.

## Goal and roles
Paid online mock tests: admin creates/publishes/reviews; student discovers/enrolls/pays/attempts/resumes/reviews. Existing approved frontend extras remain unless explicitly removed. Admin and student only; no public self-registration.

## Requirement index
| ID | Requirement + acceptance |
| --- | --- |
| AUTH-001 | One ID/password login, eye + secure 30-day Remember Me; DB resolves active admin/student; blocked students rejected; no toggle/register; generic errors. |
| ADM-001 | Colorful stats/activity modal/profile image; Prosenjit Roy account display; no decorative dot. |
| TST-001 | Draft test: compressed WebP thumbnail, subject, count/full marks, duration, marking, start/end, fee; end > start. |
| QST-001 | Ordered single/multiple/typed questions, question media plus optional A-D text-or-image options, rounded marks and non-duplicated full preview. |
| TST-002 | Publish only valid complete test; management search/full-width aligned table/icons/archive/delete/results/PDF. |
| STU-001 | Anirban Hazra account display; catalogue/trending/subject cards/search/filter/profile; responsive hero/stats. |
| ENR-001 | Scrollable modal with fee, start/end/duration/row lines/note; paid Continue opens Razorpay directly without an intermediate page; no Test Code/Student ID. |
| PAY-001 | Paid enrollment opens Razorpay directly from the enrollment modal; only server-verified payment activates enrollment, while free tests may activate directly. |
| MYT-001 | Functional Live & ready/Upcoming/Completed counts; live tests expose Start Exam or resumable progress plus server-deadline remaining time; early Start disabled; completed eye/Result action. |
| EXM-001 | Instructions show schedule, marks, stable internet and 30-minute resume Special Note. |
| EXM-002 | Server timer, left question/right palette, save/review/nav/final submit, responsive navbar. |
| EXM-003 | Resume an in-progress attempt within 30 minutes using remaining time paused at the last server heartbeat; scheduled test end is the hard cap. |
| SCR-001 | Single, multiple exact-set, typed normalized/tolerance scoring + configured negative marks. |
| RES-001 | Score summary, working filters, all-question analysis, correct/student answer and PDFs. |
| UI-001 | Official logo; red/orange/yellow/off-white/black; simple elegant responsive accessible UI. |
| REL-001 | Immediate UPSERT, pending reconnect sync, ownership, transaction/idempotent submit. |

## Admin requirements
- Dashboard matches the approved reference hierarchy: Admin Portal topbar, greeting/Create Test header, large colored All Tests/Total Enrollments/Live Tests cards with real monthly/today indicators, icon-led recent activity and two full-width quick actions. All values and activity remain database-driven.
- Activity is derived from real enrollment/publication/submission/window data with type/description/time columns and dynamic student/test/score emphasis. View All shows last five per type in a closable modal.
- Profile click opens JPEG/JPG/PNG drag/drop, max 1 MB, with adjacent View/Remove controls. Every user-managed media field (profile, test thumbnail, question and option images) exposes consistent View/Remove actions; Create Test thumbnail uses a visible native Choose File control so its actions cannot be covered by an invisible drop overlay; brand assets are not removable.
- Create Test uses native editable datalist comboboxes for exam and subject; either field filters saved suggestions while typing and accepts a new value through the normal Save flow. Category remains a dropdown. Date/time controls show orange focus. Builder shows `QUESTION n OF total` and rounded marks; Preview/Publish together below; no student status legend.
- Builder media fields expose adjacent View and Remove icons for stored or newly selected question/option images. Each A-D option accepts text or an optional image, with at least two populated options required. Saved serials update on the next render without lag and Preview contains each database question exactly once. Preview shows metadata then every question/options/correct answer. All Tests uses spaced search/full-width responsive table: Test Details, Status, Enrollments, Start Date, Actions. Result summary organized; Download top-right.

## Student/catalogue/enrollment
- The CBT landing page is a public, database-driven catalogue. Visitors can view available exams, subjects, schedules, duration, marks and enrollment fees without logging in.
- Enrollment/payment remains protected. A visitor who chooses an exam is sent to the single login and, after successful student login, returned to that exam so enrollment and Razorpay Checkout can begin automatically.
- Dashboard: full-width red hero with debounced title discovery plus visually separate exam and subject filters, yellow READY TO PRACTISE?, current-week completed/average score, enrollment-ranked trending cards, and two independent database-driven card sections: Exam-wise Mock Tests and Subject-wise Mock Tests. Category filters are not shown on the student dashboard.
- View All Tests: dedicated search + exam/category + subject filters and the same card design. Upcoming/Completed tabs show accurate database counts.
- Test cards show schedule clearly. Enrollment modal scrolls on small screens and shows fee/start/end/duration with separators.
- Note: start before end time or the exam cannot be taken. Do not show test code/student ID.
- My Tests defaults to Live & ready, with separate Upcoming and Completed tabs/counts. An unstarted live test opens Instructions through Start Exam; an active resumable attempt shows saved-question progress, paused remaining time and Resume Exam. Upcoming is disabled before start; completed shows eye/Result.

## Instructions and live exam
- Instructions match the approved reference structure with test context header, four database-driven metric cards, important rules, Special Note, acknowledgement, Back and CSRF-protected Start Test.
- Navbar/logo matches My Tests and stays horizontal. Timer: stopwatch left; right side has TIME REMAINING above countdown; contained in navbar.
- Special Note: stable internet/power; paused remaining time may resume within 30 minutes, before the scheduled test end. After expiry no resume/response; last server-saved response is final.
- Question Builder keeps a compact contained question palette/action panel; saved serials turn green, Previous opens the preceding question, and per-question unsaved text/selections persist locally while editing. Question left, palette right (stack mobile); serial and rounded `+correct / -negative` visible. Palette contained; answered green, unanswered yellow, review blue/purple with labels/contrast; Final Submit below.
- Mark for Review toggles and persists. Refresh/reconnect restores answers, position and review. Browser display cannot change server deadline.

Initial effective deadline is the earlier of test `ends_at` and `started_at + duration`; a valid resume rebuilds it from server-recorded paused remaining time without passing `ends_at`.

## Scoring/result
- Result matches the approved completed-test hierarchy with student greeting, PDF action, score/stats panel, filter counts, answer legend, per-question state/marks and end summary; every value comes from the stored scored attempt.
- Single correct => positive, answered wrong => negative, skipped => 0.
- Multiple correct only when selected IDs exactly equal correct set; partial/extra is wrong.
- Typed: trim, case/whitespace normalization; accepted strings and optional numeric tolerance.
- Store score/full marks, correct, wrong, skipped, negative marks and accuracy once. Accuracy = correct/(correct+wrong)*100; zero answered => 0.
- Result filters All/Correct/Wrong/Skipped update review. Detailed Review lists every question/options/student answer/correct answer. Server PDFs match UI totals.

## UI/accessibility
- Logo: `public/assets/images/logo.png`; do not redraw/distort.
- Prefer off-white over white; question/answer black; brand red/orange/yellow.
- No squeezed sidebar content, horizontal overflow, clipped navbar/timer/palette or unreachable modal action at 360/768/1280 px.
- Visible keyboard focus; state is not color-only; respect reduced motion.

## Out of scope until approved
Public registration, client-authoritative timer/scoring/payment, exposing private uploads/secrets, editing applied migrations.

Current payment mode is `demo`; it may simulate activation for development only. Real-money collection remains out of scope until a provider and verified webhook credentials are supplied.
## Validation and media acceptance
- Create Test numeric fields use examples as placeholders, not prefilled values.
- End date/time must be at least start date/time plus duration; client warning and server validation both block invalid submission.
- Browser form validation failures return to the originating form as branded alerts instead of a standalone 422 page.
- Every test and question has an internal, non-UI, unique eight-character alphanumeric key.
- Admin image inputs accept JPEG/PNG up to 10 MB and store optimized WebP output no larger than 1 MB.
## Question Builder selection
- Single-answer questions allow exactly one correct option through radio controls.
- Multiple-answer questions allow one or more correct options through checkbox controls.
## Feedback visibility
- Browser form success, validation and error feedback opens as a top popup alert with a clear OK acknowledgement.

- Paid enrollment uses Razorpay Standard Checkout with server-created orders and server verification of signature, captured status, amount and currency before enrollment activation.

- Admin All Tests does not expose thumbnail View/Remove controls. Confirmed Delete soft-deletes a test even when attempts exist, removes it from admin/student listings, and retains historical attempts/results/payments.

- Soft-deleted tests and their enrollments/submissions do not contribute to current Admin Dashboard totals, live counts or recent activity. Historical records remain stored.
- An in-progress exam pauses at the last server heartbeat and may resume within 30 minutes with that remaining duration restored; the scheduled test End Date/Time remains an absolute hard stop.
