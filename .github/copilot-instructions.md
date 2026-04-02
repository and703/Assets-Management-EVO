---
name: "Assets Management EVO Workspace Instructions"
description: "Project-level guidance for Copilot Chat and custom agents: CodeIgniter4 app with PHP/Unit tests, DB migration patterns, and frontend backend boundaries."
applyTo: "**"
---

## Overview

This repo is a CodeIgniter 4 application (AdminPRO fork) with:
- `app/` for application controllers, models, views, services, and libraries.
- `public/` as the webroot (index.php is here).
- `main/` and `starter/` contain app distribution snapshots.
- `tests/` for PHPUnit tests, and `vendor/` for dependencies.

Use this workspace instruction file as authoritative context for all Copilot Chat responses.

## Key Commands

- `composer install`
- `npm` is not needed (no JS build config in repo root)
- `phpunit` or `composer test`
- database setup via `.env` (copy `env` to `.env`), then run app-specific migrations if any

## Code Patterns

- MVC: `app/Controllers`, `app/Models`, `app/Views`
- `app/Config` for CI4 config classes
- `app/Libraries` custom reusable classes
- `app/Services` and `app/Helpers` for business utilities
- `app/Database/Migrations` for schema updates, seeders in `app/Database/Seeds`

## What to Prioritize

1. Preserve CI4 lifecycle (Controller -> Model -> View, Services, Filters).
2. Keep existing route/permission procedures and method naming.
3. Use existing validation and security helpers in `app/Config/Validation.php` and `app/Common.php`.
4. Avoid changing `public/` path expectations, keep webserver root on `public`.

## Suggested Conventions

- Code style: follow existing formatting in repository (PSR-12-like, low magic, explicit SQL with query builder)
- Localization: `app/Language/en` for strings.
- Access control: inspect `app/Controllers/Auth` and `app/Controllers/AdminBaseController`.

## Helpful Links

- Root README: `README.md`
- CodeIgniter docs: https://codeigniter.com/user_guide/
- CI4 upgrade docs: check `composer.json` framework version and vendor changelog.

## Agent “Intent Triggers”

This instruction is useful when user asks:
- "How do I run tests in this repo?"
- "Where is the public entrypoint?"
- "Use CodeIgniter 4 conventions" or "backend model/controller".
- "Create migration/seed for Assets Management EVO"

---

## Example prompts

- "Generate a new Controller and Model for `AssetAudit` with CRUD routes in CodeIgniter 4, based on existing `AssetController`."
- "Write unit tests for `app/Services/AssetService` using PHPUnit in this project context."
- "Refactor `app/Controllers/Dashboard.php` to use dependency injection and avoid static method calls."

## Next agent-customization ideas

- `/create-instruction security-rules`: add scoped `app/Controllers/**` instructions for auth/permission pattern, with `applyTo` on controller files.
- `/create-prompt migration-helper`: one-shot prompt for generating CI4 migrations and seeders from table schema.
- `/create-agent test-runner`: custom agent that runs `composer test`, collects failing tests, and suggests first fix.
