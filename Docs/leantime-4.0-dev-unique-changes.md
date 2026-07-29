# Leantime Codebase Changes: v3.6.2 → 4.0-dev

## Forward-Porting Reference Document

**Scope:** 752 commits | 929 files changed | 5 contributors
**Comparison:** `v3.6.2...4.0-dev` (everything on 4.0-dev NOT yet in v3.6.2)
**Purpose:** Catalog of YOUR component development work that needs to be forward-ported onto the current v3.6.2 codebase
**Generated:** February 12, 2026

---

## Context: What IS the 4.0-dev Branch?

Per the [Leantime roadmap discussion (#2696)](https://github.com/Leantime/leantime/discussions/2696), the 4.0-dev branch is the next major version with four pillars:

1. **🧱 Component System** — DaisyUI as base theme, Blade components for all HTML elements
2. **⚡ HTMX Everywhere** — Server-side rendering with HTMX replacing custom JS
3. **📦 JS Module Conversion** — All custom JS converted to ES modules, webpack improvements
4. **🏗️ Deeper Laravel Core Migration** — Further replacement of custom classes with Laravel equivalents

The branch diverged from mainline around early-mid 2024 (pre-v3.2.0). Since then, mainline has received **20+ releases** (v3.2.0 → v3.6.2) with their own significant changes, which is why this forward-port is necessary.

---

## ⚠️ CRITICAL: What v3.6.2 Changed UNDER You

While 4.0-dev was being developed, mainline made these **breaking changes** that will cause merge conflicts:

### Session Management (v3.2.0)
- All `$_SESSION` access replaced with Laravel session facade across the ENTIRE codebase
- **Impact on 4.0-dev:** Any 4.0-dev file that still uses `$_SESSION` will need updating

### File Management System (v3.5.4)
- `app/Core/Files/Fileupload.php` was **DELETED** (429 lines)
- Replaced by new `app/Core/Files/FileManager.php` (356 lines) using Laravel file storage
- New `FileManagerInterface`, `FileValidationException`, `filesystems.php` config
- **Impact on 4.0-dev:** If your branch modified or depends on `Fileupload.php`, those changes are lost

### Canvas Domain (v3.4.12)
- All Canvas controllers massively refactored
- Canvas repository (~1,215 lines changed), service (~456 lines changed)
- **Impact on 4.0-dev:** If you componentized Canvas views, the underlying controllers/services changed

### String Utilities (v3.5.7)
- `StrMacros.php` deleted, replaced with 5 individual classes in `Core/Support/String/`
- **Impact on 4.0-dev:** Any imports of StrMacros need updating

### Event System
- `DispatchesEvents` trait significantly refactored (v3.4.12)
- `EventDispatcher` enhanced with new methods (v3.5.6–v3.5.7)
- `dispatchFilter` now returns values instead of echoing
- **Impact on 4.0-dev:** Event dispatch/filter calls may behave differently

### Plugin System (v3.4.11–v3.5.8)
- New `RouteLoader.php` for plugin/module route files
- PHAR file support for plugins
- Plugin marketplace integration
- ConsoleKernel expanded for plugin command loading
- **Impact on 4.0-dev:** Plugin hooks and loading behavior changed

### New Files Added in v3.6.2 (Not in 4.0-dev)
```
app/Core/Files/FileManager.php (NEW — replaces Fileupload.php)
app/Core/Files/Contracts/FileManagerInterface.php
app/Core/Files/Exceptions/FileValidationException.php
app/Core/Routing/RouteLoader.php
app/Core/Plugins/Plugins.php
app/Core/Support/String/AlphaNumeric.php
app/Core/Support/String/BeautifyFilename.php
app/Core/Support/String/SanitizeFilename.php
app/Core/Support/String/SanitizeForLLM.php
app/Core/Support/String/ToMarkdown.php
app/Core/Support/Attributes/AITool.php
app/Command/CleanupOrphanedFilesCommand.php
app/Domain/Files/Events/FileUploaded.php
app/Domain/Help/Controllers/Support.php
app/Domain/Plugins/Hxcontrollers/Marketplaceplugins.php
app/Domain/Widgets/Templates/partials/myToDosLoadMore.blade.php
config/filesystems.php
CLAUDE.md
.claude/commands/conversation-analysis-system.md
.junie/guidelines.md
```

### Files Deleted in v3.6.2 (May still exist in 4.0-dev)
```
app/Core/Files/Fileupload.php (DELETED — replaced by FileManager)
app/Core/Support/StrMacros.php (DELETED — replaced by individual String classes)
public/download.php (DELETED)
public/js/libs/tinymce-plugins/llamadorian/plugin.js (RENAMED to aiTools)
```

---

## 🧱 4.0-DEV PILLAR 1: COMPONENT SYSTEM (DaisyUI + Blade Components)

This is the **largest body of work** on 4.0-dev — the complete UI componentization.

### What It Includes
- **DaisyUI integration** as the base CSS component framework (replacing custom CSS)
- **Blade components** for all standard HTML elements (buttons, forms, cards, modals, etc.)
- New `app/Views/Components/` directory tree with reusable Blade components
- Component-based templates replacing inline HTML across all domain views
- Consistent theming through DaisyUI theme variables
- Dramatically reduced CSS/JS footprint through component reuse

### Expected File Impact (929 files, ~80% estimated to be component work)
- **New directory:** `app/Views/Components/` — entire component library
- **Modified:** Every `.blade.php` template across all domains refactored to use components
- **Modified:** `webpack.mix.js` / build pipeline for DaisyUI/Tailwind
- **Modified:** `package.json` — DaisyUI, Tailwind CSS dependencies added
- **Modified:** CSS files — massive reduction as styles move to components
- **New/Modified:** Tailwind config (`tailwind.config.js`)
- **Modified:** Layout templates (`app.blade.php`, `header.blade.php`, etc.)

### High-Conflict Zones
These template/view files were ALSO modified in v3.6.2 and will have conflicts:

| Area | v3.6.2 Changes | 4.0-dev Changes | Conflict Risk |
|------|---------------|-----------------|---------------|
| Widget templates (MyToDos) | Rewritten with infinite scroll | Componentized with DaisyUI | 🔴 HIGH |
| Ticket/Kanban templates | Preloading, load more, drag-drop | Componentized | 🔴 HIGH |
| Layout templates | Premium links removed, header/footer updated | Componentized | 🟡 MEDIUM |
| Canvas templates | Controllers refactored, views updated | Componentized | 🟡 MEDIUM |
| Menu templates | Plugin marketplace links, composer changes | Componentized | 🟡 MEDIUM |
| Project selector | CSS fixes, hxcontroller updates | Componentized | 🟡 MEDIUM |
| File management views | New FileManager UI | Componentized | 🔴 HIGH |
| Auth/Login templates | Updated login info | Componentized | 🟢 LOW |

---

## ⚡ 4.0-DEV PILLAR 2: HTMX EXPANSION

### What It Includes
- Extended HTMX usage across pages that previously used traditional form submissions
- New `hx-*` attributes on componentized templates
- Server-side rendered partials replacing JavaScript-rendered content
- HTMX-based navigation and partial page updates
- New Hxcontroller classes for additional domains

### Expected File Impact
- **Modified:** Hxcontrollers across all domains
- **New:** Additional Hxcontroller classes for domains that didn't have them
- **Modified:** All `.blade.php` templates with HTMX attributes
- **Modified:** Route definitions for HTMX partial endpoints

### Conflict Zones
- v3.6.2 added its own HTMX improvements (MyToDos infinite scroll, load more)
- v3.6.2 enhanced HTMX event management (v3.4.12)
- The approach may differ between branches

---

## 📦 4.0-DEV PILLAR 3: JS MODULE CONVERSION

### What It Includes
- Custom JavaScript files converted from IIFE/globals to ES modules
- `import`/`export` syntax throughout JS codebase
- Webpack configuration updates for module bundling
- Removal of global scope pollution
- Better tree-shaking and code splitting

### Expected File Impact
- **Modified:** All files in `public/js/` and custom JS controllers
- **Modified:** `webpack.mix.js` — significant build pipeline changes
- **Modified:** `package.json` — new build dependencies, scripts
- **Modified:** JS controllers referenced in templates

### Conflict Zones
| File/Area | v3.6.2 Change | 4.0-dev Change |
|-----------|--------------|----------------|
| TinyMCE plugins | Renamed `llamadorian` → `aiTools` (v3.5.6) | Likely modularized |
| Ticket JS controller | Updated (v3.5.6) | Modularized |
| Confetti management | Updated | Modularized |
| Webpack config | Updated (v3.5.6) | Significantly rewritten |

---

## 🏗️ 4.0-DEV PILLAR 4: DEEPER LARAVEL MIGRATION

### What It Includes
- Continued replacement of custom Leantime classes with Laravel equivalents
- Extended service container usage
- More Laravel middleware adoption
- Deeper Eloquent integration where applicable
- Laravel-style configuration patterns

### Expected File Impact
- **Modified:** `app/Core/` classes — Application, Bootloader, Bootstrap files
- **Modified:** Service providers
- **Modified:** Configuration files
- **Modified:** Middleware stack

### Conflict Zones
v3.6.2 also continued Laravel migration, creating parallel evolution:

| Core Area | v3.6.2 State | 4.0-dev State |
|-----------|-------------|---------------|
| Session management | Fully Laravel (v3.2.0) | May have own implementation |
| File storage | Laravel FileManager (v3.5.4) | May use Fileupload.php still |
| Database | Laravel improvements (v3.5.3) | Own DB layer changes |
| Configuration | `laravelConfig.php` expanded | Own config changes |
| HTTP Kernel | Updated (v3.5.7) | Own kernel changes |
| Console Kernel | PHAR support (v3.5.8) | Own CLI changes |
| Event Dispatcher | Enhanced (v3.5.6-7) | Own event changes |

---

## 📋 RECOMMENDED FORWARD-PORT STRATEGY

### Phase 1: Assess Actual Divergence
Run locally to get the real picture:
```bash
# See what 4.0-dev changed
git diff v3.6.2...4.0-dev --stat

# See conflicts specifically in templates
git diff v3.6.2...4.0-dev --stat -- '*.blade.php'

# See conflicts in Core
git diff v3.6.2...4.0-dev --stat -- 'app/Core/'

# See new files only on 4.0-dev
git diff v3.6.2...4.0-dev --diff-filter=A --name-only

# See files modified on BOTH branches (highest conflict risk)
comm -12 \
  <(git diff $(git merge-base v3.6.2 4.0-dev)...v3.6.2 --name-only | sort) \
  <(git diff $(git merge-base v3.6.2 4.0-dev)...4.0-dev --name-only | sort)
```

### Phase 2: Rebase or Cherry-Pick

**Option A: Rebase 4.0-dev onto v3.6.2** (cleanest but most work)
```bash
git checkout 4.0-dev
git rebase v3.6.2
# Resolve ~hundreds of conflicts
```

**Option B: Create fresh branch, cherry-pick component work** (recommended)
```bash
git checkout -b 4.0-dev-rebased v3.6.2

# Cherry-pick ONLY the component/UI commits (skip infrastructure that v3.6.2 already has)
git log v3.6.2...4.0-dev --oneline | grep -i "component\|daisyui\|htmx\|module"
# Cherry-pick those commits
```

**Option C: Manual merge with strategic conflict resolution**
```bash
git checkout -b 4.0-dev-merged v3.6.2
git merge 4.0-dev --no-commit
# Resolve conflicts file by file, favoring v3.6.2 for Core/ and 4.0-dev for Views/Components/
```

### Phase 3: Conflict Resolution Priority

| Priority | Area | Strategy |
|----------|------|----------|
| 1 | `app/Core/` | **Favor v3.6.2** — it has newer infrastructure (FileManager, session, events) |
| 2 | `app/Views/Components/` | **Favor 4.0-dev** — this is YOUR new work |
| 3 | `*.blade.php` templates | **Manual merge** — take 4.0-dev components but apply v3.6.2 logic changes |
| 4 | JS/Webpack | **Favor 4.0-dev** for module structure, add back v3.6.2 aiTools rename |
| 5 | CSS/Tailwind | **Favor 4.0-dev** for DaisyUI, verify v3.6.2 CSS removals still apply |
| 6 | `package.json`/`composer.json` | **Manual merge** — combine dependencies from both |
| 7 | Domain Services/Repos | **Favor v3.6.2** for bug fixes, add 4.0-dev changes on top |
| 8 | Tests | **Favor v3.6.2** for new tests (FileManagerTest), add 4.0-dev component tests |

### Phase 4: Validation Checklist
After merging:
- [ ] `composer install` succeeds
- [ ] `npm install && npm run dev` succeeds
- [ ] DaisyUI components render correctly
- [ ] HTMX interactions work
- [ ] File upload/management works (uses new FileManager, not old Fileupload)
- [ ] Session management works (Laravel sessions)
- [ ] Plugin system works (route loader, PHAR support)
- [ ] API authentication works (non-jsonrpc paths)
- [ ] Canvas boards work (refactored controllers)
- [ ] Widget dashboard works (MyToDos infinite scroll + components)
- [ ] All existing unit tests pass
- [ ] Acceptance tests pass

---

## 🔴 TOP 10 HIGHEST-RISK COLLISION ZONES

Ranked by likelihood of painful merge conflicts:

1. **Widget/Dashboard Templates** — v3.6.2 rewrote MyToDos with infinite scroll; 4.0-dev componentized it
2. **File Management** — v3.6.2 replaced entire system; 4.0-dev may still reference old one
3. **Webpack/Build Pipeline** — both branches modified significantly
4. **Layout Templates** — both branches made structural changes
5. **Ticket/Kanban Views** — v3.6.2 added features; 4.0-dev componentized
6. **Core Infrastructure** — parallel Laravel migration work on both branches
7. **Event System** — both branches modified dispatch behavior
8. **CSS/Styles** — v3.6.2 removed transitions, dark theme fixes; 4.0-dev replaced with DaisyUI
9. **Plugin System** — v3.6.2 added routing, PHAR, marketplace; 4.0-dev may have own plugin hooks
10. **package.json / composer.json** — dependency trees diverged significantly

---

## 📊 SCALE SUMMARY

| Metric | Value |
|--------|-------|
| Commits on 4.0-dev not in v3.6.2 | **752** |
| Files changed | **929** |
| Contributors | **5** |
| Estimated component/UI files | ~700+ (75%+) |
| Estimated infrastructure files | ~150+ |
| Estimated config/build files | ~50+ |
| Releases between branch point and v3.6.2 | **20+** (v3.2.0-beta → v3.6.2) |

---

*This document was compiled from the GitHub comparison page (v3.6.2...4.0-dev), the Leantime 4.0 roadmap discussion (#2696), release notes for v3.2.0 through v3.6.2, and commit log analysis. For exact file-by-file diffs, run `git diff v3.6.2...4.0-dev` locally as GitHub cannot render this comparison (too large).*
