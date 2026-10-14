# End-to-end (e2e) tests for web

Browser end-to-end tests for Leantime, authored with [KaneCLI](https://www.testmuai.com/kane-cli/) by TestMu AI. Each test is a plain-English `_test.md` file; KaneCLI runs it in a real Chrome browser and records each step so later runs replay without re-authoring.

| Test | What it covers |
|---|---|
| `idea_board_test.md` | Opening the Idea Board and adding an idea through the idea dialog |
| `new_project_test.md` | Creating a project from the New Project form and landing on it |

They cover flows the Codeception suite in `tests/Acceptance` doesn't exercise yet.

## Run the tests

The tests expect the dev stack at `http://localhost:5080`, installed with the same admin the acceptance tests use (`test@leantime.io` / `Test123456!`):

```bash
make run-dev
```

Then open `http://localhost:5080/install`, install with `test@leantime.io`, and set the password to `Test123456!` in the onboarding steps. Log in once and answer the "What is one task that's on your mind right now?" prompt so it doesn't come back on later runs.

### With KaneCLI

```bash
npm install -g @testmuai/kane-cli
kane-cli login
kane-cli testmd run e2e/kane/idea_board_test.md --agent
kane-cli testmd run e2e/kane/new_project_test.md --agent
```

### With the exported Playwright code

Each test also has an exported Playwright (JavaScript) version under `output-<test>/playwright-javascript-code/`:

```bash
cd e2e/kane/output-idea_board/playwright-javascript-code
npm install
npx playwright install chromium
node test.js
```

Copy `.env.example` to `.env` and fill in your TestMu AI (LambdaTest) credentials first.

## Editing a test

Edit the step text in the `_test.md` file and run it again with `kane-cli testmd run`. Changed steps (and the steps after them) are re-recorded; unchanged steps replay from `output-<test>/`.
