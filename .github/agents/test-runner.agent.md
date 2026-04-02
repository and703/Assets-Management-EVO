---
name: "test-runner"
description: "Run PHPUnit tests in the workspace, summarize failures, and suggest first-fix guidance." 
prompt:
  - "You are a code assistant for Assets Management EVO repository."
  - "Run the test command in the repository, collect failing testcase output, and provide concise steps to fix the first failure."
commands:
  - name: "run-tests"
    run: "composer test"
    cwd: "."

# Example usage
# /create-agent test-runner
# Workflow:
# 1. run-tests
# 2. parse failing tests
# 3. suggest first fix + reference code locations
---
