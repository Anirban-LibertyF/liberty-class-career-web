# ADR-001 - Versioned Release Deployment
- Status: Accepted
- Date: 2026-08-24

## Decision
Use GitHub Actions + SSH with `/home/diabkloz/lcc_cbt/releases/<timestamp>`, persistent `shared/.env` and `shared/storage`, atomic `current`, and public-only sync to `/home/diabkloz/masterjee.guru`. Migrate before switch, health-check after switch, rollback to previous release on failure, and preserve `.well-known`.

## Consequences
Repeatable isolated deployments and persistent secrets/uploads; fast app rollback. DB rollback remains separate risk, so migrations should be additive and destructive changes require verified backup. Other domains/projects need separate paths and preferably separate deploy key/DB user.

