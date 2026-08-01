# Leantime Remediation Sweeps

Each sweep is a self-contained unit of work. Every sweep follows the pattern: **discover → implement → replace → verify**. Sweeps are ordered by dependency — later sweeps assume earlier ones are complete.

---

## Sweep 01: Component Audit & Duplicate Map

**Outcome:** A machine-readable manifest of every file in `app/Views/Templates/components/` with duplicates flagged, so all subsequent sweeps know exactly which files to delete and which to keep.

1. **Scan the components directory.** Recursively list every `.blade.php` file under `app/Views/Templates/components/`. For each file, extract: filename, subdirectory path, the component tag name (from the folder/file convention), and a hash of its content.
2. **Identify duplicates by function.** Group files that serve the same purpose (e.g., all files containing "dropdown" in name or rendering `<select>`, `<ul>` menus, or DaisyUI `dropdown` classes). Produce a grouped list showing: canonical name from the component tracker (e.g., `globals::actions.dropdown-menu`), all files that implement a version of it, and which file — if any — is closest to the tracker spec.
3. **Output a deprecation manifest.** Write a JSON file `component-audit.json` with the structure: `{ "canonical_name": "globals::actions.dropdown-menu", "keep": "path/to/best/version.blade.php", "delete": ["path/to/dupe1.blade.php", "path/to/dupe2.blade.php"], "references_count": 14 }` for each group. Include a `references_count` by grepping the codebase for usages of each file.
4. **Verify:** The manifest must account for every `.blade.php` in the components directory. No file should be unclassified. Run `find app/Views/Templates/components -name "*.blade.php" | wc -l` and confirm it matches the sum of all entries in the manifest.

---

## Sweep 02: Legacy File Cleanup

**Outcome:** All `.tpl` files that have a `.blade.php` equivalent are deleted. All test files (e.g., `componentTest`) are removed from template directories. Zero legacy template files remain in active template paths.

1. **Find all `.tpl` files.** Run `find` across the entire project for `*.tpl` files. For each one, check if a `.blade.php` file with the same base name exists in the same directory or the equivalent Blade directory.
2. **Delete matched `.tpl` files.** For every `.tpl` that has a Blade equivalent, delete the `.tpl` file. Log each deletion with the path pair (deleted `.tpl` → existing `.blade.php`).
3. **Remove test files from template directories.** Search all directories under `app/Views/Templates/` for files matching `*test*`, `*Test*`, `componentTest*`, or similar patterns. Delete them. These belong in a `tests/` directory, not in template paths.
4. **Verify:** `find app/Views/Templates -name "*.tpl" | wc -l` returns 0 (or only returns `.tpl` files with no Blade equivalent, which should be listed for manual review). `find app/Views/Templates -iname "*test*" | wc -l` returns 0.

---

## Sweep 03: Event Folder Rename (htmx → events)

**Outcome:** Every domain that has an `htmx/` folder now has an `events/` folder instead. All internal references (imports, autoloaders, namespace declarations) are updated. The application boots and routes resolve without errors.

1. **Find all `htmx` folders across domains.** Search the domain directories (likely under `app/Domain/*/` or similar) for folders named `htmx`. List every occurrence.
2. **Rename each `htmx/` to `events/`.** Use `git mv` (or `mv` + git add) to rename. This preserves git history.
3. **Update all references.** Grep the entire codebase for the old path segments (e.g., `Htmx\\`, `htmx/`, `htmx.`) and replace with the `events` equivalent. This includes PHP namespace declarations, `use` statements, autoloader configs, and any Blade `@include` or `@component` references.
4. **Verify:** Run the application's route cache clear (`php artisan route:clear && php artisan route:cache`) and confirm no errors. Run `grep -r "htmx" app/Domain/ --include="*.php" | grep -v "hx-"` to confirm no stale references remain (excluding legitimate `hx-` HTMX attributes in Blade templates).

---

## Sweep 04: Icon Library Consolidation

**Outcome:** The project uses only Material Icons via Blade UI. All Glyphicon and FontAwesome class references are replaced. A single icon rendering approach exists across the entire codebase.

1. **Audit current icon usage.** Grep for `glyphicon`, `fa-`, `fa `, `fas `, `far `, `fab `, `fontawesome` across all `.blade.php`, `.php`, `.js`, and `.css` files. Produce a list of every icon reference with file path and line number.
2. **Build a mapping table.** For each unique icon found (e.g., `fa-trash`, `glyphicon-plus`), map it to the equivalent Material Icon name (e.g., `delete`, `add`). Use the Material Icons reference at fonts.google.com/icons.
3. **Replace all icon references.** For each file, replace the old icon markup (e.g., `<i class="fa fa-trash"></i>` or `<span class="glyphicon glyphicon-plus"></span>`) with the Blade UI Material Icon syntax used in 4.0-beta (e.g., `<x-icon name="delete" />` or the project's established convention). Do this file-by-file, not with a single global regex, to handle edge cases in markup structure.
4. **Remove old icon library imports.** Find and delete any `<link>` or `@import` references to FontAwesome or Glyphicon CSS files in layout files, `<head>` partials, or asset build configs (webpack, vite, etc.).
5. **Verify:** `grep -r "glyphicon\|fa-\|fontawesome" app/Views/ --include="*.blade.php" | wc -l` returns 0. Load 5 representative pages (ticket list, ticket detail modal, project dashboard, goal screen, settings) and confirm all icons render visually.

---

## Sweep 05: form-field Component (Label + Input Wrapper)

**Outcome:** A single `<x-globals::forms.form-field>` Blade component exists that wraps any input with a label, caption, validation message, and consistent spacing. It accepts `label-text`, `label-position` (top/left), `caption`, `validation-text`, `validation-state`, `leading-visual`, `trailing-visual` props.

1. **Create the component.** Build `globals::forms.form-field` as a Blade component. It renders a wrapper `<div>` with the label above or beside the input (controlled by `label-position`, defaulting to `top`). The `{{ $slot }}` receives the actual input element. Below the input: optional `caption` text in muted small type, and optional `validation-text` styled by `validation-state` (error = red, warning = amber, success = green). Spacing between label, input, and validation must be fixed values — not page-dependent.
2. **Reference the 4.0-beta implementation.** Check the 4.0-beta branch for the existing form-field component. Port its spacing values (margin/padding between label and input, between input and validation text) directly. If 4.0-beta used specific DaisyUI form-control classes, use the same ones.
3. **Apply to the ticket detail view as proof.** Replace the form markup in the ticket detail sidebar (where labels are on top and inputs below) to use `<x-globals::forms.form-field label-text="Status">` wrapping the existing status select. Do the same for 3–5 other fields on that view (milestone, author, due date, priority).
4. **Apply to the milestone modal.** The milestone modal currently has fields stacked with zero spacing between them. Wrap each field in `<x-globals::forms.form-field>`. This should fix the "fields right on top of each other" issue.
5. **Verify:** Open the ticket detail view — labels and inputs should have identical spacing for every field. Open the milestone modal — fields should have consistent vertical rhythm (no cramming). Compare against the timesheet tab — if timesheet forms also use `form-field`, they should match. If not, note for a later sweep.

---

## Sweep 06: Unified dropdown-menu Component

**Outcome:** A single `<x-globals::actions.dropdown-menu>` component replaces all 4+ dropdown implementations. It handles action menus, filter menus, sprint selectors, and "new" button dropdowns with consistent item spacing, icon rendering, and background opacity.

1. **Create the component.** Build `globals::actions.dropdown-menu` with props: `trigger` (slot for the button/link that opens it), `position` (left/right/bottom, defaulting to bottom-left), `width` (auto/fixed), `background` (solid white by default — not transparent). Child `<x-globals::actions.dropdown-item>` accepts: `href`, `leading-visual` (icon name), `trailing-visual`, and renders each item with consistent padding (e.g., `px-4 py-2`), proper icon sizing, and text that never shows raw HTML.
2. **Replace the ticket page "New" button dropdown.** This is the dropdown that currently shows `<span...` raw HTML for icons. Replace its markup with `<x-globals::actions.dropdown-menu>` and `<x-globals::actions.dropdown-item leading-visual="add">` for each option. Icons should render as actual icons, not HTML strings.
3. **Replace the sprint selector dropdown.** This dropdown has different spacing than all others. Replace with the same component. Verify item spacing matches the "New" button dropdown exactly.
4. **Replace the 3-dot page header menu** (the one that opens off-screen to the right and shows blue icons as HTML). Use `position="bottom-left"` or `position="bottom-end"` to ensure it stays on-screen. Icons must render via the Material Icons approach from Sweep 04.
5. **Verify:** Open tickets page. The "New" button dropdown, sprint selector dropdown, and 3-dot header menu should all have: identical item padding, properly rendered icons (no raw HTML), opaque white/solid background (not transparent), and menus that stay within the viewport. Also verify the project dropdown (previously transparent/hard to read) — if it uses the old component, replace it with this one.

---

## Sweep 07: Unified modal Component

**Outcome:** A single `<x-globals::actions.modal>` component is used for all modals. Ticket modal and milestone modal have identical overlay transparency, X button size, and header styling.

1. **Create the component.** Build `globals::actions.modal` with props: `title` (heading text), `size` (sm/md/lg/xl), `show` (boolean or Alpine.js binding). The overlay must use a single fixed `bg-black/50` (or equivalent) — not page-specific transparency. The X close button must be a consistent size (e.g., `w-8 h-8`) using a Material Icon `close`. The header area renders the `title` prop at a fixed heading size. An optional `actions` slot provides the footer area for buttons.
2. **Remove the trash icon from the milestone modal header.** The milestone modal currently has a trash icon in the top-right that "makes no sense." Remove it from the header. If delete functionality is needed, it should be in the modal footer as a button with `state="danger"`.
3. **Replace the ticket detail modal.** Swap its current modal wrapper markup to use `<x-globals::actions.modal title="Task Details" size="lg">`. The content stays as-is inside the slot. The overlay and X button now come from the component.
4. **Replace the milestone modal.** Same approach. After replacement, the headline size should match the ticket modal exactly because both now use the same `title` rendering.
5. **Verify:** Open a ticket modal, then close it and open a milestone modal. The overlay darkness, X button size, and title font size must be identical. The milestone modal should no longer have a trash icon in the header. Closing either modal should not trigger a full page reload (this connects to hx-boost in Sweep 11).

---

## Sweep 08: button Component with Icon Support

**Outcome:** A single `<x-globals::forms.button>` component renders buttons, links-as-buttons, and icon-only circle buttons. The 3-dot menu triggers, widget header icons, and modal action buttons all use this component.

1. **Create the component.** Build `globals::forms.button` with props: `content-role` (primary/secondary/ghost/accent/link), `state` (default/info/warning/danger/success), `element` (button/a/input — controls the rendered HTML tag), `leading-visual` (icon name), `trailing-visual` (icon name), `scale` (xs/s/m/l/xl), `variant` (default/icon-only/circle). When `variant="circle"`, render as a round icon button (for 3-dot menus and widget headers). When `element="a"`, render as an `<a>` tag with button styling.
2. **Replace 3-dot menu trigger buttons.** Across the app, the 3-dot (`more_vert` or `ellipsis`) buttons are currently blue links with no background. Replace each with `<x-globals::forms.button variant="circle" content-role="ghost" leading-visual="more_vert" />`. This gives them consistent sizing and a hover background.
3. **Replace gridstack widget header icons.** These are currently inconsistent (blue, no background, link-styled). Replace with the same `variant="circle"` button. This ensures widget header icons match modal header icons match 3-dot menu icons.
4. **Apply to the filter button's badge area.** The filter button on tickets currently has a floating "1" with no background. The button itself should use this component, and the badge count should use the badge component (Sweep 10).
5. **Verify:** On the ticket kanban view, the 3-dot menu triggers, widget header icons, and filter button should all have consistent sizing and hover states. None should appear as raw blue text links. Icon-only buttons should be circular with appropriate padding.

---

## Sweep 09: chip Component (Status, Milestone, Author)

**Outcome:** Status labels, milestone indicators, and author tags across ticket views all render through `<x-globals::actions.chip>` with correct colors and no white-on-white text.

1. **Create the component.** Build `globals::actions.chip` with props: `variant` (input/choice/action/select), `content-role` (primary/secondary/tertiary), `color` (custom hex — used for milestone colors and status colors), `leading-visual`, `trailing-visual`, `scale`. The component must calculate text color based on the background `color` prop — if the background is light, use dark text; if dark, use light text. This directly fixes the white-on-white milestone chip issue.
2. **Replace milestone chips in dropdowns.** Find where milestone options render in dropdown selectors. Replace with `<x-globals::actions.chip color="{{ $milestone->color }}">{{ $milestone->headline }}</x-globals::actions.chip>`. The automatic contrast logic ensures readable text regardless of milestone color.
3. **Replace status labels on ticket cards/lists.** Where statuses currently render inconsistently (sometimes as chips, sometimes as unstyled text in a dropdown), replace with the chip component using the status color.
4. **Replace author/user tags.** Where author names appear as selectable chips (e.g., in assignment dropdowns), use `<x-globals::actions.chip variant="select" leading-visual="person">`.
5. **Verify:** On the ticket kanban board, milestone chips must show readable text on their colored backgrounds (no white-on-white). Status chips must look identical whether displayed on a card, in a list row, or inside a dropdown. Open the milestone selector dropdown — items should be consistently sized (not "too large" as reported).

---

## Sweep 10: badge, select, checkbox, and radio Fixes

**Outcome:** Filter count badges have visible backgrounds. Single-select dropdowns don't show an X clear button. Checkboxes and radios use the correct theme color (not purple) at consistent sizes.

1. **Build the badge component.** Create `globals::elements.badge` with props: `content-role` (primary/secondary/accent), `scale` (xs/s/m). It renders a small pill with a background color and contrasting text. Default is the theme's primary color with white text.
2. **Fix the filter button badge.** On the tickets filter button, find where the "1" count is rendered. Wrap it in `<x-globals::elements.badge>1</x-globals::elements.badge>`. The floating number now has a visible background pill.
3. **Fix single-select X button.** In the `globals::forms.select` component (or wherever the select is implemented), add logic: if `variant="single"` (or if the component is not multi-select), do not render the clear/X button. The X should only appear on `variant="tags"` or `variant="multiple"`.
4. **Fix checkbox and radio theming.** Find the checkbox component (`globals::forms.checkbox`) and radio component (`globals::forms.radio`). Replace any `purple` or `violet` color classes with the app's primary theme color (likely DaisyUI's `primary` class, e.g., `checkbox-primary`, `radio-primary`). Ensure both use a consistent size — the notification page checkboxes should not be oversized, and widget grouping radios should match the todo screen radios in size. Set a default scale via the component rather than inheriting page-level styles.
5. **Verify:** Tickets page filter button shows a pill badge with "1" on a colored background. Any single-select dropdown (status, milestone, sprint) does not show an X. Notification settings page checkboxes are theme-colored and standard-sized. Widget grouping radios on dashboards and the todo screen are the same color and same size.

---

## Sweep 11: hx-boost Restoration & Page Loader

**Outcome:** Main menu navigation uses `hx-boost="true"`. Navigating between major sections (tickets, projects, goals, etc.) does not trigger full page reloads. Closing a modal does not reload the page. A global page loader (progress bar or spinner) appears during HTMX navigation transitions.

1. **Port hx-boost config from 4.0-beta.** Identify where `hx-boost` was applied in the 4.0-beta branch (likely on the `<body>`, `<main>`, or the nav menu `<a>` tags). Apply the same attribute to the current build's layout file.
2. **Port the global page loader.** In 4.0-beta, an element (likely a top progress bar or spinner overlay) was shown during `htmx:beforeRequest` and hidden on `htmx:afterSettle`. Copy that element and its JS event listeners into the current layout. This is likely a `<div>` with a CSS animation that listens to HTMX lifecycle events.
3. **Fix modal close behavior.** Currently, closing a ticket modal triggers a full page reload. After hx-boost is active, the modal close should trigger an HTMX event (e.g., `htmx:trigger` to refresh the ticket list behind the modal) rather than a `window.location.reload()` or form submission that causes navigation. Check if the modal close button has `onclick="location.reload()"` or similar — remove it and replace with an HTMX-compatible approach.
4. **Verify:** Click "Tickets" in the main menu — the page content should swap without a full browser navigation (URL updates via pushState, no white flash, page loader appears briefly). Open a ticket modal, make an edit, close it — the ticket list should update behind the modal without a full page reload. Open browser DevTools Network tab — main menu clicks should show XHR/fetch requests, not full document loads.

---

## Sweep 12: tabs Component & View Tab Spacing

**Outcome:** `<x-globals::navigation.tabs>` provides consistent tab rendering. The ticket kanban view tabs no longer have excess bottom padding. The project selector's tabs no longer close the dropdown when clicked.

1. **Create/finalize the tabs component.** Build `globals::navigation.tabs` with a `<x-globals::navigation.tab>` child. Each tab accepts: `label`, `href` (for page tabs) or `target` (for in-page tab panels), `active` (boolean). The component renders with consistent padding — specifically, bottom padding/margin should be a fixed value (e.g., `pb-2 mb-4`) that doesn't vary by page context.
2. **Replace ticket view tabs.** The kanban/list/timeline view selector at the top of the tickets page currently has "a large margin/padding on the bottom." Replace with the tabs component. The consistent bottom spacing from the component eliminates the per-page override.
3. **Fix project selector tabs closing the dropdown.** The project selector has tabs inside a dropdown, and clicking a tab closes the dropdown (event propagation issue). In the tabs component, when rendered inside a dropdown context, add `@click.stop` (Alpine.js) or `event.stopPropagation()` to tab click handlers so clicks don't bubble up to the dropdown's close handler.
4. **Verify:** On tickets page, the view tabs (kanban/list/etc.) should have a reasonable, consistent bottom margin — no excessive gap before the content below. In the project selector dropdown, clicking between tabs (e.g., switching between "Recent" and "All Projects") should keep the dropdown open. The selected tab should visually highlight.

---

## Sweep 13: Domain Template Migration (Partials → Components)

**Outcome:** The `partials/` and `submodules/` folders inside domain template directories are converted to properly namespaced Blade components. Domain-specific components (ticket-card, milestone-card, ticket-state-label, comment list, project card) exist and compose the global primitives from previous sweeps.

1. **Convert ticket domain partials.** In the Tickets domain template directory, identify all files in `partials/` and `submodules/`. For each partial that renders a self-contained UI block (e.g., a ticket card, a milestone chip row, a comment thread), convert it to a Blade component under `globals::tickets.*` (e.g., `globals::tickets.ticket-card`). The component should use the global primitives internally — `<x-globals::actions.chip>` for status, `<x-globals::forms.button>` for actions, `<x-globals::elements.card>` for the card wrapper.
2. **Convert project domain partials.** Same process for the Projects domain. The project card partial becomes `globals::projects.project-card` using `<x-globals::elements.card>` and `<x-globals::elements.avatar>`.
3. **Convert goals domain partials.** The goal card that currently has "icons outside the card" should become `globals::goals.goal-card`. When built with `<x-globals::elements.card>`, the icons render inside the card's bounds because the card component controls its own overflow/positioning.
4. **Update all referencing Blade files.** In each domain's main view files, replace `@include('partials.ticket-card')` style references with `<x-globals::tickets.ticket-card :ticket="$ticket" />` component calls.
5. **Verify:** For each domain (tickets, projects, goals), confirm that the `partials/` folder is empty or deleted. Load the ticket list — ticket cards should render using the new component with consistent styling. Load the goals screen — goal card icons should be inside the card boundaries. Load the project dashboard — project cards should use the card component.

---

## Sweep 14: Page-Specific Form Fixes (Timesheets, Profile, Wiki, Theme)

**Outcome:** Every page that has forms uses the `form-field` component from Sweep 05. No page has unique/custom spacing between labels and inputs. The docs/wiki page uses the same input styling and background as the rest of the app.

1. **Fix timesheet tab forms.** The timesheet tab currently shows "a cluster of labels and inputs, no organization." Wrap every label+input pair in `<x-globals::forms.form-field>`. Group related fields visually using the card component if needed.
2. **Fix profile and company settings forms.** These pages have "yet another level of whitespace around forms and labels." Replace their form markup with `form-field` wrappers. Remove any page-specific CSS that overrides form spacing (look for scoped `<style>` blocks or page-specific CSS classes adding extra padding/margin).
3. **Fix docs/wiki input styling.** The wiki/docs editor uses "a completely different input setup and background." Identify what wrapper or layout creates the different background. Replace it with the standard `<x-globals::elements.card>` for the content area, and `<x-globals::forms.form-field>` for any input elements. The background should match the rest of the app.
4. **Fix theme page label alignment.** The theme page has "labels that show up to the left of color pickers and font pickers." Wrap these in `<x-globals::forms.form-field label-position="top">` so labels appear above pickers, matching the standard layout. If the color-picker component exists, ensure it works inside the form-field wrapper.
5. **Verify:** Open each page (timesheets, profile, company settings, docs/wiki, theme) side by side with the ticket detail view. The vertical spacing between label → input → next label should be visually identical across all of them. The docs/wiki page background should match the main app background. The theme page labels should be above their pickers, not floating to the left.

---

## Sweep 15: Goal Screen Bug Fixes

**Outcome:** Creating a new goal does not throw errors. Goal metrics dashboard has proper bottom margin. Goal card icons are inside cards (if not already fixed by Sweep 13).

1. **Debug goal creation errors.** Navigate to the goals screen, open the "create new goal" flow, and trigger the error. Check Laravel logs (`storage/logs/laravel.log`) and browser console for the specific error messages. Common causes: missing required fields in the form submission, a migration that wasn't run, or a controller referencing an old route/view. Fix the root cause — this is likely a backend issue, not a component issue.
2. **Fix goal metrics bottom margin.** On the goals dashboard, the metrics/statistics at the top have "no margin to the bottom." Find the container that wraps the goal metrics (likely a `<div>` with statistic components). Add a bottom margin class (`mb-6` or similar). If using the `<x-globals::elements.statistic>` component, the margin should be built into the component's wrapper or a layout grid.
3. **Verify goal cards.** If Sweep 13 already converted goal cards to use the card component, verify that icons are now inside the card. If they're still outside, the issue is likely an absolutely-positioned icon that's relative to the wrong parent — move the icon element inside the card component's content area.
4. **Verify:** Create a new goal end-to-end — fill form, submit, see it appear on the goal board with no errors in console or logs. The metrics row at the top of the goals dashboard should have visible spacing between it and the content below. Goal card icons should be visually inside their cards.

---

## Sweep 16: Final Cross-Page Consistency Audit

**Outcome:** Every page in the application uses the same component set with no visual outliers. A before/after screenshot set documents the changes.

1. **Systematic page walk-through.** Visit each major page: Ticket Kanban, Ticket List, Ticket Detail (modal), Project Dashboard, Project Cards, Goals Dashboard, Goal Detail, Milestones, Timesheets, My Profile, Company Settings, Notifications, Theme Settings, Docs/Wiki. For each page, check: are all dropdowns using the unified component? Are all form fields wrapped in form-field? Are all icons Material Icons? Are all buttons using the button component? Are modals using the modal component?
2. **Fix any remaining one-off issues.** If any page still has inline/custom implementations, replace them with the canonical components. This sweep is the catch-all for anything missed in previous sweeps.
3. **DaisyUI alignment check.** For each component built, verify that it uses DaisyUI utility classes where they exist (e.g., `btn`, `btn-primary`, `modal`, `dropdown`, `badge`, `checkbox`, `radio`). Components should extend DaisyUI, not fight against it.
4. **Verify:** No `grep -r` for deprecated component files returns results. A clean `php artisan view:cache` completes without errors. The application loads end-to-end with no console errors related to missing components or undefined variables.
