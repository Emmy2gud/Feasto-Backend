---
title: IDE Hints & Docblocks
scope:
  - "app/**"
  - "resources/js/**"
rules:
  - id: docblocks-and-types
    message: Add docblocks and explicit return types to public methods to improve IDE autocomplete and static analysis.
    severity: suggestion
---

Rationale:

Type hints and docblocks make editor suggestions and static analysis more reliable. Use PHP 8 return types where possible.

Example:

`public function handle(Request $request): RedirectResponse`
