# AGENTS.md

## Start Here
- `Makefile` is the source of truth for local build, test, and CI commands. `package.json` defines dependencies only; there are no npm scripts.
- `CLAUDE.md` is the only repo-local architecture guide with broad coverage. Use it for domain conventions after checking executable config.

## Environment And Setup
- The app and webpack both load env from `config/.env`. The sample file is `config/sample.env`, not `.env.sample`.
- Do not add Laravel config under `config/*.php`. The custom bootstrap loads Laravel config from `app/Core/Configuration/laravelConfig.php` via `app/Core/Bootstrap/LoadConfig.php`.
- `app/Plugins` is a git submodule (`.gitmodules`) pointing at a private repo, so OSS checkouts may have an empty plugins tree.

## Build Commands
- `make build-dev`: installs npm + Composer deps, clears bootstrap/storage caches, runs `npx mix`, then `node generateBlocklist.mjs`.
- `make build`: same flow for production, but with `npx mix --production` and `composer install --no-dev --optimize-autoloader`.
- If you change JS, LESS, Tailwind usage, or assets loaded from `public/dist`, run `make build-dev`. The build also regenerates `blocklist.json` from the compiled CSS.

## Test And Verification
- CI style check: `make build-dev` then `make test-code-style`.
- CI static analysis: `make build-dev` then `make phpstan`.
- `make phpstan` uses `.phpstan/phpstan.neon` (level 0), not the root `phpstan.neon`.
- `make unit-test` and `make acceptance-test` both rebuild the Docker test stack before running Codeception.
- Acceptance tests use `.dev/docker-compose.tests.yaml`, mount `.dev/test.env` to `config/.env`, hit `https://leantime-dev`, and reset the `leantime_test` DB with `populate: true` and `cleanup: true`.
- Focused acceptance runs are done inside the test container, e.g. `docker compose --file .dev/docker-compose.yaml --file .dev/docker-compose.tests.yaml exec leantime-dev php vendor/bin/codecept run -g api --steps`.

## Runtime Wiring
- HTTP entrypoint is `public/index.php`, which loads `app/helpers.php`, boots `bootstrap/app.php`, then hands off to `Leantime\Core\Bootloader`.
- Routing is hybrid. Explicit Laravel routes come from per-domain `routes.php`, but most URLs still fall through `Leantime\Core\Controller\Frontcontroller`.
- Frontcontroller URL shape is `/module/action[/id][/method]`; `/hx/...` switches resolution to `Hxcontrollers`.
- MCP automation lives at `POST /mcp`; operational setup, token commands, presets, and tool examples are documented in `docs/mcp.md`.

## Controller And UI Conventions
- Controllers and HTMX controllers get extra DI through an `init()` method. The base `Controller` and `HtmxController` call `app()->call([$this, 'init'])` automatically.
- HTMX controllers must declare `protected static string $view` and return fragments through `Template::displayFragment()`.
- Full-page templates are still often legacy `.tpl.php`; HTMX fragments are typically Blade partials.
- All domain JS under `app/Domain/**/*.js` is concatenated into `public/dist/js/compiled-app.<version>.min.js`, which is loaded globally from `app/Views/Templates/sections/header.blade.php`. A new domain JS file affects every page load.
- Tailwind is available but prefixed: use `tw-` classes (`tailwind.config.js`).

## Repo-Specific Gotchas
- `make run-dev` binds the app to `http://localhost:5080`; the test compose file uses `http://localhost:8002` / `https://localhost:44302` instead.
- Release packaging strips `app/Plugins/*`, `userfiles/*`, and uncompiled JS from the package (`make package`). Do not treat packaged output as a faithful dev tree.
