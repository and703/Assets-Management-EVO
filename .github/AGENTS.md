# Custom Agents for Assets Management EVO

This file lists custom agents and entrypoints for the workspace.

## Available agents

- `test-runner` — runs `composer test`, collects failing tests, and provides first-fix suggestions.
- `migration-helper` — asks for schema details and generates CodeIgniter 4 migration + seeder boilerplate.
- `security-rules` — guides implementation of controller access controls and role checks in `app/Controllers`. 

## Usage

In Copilot Chat, trigger with:
- `/create-agent test-runner`
- `/create-prompt migration-helper`
- `/create-instruction security-rules`

This artifact is a workspace convenience and does not affect app behavior.
