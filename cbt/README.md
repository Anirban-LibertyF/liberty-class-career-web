# Liberty Class and Career (CBT)

Complete PHP 8.2 + MySQL 8 + vanilla JavaScript CBT application based on the supplied PRD and the approved Liberty UI. The current frontend's additional screens and visual features are retained and connected to database-backed flows.

## Included

- One login form; active role is resolved from the stored credential, blocked students are rejected, and secure Remember Me is supported.
- Admin dashboard, colorful live statistics, real activity groups/modal, profile image validation, validated Question Builder/full preview, paginated All Tests actions and organized results.
- Student dashboard, searchable exam/category/subject catalogue, scrollable enrollment modal, accurate My Tests tabs, instructions and detailed scheduling.
- Server-authoritative attempts with `started_at`, immutable `deadline_at`, 30-minute resume window and current-question persistence.
- Immediate answer UPSERT, browser pending queue, reconnect synchronization, refresh restoration, network/sync status and server ownership checks.
- Single, multiple exact-match and normalized typed-answer scoring with negative marks; transaction-safe/idempotent submission.
- Student result summary, working result filters, answer analysis and server-generated PDF summary.
- PDO prepared queries, password hashing, strict sessions, role guards, CSRF, database rate limiting, protected upload storage and real MIME validation.
- Normalized InnoDB/utf8mb4 schema, indexes, foreign keys, seed subjects, CLI user creation and deployment/security documentation.

## Project structure

```text
app/
  Config/routes.php
  Controllers/       # HTTP/page/API controllers
  Core/              # Router, PDO, auth, CSRF, rate limit, response/view
  Services/          # Attempt, scoring, payment and upload rules
  Views/             # Auth, admin, student and exam templates
database/
  schema.sql
  seed.sql
  migrations/
public/
  index.php
  .htaccess
  assets/css/app.css
  assets/js/app.js
  assets/images/logo.png
scripts/create-user.php
storage/             # private uploads, logs and exports
tests/
```

## Quick setup

See [SETUP.md](SETUP.md). Never expose the project root as the web root; point Apache/Nginx to `public/`.

## Development documentation

- [AGENTS.md](AGENTS.md): first file for Codex/AI; context routing, rules and Definition of Done.
- [PRD.md](PRD.md): concise requirement IDs and acceptance baseline.
- [MEMORY.md](MEMORY.md): verified current state, limitation and next validation.
- [ARCHITECTURE.md](ARCHITECTURE.md), [DATABASE.md](DATABASE.md), [API.md](API.md)
- [UI_GUIDE.md](UI_GUIDE.md), [TESTING.md](TESTING.md)
- `docs/workflows/`: admin/student/exam journeys.
- `docs/prompts/`: short reusable bug/feature/release prompts.
- `docs/decisions/`: durable architecture decisions.

For AI work, say: “Read `AGENTS.md` first, then only the files it routes for this task.”

## Payment gateway

The project contains a provider-neutral enrollment/payment record model. `PAYMENT_DRIVER=demo` lets the full project be installed without invented production credentials. Before collecting real money, connect the selected gateway SDK/webhook in `PaymentService`, set `.env`, verify amount/currency/signature server-side and mark both payment and enrollment in one transaction. See `DEPLOYMENT.md`.

## Important release note

Change the generated first-admin password, configure HTTPS, install Composer dependencies, connect production gateway credentials, and run database-backed integration/E2E tests in the target hosting environment before accepting real payments or running a live examination.

