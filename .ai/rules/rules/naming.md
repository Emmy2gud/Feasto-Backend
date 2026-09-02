---
title: Naming Conventions
scope:
  - "app/**"
rules:
  - id: naming-conventions
    message: Follow project naming conventions: Models `PascalCase` singular, Controllers `PascalCaseController`, variables `camelCase`, methods `camelCase`.
    severity: suggestion
---

Rationale:

Consistent naming improves readability and aligns with Laravel conventions.

Examples:

- `User` (model)
- `UserController` (controller)
- `$userRepository` (variable)
