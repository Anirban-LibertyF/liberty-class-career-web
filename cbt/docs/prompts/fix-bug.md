# Bug Fix Prompt
```text
Read AGENTS.md first; route only to docs/source relevant to this bug.
Bug: <one sentence>
Expected: <exact behaviour>
Actual/error: <exact behaviour/log; no secrets>
Reproduce: <short steps>

Diagnose root cause before editing. Preserve unrelated features/contracts.
Make the smallest complete safe fix and add/update a regression test.
Do not edit applied migrations/secrets. Run AGENTS.md verification + relevant TESTING.md checks.
Update CHANGELOG; MEMORY only if current state/limitation changed. Complete the fix, not just a plan.
Final only: outcome; root cause; changed files; tests; remaining risk.
```

