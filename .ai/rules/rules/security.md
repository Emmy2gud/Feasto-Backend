---
title: Security Best Practices
scope:
  - "app/**"
  - "routes/**"
rules:
  - id: avoid-raw-sql
    message: Avoid raw SQL queries; prefer query builder or parameterized statements to prevent SQL injection.
    severity: warning
  - id: require-authorization
    message: Ensure authorization checks (policies or `authorizeResource`) are used for controller actions that modify sensitive data.
    severity: warning
  - id: no-eval
    message: Do not use `eval()` or other dynamic code execution.
    severity: error
---

Rationale:

Security rules help prevent common vulnerabilities. Keep severity strict for dangerous patterns.

Examples:

- Use Eloquent or `DB::select()` with bindings instead of concatenating SQL strings.
- Use policies or `Gate::authorize` before performing destructive actions.
