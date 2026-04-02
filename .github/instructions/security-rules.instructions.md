---
name: "security-rules"
description: "Use when implementing or reviewing authorization checks in app/Controllers."
applyTo: "app/Controllers/**"
---

## Purpose

Ensure controller actions in this app obey role-based and permission-based access controls before executing business logic.

## Guidelines

- Check `session()->get('isLoggedIn')` or equivalent method from `app/Controllers/Auth/AuthFilter.php` at controller entry.
- Use `AdminBaseController::checkPermission()` or similar direct helper in `app/Controllers/AdminBaseController.php`.
- Validate request payload with `validate()` and `app/Config/Validation.php` rules.
- Avoid inline SQL; use models and query builder.

## Checklist for each controller method

- [ ] Does the method require authenticated user?
- [ ] Does it check `userCan('module.action')` or equivalent permission/role before mutation?
- [ ] Does it sanitize input via validation rules and xss_clean (if needed)?
- [ ] Does it handle unauthorized access with 403 or redirect to login?
- [ ] Does it use `csrf_token()` where appropriate for forms?
