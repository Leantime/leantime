# Leantime Glass Effects — Surface Map & Requirements

**Version:** 1.3
**Date:** February 24, 2026
**Owner:** Gloria Folaron
**Status:** Design decisions locked — ready for implementation
**Depends on:** Design System v2 foundation (token aliasing) — ✅ Complete

**Changelog v1.3:** Removed emboss patterns from glass effects scope. Emboss (inset track/fill treatment for progress bars, milestones, toggles) is a separate visual polish concern, not glass (backdrop-filter) effects. Section 4 replaced with scope redirect. Phase E removed from implementation priority. Emboss tokens committed in Phase A are inert — harmless but will be referenced by a future emboss spec, not this one.

**Changelog v1.2:** Fixed dark mode value contradiction between sections 2.1 and 7 — Section 7 now documents target values explicitly, Phase A scope expanded to normalize existing dark values. Moved phase row UX redesign to separate Logic Model Canvas spec (out of scope for glass). Renamed utility classes `.glass` → `.lt-glass` to avoid third-party collisions.

**Changelog v1.1:** Fixed theme architecture to match actual codebase (default/minimal themes, separate light.css/dark.css files). Removed fictional .theme-focus/.theme-high-contrast selectors. Added subtle glass token gap. Added card glass Tailwind class gap. Locked all 5 design review questions. Added hardcoded blur inventory.

---

## 1. Purpose

This document maps every UI surface in Leantime to a glass treatment decision. The design system requirements doc (section 5) established the general rules — this document gets specific: which Blade components, which page regions, which interactive elements get glass, and at what level.

**Why this needs a separate doc:** Glass effects touch performance (backdrop-filter is GPU-composited), accessibility (translucent backgrounds can reduce text contrast), and theme architecture (two themes × two modes = four combinations that all need to work). Rushing this creates visual regressions. Surface-by-surface decisions prevent that.

**Companion documents:**
- `leantime-design-system-requirements.md` — Section 5 (Glass Brutalist Aesthetic)
- `leantime-glass-effects-claude-code-prompt.md` — Implementation prompt
- `leantime-component-guide-v2.html` — Visual reference showing glass in action

---

## 2. Glass Token System

### 2.1 What Already Exists

These primary glass tokens are **already defined** in all 4 theme CSS files:

| Token | Purpose | Defined in |
|-------|---------|-----------|
| `--glass-blur` | Primary blur value | All 4 theme files |
| `--glass-background` | Glass bg (alias) | All 4 theme files |
| `--glass-border` | Glass border color | All 4 theme files |
| `--glass-bg` | Glass bg (primary) | All 4 theme files |
| `--glass-inset` | Inner highlight | All 4 theme files |

**Current values across themes:**

| Theme file | --glass-blur | --glass-bg opacity | Notes |
|-----------|-------------|-------------------|-------|
| default/light.css | `blur(8px)` | 85% | Full glass |
| default/dark.css | `blur(24px)` | 50% (`rgba(0,0,0,0.5)`) | ⚠️ Too translucent — needs correction |
| minimal/light.css | `blur(4px)` | 100% | Effectively no glass (opaque) |
| minimal/dark.css | `blur(4px)` | 30% | ⚠️ Too translucent — needs correction |

**⚠️ Dark mode values need normalization (Phase A).** The current `default/dark.css` uses `rgba(0,0,0,0.5)` (50% opacity, pure black base) with `blur(24px)`. This creates a muddy dark wash. Section 7 defines the corrected target values: `rgba(30,30,46,0.85)` (85% opacity, tinted dark base) with `blur(8px)`. Phase A must update both existing full-tier dark values AND add the new subtle tier. The hierarchy must be:

- **Full glass dark:** 85% opacity, `blur(8px)` — most translucent, most blur
- **Subtle glass dark:** 92% opacity, `blur(4px)` — less translucent, less blur
- **No glass (minimal):** 100% opacity, no blur — opaque

**Already using glass:**
- Global modal uses `backdrop-filter: var(--glass-blur)` ✅
- Card component has `glass => false` prop scaffold ✅

### 2.2 What's Missing (Must Add)

**Subtle glass tier tokens** — referenced throughout this document but not yet defined:

| Token | Purpose | Status |
|-------|---------|--------|
| `--glass-bg-subtle` | Subtle glass background | ❌ Not in any theme file |
| `--glass-blur-subtle` | Subtle blur value | ❌ Not in any theme file |

These must be added to all 4 theme CSS files in Phase A.

**Proposed values:**

| Theme file | --glass-bg-subtle | --glass-blur-subtle |
|-----------|------------------|-------------------|
| default/light.css | `rgba(255, 255, 255, 0.92)` | `blur(4px)` |
| default/dark.css | `rgba(30, 30, 46, 0.92)` | `blur(4px)` |
| minimal/light.css | `var(--color-bg-card)` (opaque) | `none` |
| minimal/dark.css | `var(--color-bg-card)` (opaque) | `none` |

**Card glass Tailwind class** — card.blade.php maps `glass => true` to `tw:glass`, but that class is not defined in Tailwind config. Fix: define `.lt-glass` as a plain CSS utility class (section 2.3) and update card.blade.php to use `lt-glass` instead of `tw:glass`. Prefixed to avoid third-party class name collisions.

### 2.3 Glass Utility Classes (To Add)

```css
/* Full glass surface */
.lt-glass {
  background: var(--glass-bg);
  backdrop-filter: var(--glass-blur);
  -webkit-backdrop-filter: var(--glass-blur);
  border: 1px solid var(--glass-border);
  box-shadow: var(--glass-inset), var(--shadow-sm);
}

/* Subtle glass surface */
.lt-glass-subtle {
  background: var(--glass-bg-subtle);
  backdrop-filter: var(--glass-blur-subtle);
  -webkit-backdrop-filter: var(--glass-blur-subtle);
  border: 1px solid var(--glass-border);
}

/* Fallback for browsers without backdrop-filter */
@supports not (backdrop-filter: blur(1px)) {
  .lt-glass, .lt-glass-subtle {
    background: var(--color-bg-card);
    backdrop-filter: none;
    -webkit-backdrop-filter: none;
  }
}
```

### 2.4 Theme Degradation (How It Actually Works)

Leantime has **two** themes: "default" and "minimal". Each theme has separate light.css and dark.css files that define their own token values. There are no CSS class selectors like `.theme-focus` or `.theme-high-contrast`.

**Degradation is automatic** — it happens because each theme file sets different values for the same glass tokens:

| Surface type | default theme | minimal theme |
|-------------|--------------|---------------|
| Full-glass surface | blur(8px), 85% opacity | blur(4px), 100% (effectively opaque) |
| Subtle-glass surface | blur(4px), 92% opacity | opaque, no blur |
| No-glass surface | Opaque | Opaque |

Components reference tokens. Theme files control what those tokens resolve to. No conditional CSS logic needed in components.

**Dark mode is handled by separate CSS files** — Leantime loads `light.css` or `dark.css` per theme. There are no `[data-theme="dark"]` or `.theme-dark` attribute selectors in shared CSS. All dark-mode glass values live in the respective `dark.css` files.

---

## 3. Surface Decision Map

### Decision key
- ✅ **Full glass** — Primary floating surface. Uses `--glass-bg` / `--glass-blur`.
- 🔵 **Subtle glass** — Secondary/nested. Uses `--glass-bg-subtle` / `--glass-blur-subtle`.
- ⬜ **No glass** — Opaque `var(--color-bg-card)` or `var(--color-bg-page)`.
- 🔶 **Conditional** — Glass only in specific contexts (documented below).

### 3.1 Layout Surfaces

| Surface | Blade Component | Decision | Rationale |
|---------|----------------|----------|-----------|
| **Navigation sidebar** | Main layout template | ✅ Full | Permanent landmark. Signature glass surface. |
| **Top header bar** | Main layout template | 🔵 Subtle | Sticky — full blur on scroll is distracting. |
| **Page background** | Body / main wrapper | ⬜ No glass | This IS the background. |
| **Footer** | Main layout template | ⬜ No glass | Opaque to anchor the page. |
| **Sidebar panels** (right) | Various views | 🔵 Subtle | Contextual surfaces — lighter than nav (intentional asymmetry). |

### 3.2 Container Components

| Surface | Blade Component | Decision | Rationale |
|---------|----------------|----------|-----------|
| **Card** | `elements/card.blade.php` | 🔶 Conditional | Glass when `glass` prop is true. Default false. Templates opt in. |
| **Card (glass mode)** | glass=true | ✅ Full | Explicitly opted in. |
| **Card (default)** | glass=false | ⬜ No glass | Opaque `var(--color-bg-card)`. |
| **Widget** | `content/widget.blade.php` | 🔵 Subtle | blur(4px) is cheap even at 10+ widgets. |
| **Accordion** | `accordion.blade.php` | ⬜ No glass | Text readability. |
| **Collapsible** | `collapsible.blade.php` | ⬜ No glass | Same. |

### 3.3 Overlay Surfaces

| Surface | Blade Component | Decision | Rationale |
|---------|----------------|----------|-----------|
| **Modal** | `actions/modal.blade.php` | ✅ Full | Already using glass tokens. |
| **Modal (confirm-delete)** | `actions/confirm-delete.blade.php` | ✅ Full | Same. |
| **Dropdown menu** | `elements/dropdown.blade.php` | ✅ Full | Small floating surface. |
| **Button dropdown** | `button-dropdown.blade.php` | ✅ Full | Same. |
| **Link dropdown** | `link-dropdown.blade.php` | ✅ Full | Same. |
| **Tooltip** | Custom / inline | 🔵 Subtle | Small, transient. |
| **Popover** | Health popover, etc. | 🔵 Subtle | Medium floating. |
| **Toast / notification** | Floating alert | 🔵 Subtle | Position TBD (not a glass decision). |
| **Context menu** | Right-click menus | ✅ Full | Dropdown pattern. |

### 3.4 Form Elements

| Surface | Blade Component | Decision | Rationale |
|---------|----------------|----------|-----------|
| **Input** | `forms/input.blade.php` | ⬜ No glass | **Never glass.** |
| **Select** | `forms/select.blade.php` | ⬜ No glass | Same. |
| **Textarea** | `forms/textarea.blade.php` | ⬜ No glass | Same. |
| **Checkbox/Toggle** | `forms/checkbox.blade.php` | ⬜ No glass | Opaque for clarity. |
| **Custom select dropdown** | JS select only | ✅ Full | Options panel follows dropdown rules. |

### 3.5 Data Display

| Surface | Blade Component | Decision | Rationale |
|---------|----------------|----------|-----------|
| **Table** | `elements/table.blade.php` | ⬜ No glass | **Never glass.** |
| **Table wrapper card** | Card wrapping table | ⬜ No glass | No glass for tabular content. |
| **Metric cell** | `metric-cell.blade.php` | ⬜ No glass | Number readability. |
| **Proportion bar** | `proportion-bar.blade.php` | ⬜ No glass | Small data element — stay opaque. |
| **Progress bar** | `progress.blade.php` | ⬜ No glass | Small data element — stay opaque. See future emboss spec. |

### 3.6 Specialized Components

| Surface | Blade Component | Decision | Rationale |
|---------|----------------|----------|-----------|
| **Stageflow card (active)** | `stageflow/card.blade.php` | ⬜ No glass | Shadow + accent border + scale already differentiates. Glass is noise. |
| **Stageflow card (inactive)** | `stageflow/card.blade.php` | ⬜ No glass | Dimmed/scaled — glass fights "receded" look. |
| **Kanban column** | `kanban/` components | ⬜ No glass | Content-dense. |
| **Kanban card** | Task cards | ⬜ No glass | N cards = N composites. |
| **Selectable tiles** | `selectable.blade.php` | 🔶 Conditional | Selected = subtle glass. Unselected = opaque. |
| **Inline edits** | `inlineLinks/Select/dropdownPill` | ⬜ No glass | Stay opaque for focus. |
| **Dropdown pill** | `dropdownPill.blade.php` | ⬜ No glass | Its dropdown menu IS glass per 3.3. |
| **Empty state** | `empty-state.blade.php` | ⬜ No glass | Illustration provides interest. |
| **Page header** | `pageheader.blade.php` | ⬜ No glass | Structural. Sticky header (3.1) gets glass. |
| **Tabs** | `tabs.blade.php` | ⬜ No glass | Nav element, not a surface. |
| **Timeline / Gantt bars** | Milestone bars | ⬜ No glass | Many instances — stay opaque. See future emboss spec. |

### 3.7 Alert & Feedback

| Surface | Blade Component | Decision | Rationale |
|---------|----------------|----------|-----------|
| **Alert (inline)** | `feedback/alert.blade.php` | ⬜ No glass | Semantic bg tints = status signal. |
| **Alert (floating/toast)** | Same, floating context | 🔵 Subtle | Floats over content. |
| **Badge** | `badge.blade.php` | ⬜ No glass | Tiny element. |
| **Health popover** | Stageflow health | 🔵 Subtle | Small floating panel. |
| **AI assist popover** | AI trigger | 🔵 Subtle | Floating preview. |

---

## 4. Emboss Patterns — OUT OF SCOPE

Emboss (inset track/fill treatment for progress bars, milestones, toggles) is **not** glass. Glass = `backdrop-filter` frosted surfaces. Emboss = layered opacity for depth on small bar elements. Different visual concern, different implementation pattern.

| Topic | Redirect |
|-------|----------|
| Progress bar emboss | Future spec: Progress & Bar Visual Polish |
| Milestone/Gantt bar emboss | Future spec: Timeline Visual Polish |
| Toggle track emboss | Future spec: Form Component Polish |

The `--emboss-track` and `--emboss-highlight` tokens committed in Phase A are inert (nothing references them). They will be consumed by the emboss spec when written.

---

## 5. Hardcoded Blur Inventory

These existing hardcoded blur values must be migrated to glass tokens:

| Location | Current Value | Should Be | Phase |
|----------|-------------|-----------|-------|
| `resources/css/main.css` `#modal-blur-overlay` | `backdrop-filter: blur(12px)` | `backdrop-filter: var(--glass-blur)` | B |
| Registration layout | Inline `backdrop-filter: blur(3px)` | `backdrop-filter: var(--glass-blur-subtle)` | C |

Search for others:
```bash
grep -rn "backdrop-filter:\s*blur" \
  app/Views/ resources/css/ \
  --include="*.blade.php" --include="*.css" \
  | grep -v "var(--glass"
```

---

## 6. Performance Budget

**Target:** ≤15 simultaneously visible glass surfaces.

**Worst case — Dashboard:** Sidebar (1) + Header (1) + 10 widgets (subtle) + 1 dropdown (full) = 13 ✅

**Subtle is cheap:** `blur(4px)` costs roughly half the composite time of `blur(8px)`. Having 10 subtle surfaces ≈ 5 full surfaces in GPU cost.

**Fallback:** `@supports not (backdrop-filter: blur(1px))` → opaque `var(--color-bg-card)`.

---

## 7. Dark Mode

**Current dark values are wrong.** The existing `default/dark.css` uses `rgba(0,0,0,0.5)` (50% opacity pure black) with `blur(24px)`. This is too translucent and too blurry — dark mode glass should be MORE opaque than light mode, not less. Phase A corrects these.

**Target values (Phase A will apply these):**

| Token | light.css (no change) | dark.css (CORRECTED) |
|-------|-----------------------|---------------------|
| `--glass-bg` | `rgba(255,255,255,0.85)` | `rgba(30,30,46,0.85)` ← was `rgba(0,0,0,0.5)` |
| `--glass-blur` | `blur(8px)` | `blur(8px)` ← was `blur(24px)` |
| `--glass-bg-subtle` | `rgba(255,255,255,0.92)` | `rgba(30,30,46,0.92)` (new) |
| `--glass-blur-subtle` | `blur(4px)` | `blur(4px)` (new) |
| `--glass-border` | `rgba(255,255,255,0.2)` | `rgba(255,255,255,0.06)` |
| `--glass-inset` | `inset 0 1px 0 rgba(255,255,255,0.1)` | `inset 0 1px 0 rgba(255,255,255,0.04)` |

**Opacity hierarchy (both modes):**
- Subtle glass (92%) > Full glass (85%) > Page background — subtle is MORE opaque (less glass), which is correct
- minimal theme: 100% opacity both tiers (no glass at all)

**Pitfalls:** Border at 0.06 white is the sweet spot (higher = bright outline, lower = invisible).

---

## 8. Accessibility

- Glass surfaces: minimum 12px content padding
- minimal theme serves as reduced-glass fallback (100% opacity light, 4px blur dark)
- `prefers-reduced-motion: reduce` → transition duration near-zero, glass is instant
- No "blur in/out" animations

---

## 9. Design Decisions (Locked)

| # | Question | Decision | Rationale |
|---|---------|----------|-----------|
| 1 | Widget glass level | 🔵 **Subtle on all** | blur(4px) at 92% is cheap |
| 2 | Stageflow active card | ⬜ **No glass** | Already differentiated by shadow + border + scale |
| 3 | Sidebar asymmetry | **Intentional** | Nav = landmark (full), panels = contextual (subtle) |
| 4 | Toast position | **Deferred** | Notification system decision, not glass |
| 5 | Card glass default | **Keep false** | Templates opt in |

---

## 10. Out of Scope (Tracked Elsewhere)

The following items were discussed during glass effects design review but belong in separate specs:

| Item | Why it's separate | Tracked in |
|------|------------------|------------|
| Phase row UX redesign (chevron-left layout, grayscale picker, dual swatch palettes) | Interaction/layout design, not visual surface treatment | Logic Model Canvas spec (Phase 2a) |
| Toast/notification positioning | Notification system decision, not glass | Deferred |
| Consumer migration (Leantime → Focus theme) | Theme strategy, not glass effects | Pending decision |

## 11. Implementation Priority

| Phase | What | Effort |
|-------|------|--------|
| **A** | Normalize dark mode values + subtle glass tokens + utility classes in 4 theme files | Small–Medium |
| **B** | Overlay surfaces + `#modal-blur-overlay` hardcoded fix | Medium |
| **C** | Layout surfaces + registration inline blur fix | Medium |
| **D** | Card glass prop fix (tw:lt-glass → .lt-glass), widget subtle glass | Medium |
| **E** | Dark mode verification across both themes | Testing |
