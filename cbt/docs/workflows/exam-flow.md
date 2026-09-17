# Exam Flow
```text
published + active enrollment -> upcoming -> live/start -> instructions -> in_progress
-> save answer/position/review -> reconnect/restore -> submit/expire -> completed/result/PDF
```
- PHP and each MySQL session use the same `APP_TIMEZONE` offset, so My Tests classification and Start eligibility evaluate the same schedule window.
- Initial effective deadline is earlier of test end and `started_at + duration`; a valid resume rebuilds it from paused remaining time but never beyond test end.
- DB is answer truth; browser may queue unsent changes, but Final Submit is blocked until every pending answer synchronizes successfully.
- My Tests classifies active enrollments from server timestamps into Live & ready, Upcoming and Completed. A live unstarted test links to Instructions; an `in_progress` attempt is shown as resumable when its server-recorded paused remaining time is positive and it is within the 30-minute interruption window.
- Resume only for owner, `in_progress`, scheduled test still open, interruption <=30 minutes. My Tests displays remaining time paused at the last heartbeat; only the server rebuilds the effective deadline on Resume.
- Correct answers are hidden before submit.
- Submit locks attempt, returns existing result if repeated, scores/persists/commits once.
- The reference-style Instructions, Live Exam and Result views are presentation layers over these server-owned states; they do not calculate deadlines, ownership, scoring or submission authority in the browser.

- Instructions reuses the student dashboard brand treatment and shows both per-question correct and negative marks before Start.

- Mark for review is a per-question middle action button; toggling it syncs the existing server-owned review flag and palette state.

- With Live Exam open, countdown zero submits through the API. The once-per-minute `scripts/finalize-expired.php` cron is the browser-independent finalizer for closed/offline pages.

- Leaving Live Exam pauses remaining duration at the last server heartbeat. Resume within 30 minutes restores the same attempt, answers and current position, rebuilds the effective deadline from the paused duration, and never extends past the scheduled test end.
