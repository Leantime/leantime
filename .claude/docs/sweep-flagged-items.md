# Design Token Sweep — Flagged Items

Items that need design decisions or refactoring beyond simple value swaps.
Collected across all sweeps for batch resolution.

---

## Sweep 1: Hardcoded Brand Colors

### Needs Refactor
- **`app/Plugins/StrategyPro/register.php:354`** — `'color' => '#1B75BB'` for `status_draft`. Used in inline styles with hex alpha suffix trick (`#1B75BB20`, `#1B75BB40`) on line 363. Can't use `var(--accent1)` because CSS variables don't support appending hex alpha digits. Needs refactor to `color-mix()` or separate opacity properties.
- **`app/Plugins/StrategyPro/Js/logicmodelPluginController.js:581`** — `'#1B75BB': [27, 117, 187]` in hex-to-RGB lookup table for PDF export. Needs runtime `getComputedStyle()` to read `--accent1` instead of a static lookup key.

---

## Sweep 2: Hardcoded Neutral Colors

### Needs Design Decision
- **`app/Domain/Wiki/Templates/templates.blade.php:194`** — `background-color: #ffffff` on HTML table. This is wiki template content (user-facing, not UI chrome). Changing it could affect how wiki pages render for users.
- **`app/Plugins/Billing/Templates/subscriptions.blade.php:46`** — `background: white; color: #667eea;` on "Start Free Trial" button. The `#667eea` (purple) is not in the palette. Intentional brand override for CTA.

### Semantic Colors (Billing plugin — Material Design palette)
These status/feedback colors should map to `var(--color-success-bg)`, `var(--color-info-bg)`, `var(--color-warning-bg)`, `var(--color-error-bg)` but the exact hex values don't match the design token doc 1:1. Needs alignment on whether to use the doc values or keep current Material Design values.

| File | Lines | Colors | Semantic |
|---|---|---|---|
| `Billing/partials/creditConfirmation.blade.php` | 13, 51, 73 | `#e8f5e9`/`#4caf50`, `#fff3e0`/`#ff9800`, `#e3f2fd`/`#2196f3` | success, warning, info |
| `Billing/partials/coreConfirmation.blade.php` | 36, 68 | `#e8f5e9`/`#4caf50`, `#fff3e0`/`#ff9800` or `#e3f2fd`/`#2196f3` | success, warning/info |
| `Billing/partials/subscriptionStatus.blade.php` | 40 | `#e8f5e9`/`#4caf50` | success |
| `Billing/partials/coreSubscription.blade.php` | 59 | `#e3f2fd` | info |
| `Billing/partials/creditAddons.blade.php` | 54 | `#fff3cd` | warning |
| `Billing/addCreditsModal.blade.php` | 19, 115, 175, 189, 201, 316, 321 | `#e8f5e9`/`#4caf50`, `#fff3cd`/`#f57c00`, `#fffef7` | success, warning |
| `Billing/changePlanModal.blade.php` | 117, 321 | `#e3f2fd`, `#f0f8ff` | info |
| `Billing/subscriptions.blade.php` | 283 | `#ffebee`/`#f44336` | error/danger |
| `Billing/confirm.blade.php` | — | (check for semantic colors) | — |
| `Copilot/partials/taskPriorityActions.blade.php` | 112, 116 | `#ef4444`, `#f59e0b` | error, warning |
| `GoogleCalendar/accountSettings.blade.php` | 14 | `#fee2e2`/`#ef4444`/`#991b1b` | error |
| `PgmPro/resources/partials/header.blade.php` | 35, 42 | `#FEF3C7`/`#FDE68A`/`#92400E` | warning |
| `Help/Templates/support.blade.php` | 102, 110 | `#EBF9FF`, `#FBFDED` | info-tint, success-tint |
| `StrategyPro/partials/logicmodel/healthBadgePopover.blade.php` | 266 | JS object with `#FEF4E4`/`#fdab3d` | warning |

---

## Sweep 3: Dark Mode Verification

### Shadows — Special Purpose (don't map cleanly to tokens)
- **`app/Views/Templates/layouts/registration.blade.php:29`** — `box-shadow: 0px 0px 50px rgba(0,0,0,0.4);` — 50px blur, much larger than any token (`--shadow-xl` is 48px but 14px offset). Decorative registration card shadow.
- **`app/Plugins/Pomodoro/Templates/partials/pomodoro.blade.php:572`** — `box-shadow: 0 0 10px 5px rgba(0,0,0, 0.2);` — Spread shadow on timer card. Non-standard shape.
- **`app/Plugins/Billing/Templates/addCreditsModal.blade.php:317,323`** — `box-shadow: 0 4px 12px rgba(245, 124, 0, 0.2/0.3);` — Orange-tinted shadow for active credit selection. Semantic color in shadow.

### Focus Rings — Needs Pattern Decision
- **`app/Plugins/Reactions/assets/css/sentiment.css:45,113`** — `box-shadow: 0 0 0 2px rgba(0,123,255,.25);` — Single blue ring. Design token doc defines `--focus-ring` as a double-ring pattern (`0 0 0 2px bg-card, 0 0 0 4px accent1`). Needs decision on whether to adopt the double-ring pattern.

### Off-Palette Colors — Needs Design Decision
- **`app/Domain/Tickets/Templates/componentTest.blade.php:212,268,319,364`** — `color: #6B7A4D;` — Olive green for section headings. Not in palette.
- **`app/Domain/Users/Templates/editOwn.blade.php:290`** — `color:#f5a623;` — Amber for light-mode icon. Close to `--color-warning` (#D97706) but different value.
- **`app/Domain/Users/Templates/editOwn.blade.php:296`** — `color:#c4cfe0;` — Muted blue for dark-mode icon. Not in palette.
- **`app/Plugins/Billing/Templates/partials/coreSubscription.blade.php:60`** — `color: #1976d2;` — Blue for "Includes AI Credits". Close to `--accent1` but different value.
- **`app/Plugins/Billing/Templates/partials/creditAddons.blade.php:55`** — `color: #856404;` — Dark amber text. Not in palette.
- **`app/Plugins/Billing/Templates/partials/creditConfirmation.blade.php:25,34`** — `color: #2e7d32;` — Dark green success text. Doc says `#059669`.
- **`app/Domain/Help/Templates/support.blade.php:98,106`** — `background:#D6F3FF`, `background:#FEEBF3` — Tinted backgrounds (blue, pink). Not in standard token set.

### Wiki Templates — Content Colors (exclude from automated fixes)
- **`app/Domain/Wiki/Templates/templates.blade.php`** — Lines 194-354. Extensive hardcoded colors (`#3598db`, `#ffffff`, etc.) in HTML table templates that users copy for wiki pages. These are content templates, not UI chrome. Changing them alters user-facing wiki content.
