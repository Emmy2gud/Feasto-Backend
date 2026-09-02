---
title: Service Classes
scope:
  - "app/Services/**"
rules:
  - id: require-service-layer
    message: Encapsulate business logic in service classes under `app/Services` instead of putting it in controllers or models.
    severity: suggestion
---

Rationale:

Service classes make complex business flows testable and reusable. Prefer small, single-responsibility services.

Example:

Create `app/Services/UserService.php` with `create(array $data): User` and call it from controllers or jobs.
