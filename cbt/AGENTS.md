# Liberty Class and Career (CBT) — Agent Instructions

## Project

- Paid mock-test platform with two roles: `admin` and `student`.
- One credential-based login; the user role is resolved securely from the database.
- No public registration and no Admin/Student login toggle.
- Stack: PHP 8.2+ (production PHP 8.5), MySQL, vanilla JavaScript, server-rendered PHP, custom router and PDO.
- Production URL: https://masterjee.guru
- GitHub/Git is the source of truth.
- Never edit production application code manually.
- Preserve approved frontend features and extras even if they are beyond the original PRD, unless the owner explicitly requests their removal.

## Start Every Task

1. Read this `AGENTS.md` first.
2. Read `MEMORY.md` to understand the current project state, completed work, limitations and pending work.
3. Read only the additional documentation required for the task.
4. Inspect only the relevant controller, service, view, JavaScript, database and test files.
5. Check the existing Git changes before editing.
6. Never overwrite or remove unrelated user work.

Do not load every project document by default. Keep context and token usage focused.

## Documentation Routing

| Task | Required document |
|---|---|
| Product behaviour or acceptance criteria | `PRD.md` |
| Current state, limitations or pending work | `MEMORY.md` |
| Application boundaries or technical flow | `ARCHITECTURE.md` |
| Schema, migration, relation or database rule | `DATABASE.md` |
| Routes, requests, responses or JSON | `API.md` |
| Branding, layout, components or responsive UI | `UI_GUIDE.md` |
| Verification, test cases or smoke checks | `TESTING.md` |
| Deployment, CI/CD or rollback | `DEPLOYMENT.md` |
| Authentication, uploads or sensitive work | `SECURITY.md` |
| Admin, student or exam journey | `docs/workflows/*.md` |
| Durable architectural decision | `docs/decisions/*.md` |

After routing, inspect only the relevant implementation files.

## Code Map

- `public/index.php`: application bootstrap.
- `app/Config/routes.php`: canonical application routes.
- `app/Controllers`: HTTP handling, authorization and request guards.
- `app/Services`: business rules, transactions and shared domain logic.
- `app/Core`: router, PDO, authentication, CSRF, response and rate limiting.
- `app/Views`: presentation and server-rendered UI.
- Escape dynamic output in views using `e()`.
- `database/schema.sql`: complete schema for a fresh installation.
- `database/migrations`: immutable numbered production migrations.
- `database/seed.sql`: safe initial/demo seed data.
- `scripts`: deployment, migration, rollback and user-management CLI scripts.
- `tests/run.php`: primary automated test entry point.
- `public/assets`: frontend CSS, JavaScript, images and brand assets.
- `storage`: logs, exports and private uploads.

## Product Rules

1. There must be no Admin/Student login toggle.
2. The browser must not select, submit or override a user role.
3. Login credentials are checked against the database and the stored role controls redirection.
4. There is no public registration option.
5. A student must have an active enrollment before accessing a paid test.
6. The server owns test availability, start time, end time, `deadline_at`, resume state and submission state.
7. Browser timers and `localStorage` must never extend an examination deadline.
8. A disconnected attempt may resume only within the permitted resume window and before the final test end time.
9. After the test end time, answers cannot be saved, resumed or changed.
10. The last valid saved response is treated as final when automatic submission or deadline processing occurs.
11. Answer saving must validate:
    - authenticated user;
    - student ownership;
    - attempt status;
    - test deadline;
    - question membership;
    - option validity.
12. `(attempt_id, question_id)` must remain unique.
13. Multiple-answer questions use exact correct-set equality.
14. Submission and scoring must be transactional and idempotent.
15. Browser payment redirection or client-reported success is not proof of payment.
16. Only verified payment-provider data may activate an enrollment.
17. Preserve approved frontend extras unless explicitly removed by the owner.

## Security Rules

- Never commit `.env`, passwords, password hashes, access tokens, API keys, private keys, payment secrets, production database dumps or private uploads.
- Never expose production credentials in code, documentation, logs, screenshots or responses.
- Every state-changing request requires:
  - authentication;
  - role or ownership authorization;
  - server-side validation;
  - CSRF protection.
- Use PDO prepared statements only.
- Passwords must use `password_hash()` and `password_verify()`.
- Never trust a client-provided role, user ID, marks, price, duration, deadline, enrollment status, attempt status or payment success.
- Production must use `APP_DEBUG=false`.
- Do not show raw exceptions, SQL errors, stack traces or credentials to users.
- Rate-limit authentication and other abuse-sensitive endpoints.
- Validate uploads using real MIME type and decoded image content.
- Profile images must be JPEG, JPG or PNG and no larger than 1 MB.
- Store uploaded files using random generated names.
- Keep private uploads outside directly executable public paths whenever possible.
- Re-encode uploaded images when supported.
- Do not commit real student information or sensitive production data as examples.

## Database Rules

- `database/schema.sql` must represent the latest complete fresh-install schema.
- Applied production migrations are immutable.
- Never edit an already-deployed migration to change production behaviour.
- Add a new sequentially numbered migration for every schema or data migration.
- Migrations should be safe to run through the existing CI/CD deployment process.
- Database operations involving payments, submissions, scoring or enrollment activation must use transactions.
- Foreign keys, unique constraints and indexes must remain consistent with documented business rules.
- Never delete or rewrite production data without explicit owner authorization and a recovery plan.

## UI and Brand Rules

- Brand name: Liberty Class and Career (CBT).
- Primary brand colours: red, yellow and orange.
- Main background: off-white.
- Questions, answers and primary reading text: black.
- Use pure white sparingly.
- Keep the design simple, elegant, responsive and accessible.
- Use the approved Liberty logo wherever the brand logo is required.
- Do not distort, recolour, crop or replace the approved logo without owner approval.
- Maintain consistent spacing, typography, button states, hover states and responsive behaviour.
- Verify important UI changes at approximately:
  - 360 px;
  - 768 px;
  - 1280 px.
- Preserve the approved Admin, Student, Instruction, Live Exam and Result page behaviour.

## Change Discipline

- Diagnose the root cause before editing.
- Make the smallest complete and secure change.
- Preserve unrelated contracts, routes, behaviour and UI.
- Keep controllers thin.
- Put shared business, security and transaction logic in services.
- Reuse existing patterns before introducing new abstractions.
- Do not introduce a new framework, package manager, build tool or infrastructure dependency without owner approval.
- Do not silently change database, API or deployment contracts.
- Do not overwrite unrelated work in a dirty working tree.
- Use clear and consistent naming:
  - classes: `PascalCase`;
  - PHP methods and variables: `camelCase`;
  - database identifiers: `snake_case`;
  - migration files: sequential numeric naming.

## Mandatory Documentation Maintenance

Documentation is part of the implementation and must stay synchronized with the code.

After every feature, bug fix, UI change, database change, API change, security change, test change, configuration change or deployment change, update all affected authoritative documents in the same commit.

### Documentation Update Matrix

| Change type | Documents to update |
|---|---|
| New feature or changed business rule | `PRD.md`, `MEMORY.md`, `CHANGELOG.md` |
| Completed feature or current-state change | `MEMORY.md`, `CHANGELOG.md` |
| UI, branding, navigation or responsive change | `UI_GUIDE.md`, `MEMORY.md`, `CHANGELOG.md` |
| Database table, column, constraint or relation | `DATABASE.md`, new migration, `MEMORY.md`, `CHANGELOG.md` |
| API route, validation, request or response | `API.md`, `MEMORY.md`, `CHANGELOG.md` |
| Authentication, authorization or security change | `SECURITY.md`, `MEMORY.md`, `CHANGELOG.md` |
| Architecture, service or application-flow change | `ARCHITECTURE.md`, `MEMORY.md`, `CHANGELOG.md` |
| Admin, student or exam journey change | Relevant `docs/workflows/*.md`, `MEMORY.md` |
| Test or verification change | `TESTING.md` and affected tests |
| CI/CD, hosting, release or rollback change | `DEPLOYMENT.md`, `MEMORY.md`, `CHANGELOG.md` |
| Durable technical decision | New or updated ADR in `docs/decisions/` |
| AI-agent working rule change | `AGENTS.md` |
| No contract or current-state impact | Documentation update may be unnecessary; state why |

### Documentation Rules

1. Update only documents affected by the change.
2. Do not rewrite every documentation file unnecessarily.
3. Implementation and affected documentation must be included in the same commit.
4. `PRD.md` contains authoritative product requirements and acceptance rules.
5. `MEMORY.md` contains the current project state, recent completed work, known limitations, pending work and important active decisions.
6. Keep `MEMORY.md` concise and current; remove or archive stale information.
7. Do not use `MEMORY.md` as an unlimited historical log.
8. Put chronological user-visible history in `CHANGELOG.md`.
9. `ARCHITECTURE.md`, `DATABASE.md`, `API.md`, `UI_GUIDE.md`, `TESTING.md`, `DEPLOYMENT.md` and `SECURITY.md` must describe the current implementation.
10. Add an ADR only for a durable architectural or operational decision.
11. Never add passwords, private keys, tokens, `.env` values, production credentials or sensitive personal data to documentation.
12. Examples must use placeholders such as:
    - `example@example.com`;
    - `your_database_name`;
    - `your_secret_here`.
13. Before finishing, compare the changed code with the affected documentation and remove contradictions.
14. If documentation cannot be updated accurately, report the missing information as a remaining risk.
15. Do not claim that documentation is updated unless the relevant files were actually changed.

## Required Verification

Run the available project checks:

```bash
php tests/run.php
php -l public/index.php
composer validate --no-check-publish
git diff --check
```

Also:

- Run relevant smoke checks from `TESTING.md`.
- Syntax-check every changed PHP file when practical.
- Use a disposable database for migration and integration checks.
- Verify authorization changes with allowed and forbidden roles.
- Verify important UI changes at approximately 360 px, 768 px and 1280 px.
- Never claim an unavailable test passed; report it and rely on equivalent CI checks only when they actually run.

## Git and Deployment Rules

- Git is the source of truth; do not edit production application files manually.
- Review staged files and confirm that no secret, `.env`, private upload, dump or generated log is included.
- Commit implementation, migration, tests and affected documentation together.
- Deploy production only through the configured GitHub Actions pipeline.
- A failed test or health check must stop deployment or trigger the documented rollback.

## Definition of Done

A task is complete only when:

1. The requirement and relevant edge cases are implemented.
2. Authentication, role, ownership, validation and CSRF rules are correct.
3. Exam/payment state remains server-authoritative.
4. Database changes use a safe new migration.
5. Relevant tests pass, or unavailable checks are explicitly reported.
6. Affected documentation matches the implementation.
7. No secrets or unrelated changes are included.
8. Deployment health is verified when deployment is part of the task.

## Final Response Format

Provide only:

1. Outcome.
2. Changed files.
3. Tests and actual results.
4. Documentation updated.
5. Remaining risks or required manual action.

