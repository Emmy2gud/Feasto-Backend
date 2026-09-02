---
title: Prefer Form Requests
scope:
  - "app/Http/Controllers/**"
rules:
  - id: prefer-form-requests
    message: Use FormRequest classes for validation instead of inline controller validation.
    severity: suggestion
---

Rationale:

Using `FormRequest` classes keeps controller actions focused and centralizes validation rules, messages, and authorization logic.

Example:

Use `php artisan make:request StoreUserRequest` and type-hint it in controller methods instead of calling `$request->validate()` inside the controller.
