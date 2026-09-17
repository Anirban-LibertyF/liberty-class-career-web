# Release Review Prompt
```text
Read AGENTS.md, DEPLOYMENT.md, SECURITY.md and TESTING.md. Review current diff; do not edit.
Check secrets/data exposure; auth/role/ownership/CSRF/SQL; migration/backup;
payment/exam/deadline/idempotency; tests/responsive smoke; CI/CD/health/rollback; docs.
Return only: 1) blockers with file/evidence/fix 2) warnings 3) required tests 4) READY/NOT READY.
```

