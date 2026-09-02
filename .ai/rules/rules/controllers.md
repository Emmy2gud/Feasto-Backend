---
title: Controller Structure
scope:
  - "app/Http/Controllers/**"
rules:
  - id: controller-thin
    message: Controllers should be thin and delegate business logic to services and form requests.
    severity: warning
---

Rationale:

Keep controllers focused on HTTP concerns (request/response). Move validation and business rules to `FormRequest` and service classes respectively.

Example:

`$service->createUser($request->validated());`
