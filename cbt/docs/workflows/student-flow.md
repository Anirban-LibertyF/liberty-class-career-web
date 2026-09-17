# Student Flow
1. Shared login -> server resolves active student role.
2. Browse/search/filter published tests by exam, category and subject.
3. Enrollment modal shows fee/start/end/duration/note.
4. Free test activates directly. Paid Continue requests a server-created order and opens Razorpay Checkout from the enrollment modal without an intermediate page; enrollment remains pending until server verification.
5. View Test -> instructions; start disabled before start and rejected after end.
6. Start/resume live attempt; server saves answers/position/review.
7. Submit/expiry creates one authoritative result.
8. Completed card -> Result summary, filters, full review and PDF.

Invariant: browser cannot grant enrollment, alter timer/score or access another student's attempt/result.

- Dashboard discovery previews typed matches after debounce; magnifier/Enter and Exam/Subject changes navigate to server-filtered All Tests. Subject filtering uses the catalogue ID while exam uses its saved name.

- Soft-deleted tests are excluded from dashboard, discovery and My Tests listings; authorized historical results remain retained.
# Public discovery and enrollment

1. A visitor opens the CBT root and browses published available exams without login.
2. The visitor views an exam's subject, schedule, duration, marks and fee.
3. Choosing paid/free enrollment requires the single student login.
4. Successful login returns to the selected exam with `enroll=1`; the existing enrollment request starts and paid tests open verified Razorpay Checkout.
5. Free enrollment activates directly; paid enrollment activates only after server verification.
