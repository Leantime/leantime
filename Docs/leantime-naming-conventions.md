# Leantime Design System — Naming Conventions Reference

> Blade Components · CSS Tokens · Attribute API
>
> How to name things so humans and AI agents can read, write, and maintain Leantime code without guessing. This is the single source of truth for the naming system that connects Blade templates, CSS custom properties, and component attributes.

---

## 1. The Naming Philosophy

Every name answers **"What is this for?"** — never "What does it look like?"

```
Bad:  bg-blue-500         →  Good: bg-primary
Bad:  --lt-bg-2           →  Good: --color-surface-card
Bad:  font-lg-bold        →  Good: --text-heading-2
```

This applies to three naming surfaces:

1. **Blade component tags and attributes** — how you call components in templates
2. **CSS custom properties (design tokens)** — how you style them
3. **Component attribute values** — the shared vocabulary for role, state, and scale

---

## 2. Blade Component Tags

### 2.1 Tag Name Grammar

```blade
<x-{scope}::{category}.{component}>
```

| Segment | Rule | Examples |
|---------|------|----------|
| `scope` | `globals` (shared) or domain name | `globals`, `tickets`, `projects` |
| `category` | Functional group from tracker | `actions`, `elements`, `forms`, `navigation`, `feedback`, `layout` |
| `component` | kebab-case, singular noun | `dropdown-menu`, `text-input`, `badge` |

### 2.2 Complete Component Tag Registry

#### Global Simple

| Component | Tag Name | Category | Priority |
|-----------|----------|----------|----------|
| dropdown-menu | `<x-globals::actions.dropdown-menu>` | actions | P0 |
| modal | `<x-globals::actions.modal>` | actions | P0 |
| chip | `<x-globals::actions.chip>` | actions | P0 |
| card | `<x-globals::elements.card>` | elements | P0 |
| avatar | `<x-globals::elements.avatar>` | elements | P1 |
| accordion | `<x-globals::elements.accordion>` | elements | P1 |
| table | `<x-globals::elements.table>` | elements | P1 |
| empty-state | `<x-globals::elements.empty-state>` | elements | P1 |
| date-info | `<x-globals::elements.date-info>` | elements | P1 |
| code | `<x-globals::elements.code>` | elements | P1 |
| statistic | `<x-globals::elements.statistic>` | elements | P1 |
| badge | `<x-globals::elements.badge>` | elements | P1 |
| chat-bubble | `<x-globals::elements.chat-bubble>` | elements | P2 |
| keyboard | `<x-globals::elements.keyboard>` | elements | P2 |
| calendar | `<x-globals::elements.calendar>` | elements | P2 |
| button | `<x-globals::forms.button>` | forms | P0 |
| checkbox | `<x-globals::forms.checkbox>` | forms | P1 |
| radio | `<x-globals::forms.radio>` | forms | P1 |
| select | `<x-globals::forms.select>` | forms | P0 |
| text-input | `<x-globals::forms.text-input>` | forms | P0 |
| toggle | `<x-globals::forms.toggle>` | forms | P1 |
| button-group | `<x-globals::forms.button-group>` | forms | P1 |
| textarea | `<x-globals::forms.textarea>` | forms | P2 |
| file-input | `<x-globals::forms.file-input>` | forms | P2 |
| range | `<x-globals::forms.range>` | forms | P2 |
| form-field | `<x-globals::forms.form-field>` | forms | P0 |
| tabs | `<x-globals::navigation.tabs>` | navigation | P0 |
| steps | `<x-globals::navigation.steps>` | navigation | P1 |
| breadcrumbs | `<x-globals::navigation.breadcrumbs>` | navigation | P1 |
| pagination | `<x-globals::navigation.pagination>` | navigation | P1 |
| context-menu | `<x-globals::navigation.context-menu>` | navigation | P1 |
| menu | `<x-globals::navigation.menu>` | navigation | P2 |
| navbar | `<x-globals::navigation.navbar>` | navigation | P2 |
| alert | `<x-globals::feedback.alert>` | feedback | P1 |
| loading | `<x-globals::feedback.loading>` | feedback | P1 |
| progress | `<x-globals::feedback.progress>` | feedback | P1 |
| skeleton | `<x-globals::feedback.skeleton>` | feedback | P1 |
| indicator | `<x-globals::feedback.indicator>` | feedback | P1 |

#### Global Special

| Component | Tag Name | Category | Priority |
|-----------|----------|----------|----------|
| text-editor | `<x-globals::forms.text-editor>` | forms | P0 |
| date-picker | `<x-globals::forms.date-picker>` | forms | P0 |
| color-picker | `<x-globals::forms.color-picker>` | forms | P1 |
| emoji-input | `<x-globals::forms.emoji-input>` | forms | P2 |
| select-panel | `<x-globals::action.select-panel>` | action | P1 |
| page-header | `<x-globals::layout.page-header>` | layout | P1 |

#### Domain Specific

| Component | Tag Name | Category |
|-----------|----------|----------|
| list | `<x-globals::comments.list>` | comments |
| ticket-card | `<x-globals::tickets.ticket-card>` | tickets |
| milestone-card | `<x-globals::tickets.milestone-card>` | tickets |
| ticket-state-label | `<x-globals::tickets.ticket-state-label>` | tickets |
| project-card | `<x-globals::projects.project-card>` | projects |

---

## 3. Component Attribute API

Every component shares a standard set of attributes. Learn it once, use it everywhere.

### 3.1 IDL (Behavioral) Attributes

These control what the component does and how it looks at a behavioral level.

| Attribute | Options | Default | What It Controls |
|-----------|---------|---------|------------------|
| `content-role` | `primary`, `secondary`, `ghost`, `accent`, `link`, `default` | `primary` (actions), `default` (elements) | Visual weight / emphasis. Determines whether a button is the main CTA or a quiet secondary action. |
| `state` | `default`, `info`, `warning`, `danger`, `success` | `default` | Semantic meaning. Maps directly to status colors. An alert with `state="danger"` gets red treatment. |
| `variant` | (component-specific) | `""` | Behavioral mode. Changes how the component works, not just how it looks. e.g. chip `variant="filter"` vs `variant="action"`. |
| `scale` | `xs`, `s`, `m`, `l`, `xl` | `m` | Size. Applies to the entire component uniformly — font, padding, icon size all scale together. |
| `position` | `left`, `right`, `top`, `bottom`, `start`, `end`, `inner`, `outer` | `bottom` | Spatial placement of sub-elements like tooltips, dropdowns, labels. |
| `element` | `a`, `input`, `button` | (semantic default) | HTML element override. A button that navigates uses `element="a"`. Rare but necessary. |
| `align` | `start`, `end` | — | Content alignment within the component. |

### 3.2 Content Attributes

These pass content into component slots. Consistent pattern across all form-type components.

| Attribute | Value | Used In | Notes |
|-----------|-------|---------|-------|
| `label-text` | string | All form components | The visible label above/beside the input. |
| `label-position` | `top`, `left`, `right`, `bottom`, `inside` | All form components | Where the label renders relative to the control. |
| `caption` | string | All form components | Helper text beneath the input. |
| `validation-text` | string | All form components | Error/success message text. |
| `validation-state` | `error`, `success`, `warning` | All form components | Which validation style to apply. |
| `leading-visual` | icon name | dropdown-item, button, input | Icon before the content. |
| `trailing-visual` | icon name | dropdown-item, button, input | Icon after the content. |
| `items` | array | dropdown, select | Data source for list-based components. |
| `link` / `href` | URL string | Any actionable component | Navigation target when the component is clicked. |

### 3.3 The Form Component Pattern

Every form component accepts the same base attribute set. This is the "form field contract" — if a component is in the `forms` category, it supports all of these:

```blade
<x-globals::forms.text-input
    label-text="Email address"
    caption="We'll never share your email"
    validation-text="Please enter a valid email"
    validation-state="error"
    leading-visual="mail"
    state="danger"
    scale="m"
/>
```

The same pattern works for `checkbox`, `radio`, `select`, `textarea`, `toggle`, `file-input`, and `range`. A developer who knows one knows all of them.

---

## 4. CSS Token Naming

Leantime uses CSS custom properties as design tokens. The naming system has three layers, modeled after GitHub Primer's approach.

### 4.1 The Three Layers

| Layer | Purpose | Example | Who Uses It |
|-------|---------|---------|-------------|
| **Base (Primitive)** | Raw values. Never used in components. | `--lt-color-blue-500: #1B75BB` | Token file only. `theme.css` defines these. |
| **Functional (Semantic)** | What it's for. Used in templates. | `--color-brand-primary: var(--lt-color-blue-500)` | Any template, any component. |
| **Component** | Scoped to one component. Overridable. | `--btn-bg-primary: var(--color-brand-primary)` | Inside that component's CSS only. |

### 4.2 Token Name Grammar

```
--{category}-{property}-{element}-{variant}-{state}
```

Read left to right: start broad, get specific. Not every segment is required — use only what's needed to be unambiguous.

| Segment | What It Is | Values |
|---------|-----------|--------|
| `category` | What kind of token | `color`, `spacing`, `radius`, `shadow`, `text`, `z`, `size` |
| `property` | What CSS property it maps to | `bg`, `border`, `text`, `surface`, `brand`, `focus`, `font`, `weight`, `line` |
| `element` | What UI element it targets | `page`, `card`, `input`, `sidebar`, `header`, `modal`, `overlay` |
| `variant` | Semantic variation | `primary`, `secondary`, `muted`, `danger`, `success`, `warning`, `info` |
| `state` | Interaction state | `default`, `hover`, `focus`, `active`, `disabled` |

### 4.3 Naming Shortcuts (Decision Tree)

1. **Is it a raw value (hex, px, rem)?** → Base layer. Prefix with `--lt-`. Example: `--lt-color-green-600`
2. **Is it used across many components?** → Functional layer. No prefix. Example: `--color-state-danger`
3. **Is it only for one component?** → Component layer. Prefix with component name. Example: `--btn-bg-primary`

### 4.4 Functional Token Quick Reference

#### Colors

| Token | Use It For | Maps To |
|-------|-----------|---------|
| `--color-brand-primary` | Main brand color, primary buttons, active states | `--lt-color-blue-500` |
| `--color-brand-secondary` | Secondary accents, hover highlights | `--lt-color-green-500` |
| `--color-surface-page` | Page background | `--lt-color-gray-50` |
| `--color-surface-card` | Card/panel backgrounds | `#ffffff` |
| `--color-surface-inset` | Recessed areas, code blocks, table stripes | `--lt-color-gray-100` |
| `--color-surface-overlay` | Modal/dropdown backdrops | `rgba(0,0,0,0.5)` |
| `--color-text-default` | Body text | `--lt-color-gray-800` |
| `--color-text-muted` | Secondary text, captions, timestamps | `--lt-color-gray-500` |
| `--color-text-link` | Hyperlinks | `--color-brand-primary` |
| `--color-text-on-emphasis` | Text on colored backgrounds (buttons etc) | `#ffffff` |
| `--color-border-default` | Standard borders | `--lt-color-gray-200` |
| `--color-border-emphasis` | Active/focused borders | `--lt-color-gray-400` |
| `--color-state-danger` | Errors, destructive actions | `--lt-color-red-500` |
| `--color-state-warning` | Warnings, at-risk indicators | `--lt-color-yellow-500` |
| `--color-state-success` | Confirmations, on-track indicators | `--lt-color-green-500` |
| `--color-state-info` | Informational alerts, tips | `--lt-color-blue-400` |
| `--color-focus-ring` | Keyboard focus outline (a11y critical) | `--lt-color-blue-300` |

#### Spacing

| Token | Value | Typical Use |
|-------|-------|-------------|
| `--spacing-xs` | `0.25rem` (4px) | Tight gaps, icon margins |
| `--spacing-s` | `0.5rem` (8px) | Inner padding, compact lists |
| `--spacing-m` | `1rem` (16px) | Default padding, card gutters |
| `--spacing-l` | `1.5rem` (24px) | Section spacing |
| `--spacing-xl` | `2rem` (32px) | Page margins, major separations |
| `--spacing-2xl` | `3rem` (48px) | Hero spacing, layout gaps |

#### Typography

| Token | Value | Use For |
|-------|-------|---------|
| `--text-xs` | `0.75rem` | Badges, tiny labels |
| `--text-s` | `0.875rem` | Captions, helper text |
| `--text-m` | `1rem` | Body text (default) |
| `--text-l` | `1.25rem` | Subheadings, emphasis |
| `--text-xl` | `1.5rem` | Page titles |
| `--text-2xl` | `2rem` | Hero headings |
| `--font-family-base` | `system-ui, sans-serif` | All UI text |
| `--font-family-mono` | `monospace` | Code blocks, pre |
| `--font-weight-normal` | `400` | Body text |
| `--font-weight-medium` | `500` | Labels, emphasis |
| `--font-weight-bold` | `700` | Headings, strong emphasis |

#### Radius, Shadow, Z-Index

| Token | Value | Use For |
|-------|-------|---------|
| `--radius-s` | `0.25rem` | Buttons, badges, chips |
| `--radius-m` | `0.5rem` | Cards, inputs, dropdowns |
| `--radius-l` | `1rem` | Modals, large panels |
| `--radius-full` | `9999px` | Avatars, pills |
| `--shadow-s` | `0 1px 2px rgba(0,0,0,0.05)` | Subtle lift: buttons, chips |
| `--shadow-m` | `0 4px 6px rgba(0,0,0,0.07)` | Cards, dropdowns |
| `--shadow-l` | `0 10px 15px rgba(0,0,0,0.1)` | Modals, popovers |
| `--z-dropdown` | `1000` | Dropdown menus |
| `--z-sticky` | `1100` | Sticky headers |
| `--z-modal` | `1300` | Modal dialogs |
| `--z-toast` | `1400` | Toast notifications |

---

## 5. Component Token Patterns

When a component needs its own token, prefix with the component's short name. The internal structure mirrors the attribute API: `content-role` maps to variant, `state` maps to state.

### 5.1 Token ↔ Attribute Alignment

The CSS token names and the Blade attribute values share vocabulary. This is intentional — if a button accepts `content-role="primary"`, its CSS token is `--btn-bg-primary`.

| Blade Attribute | Attribute Value | CSS Token Pattern |
|----------------|----------------|-------------------|
| `content-role="primary"` | `primary` | `--{component}-bg-primary`, `--{component}-text-primary` |
| `content-role="secondary"` | `secondary` | `--{component}-bg-secondary` |
| `content-role="ghost"` | `ghost` | `--{component}-bg-ghost` (transparent) |
| `state="danger"` | `danger` | `--{component}-bg-danger`, `--{component}-border-danger` |
| `state="success"` | `success` | `--{component}-bg-success` |
| `scale="s"` | `s` | `--{component}-padding-s`, `--{component}-font-s` |

### 5.2 Button Tokens (Example)

```css
/* Button component tokens — defined in button.blade.php or component CSS */
--btn-bg-primary:     var(--color-brand-primary);
--btn-bg-secondary:   var(--color-surface-inset);
--btn-bg-ghost:       transparent;
--btn-text-primary:   var(--color-text-on-emphasis);
--btn-text-secondary: var(--color-text-default);
--btn-border-default: var(--color-border-default);
--btn-padding-s:      var(--spacing-xs) var(--spacing-s);
--btn-padding-m:      var(--spacing-s) var(--spacing-m);
--btn-padding-l:      var(--spacing-m) var(--spacing-l);
```

Every component follows this same pattern. Replace `btn` with `card`, `input`, `alert`, `chip`, `badge`, `modal`, etc.

### 5.3 Naming a New Component Token

```
--{short-name}-{property}-{variant}
```

| short-name | property | variant (optional) |
|-----------|----------|-------------------|
| `btn`, `card`, `input`, `alert` | `bg`, `text`, `border`, `shadow`, `padding`, `font`, `radius` | `primary`, `secondary`, `ghost`, `danger`, `success` |
| `chip`, `badge`, `tab`, `modal` | `bg-hover`, `border-focus`, `text-muted` | `info`, `warning`, `active`, `disabled` |
| `select`, `toggle`, `radio` | `ring` (focus ring width), `gap` (spacing between) | `checked`, `unchecked`, `indeterminate` |

---

## 6. The Rules

1. **Tailwind + DaisyUI only.** No custom CSS without approval (per Guidelines checklist). Design tokens are the exception — they're defined in `theme.css`.

2. **No magic numbers.** Every spacing, color, and font-size value must come from a token. Never hardcode px, hex, or rem in a template.

3. **Kebab-case everything.** Component names, attribute names, CSS tokens. All kebab-case. No camelCase, no underscores.

4. **Attributes describe content, not style.** A developer passes `content-role="primary"`, not `class="bg-blue-500 text-white"`. The component decides how to render `primary`.

5. **Components handle their own data.** A developer should not need to reshape data for a component. Pass it from the controller as-is.

6. **No business logic in components.** Components are presentation only. Logic lives in services and controllers.

7. **Fail gracefully.** If an attribute is missing or wrong, the component should render with safe defaults, not throw.

8. **ARIA and WCAG from day one.** Every component includes appropriate `aria-*` attributes. Keyboard navigation works. Focus ring is visible.

9. **Responsive by default.** Every component works on mobile. No separate mobile components.

---

## 7. Copy-Paste Patterns

### Primary Button

```blade
<x-globals::forms.button content-role="primary" leading-visual="plus">
    Create New Task
</x-globals::forms.button>
```

### Danger Button (Ghost)

```blade
<x-globals::forms.button content-role="ghost" state="danger">
    Delete
</x-globals::forms.button>
```

### Form Input with Validation

```blade
<x-globals::forms.text-input
    label-text="Project Name"
    caption="Choose something memorable"
    validation-text="{{ $errors->first('name') }}"
    validation-state="error"
    :value="$project->name"
/>
```

### Select with Remote Data

```blade
<x-globals::forms.select
    label-text="Assign To"
    variant="tags"
    :items="$teamMembers"
    search="true"
/>
```

### Status Badge

```blade
<x-globals::elements.badge state="success" scale="s">
    On Track
</x-globals::elements.badge>
```

### Alert Toast

```blade
<x-globals::feedback.alert state="warning" variant="toast">
    Your session will expire in 5 minutes.
</x-globals::feedback.alert>
```

### Card with Dropdown

```blade
<x-globals::elements.card>
    <x-globals::actions.dropdown-menu position="right">
        <x-globals::actions.dropdown-item leading-visual="edit" href="/edit">
            Edit
        </x-globals::actions.dropdown-item>
        <x-globals::actions.dropdown-item leading-visual="trash" state="danger">
            Delete
        </x-globals::actions.dropdown-item>
    </x-globals::actions.dropdown-menu>
    {{ $content }}
</x-globals::elements.card>
```

---

## 8. Notes for AI Coding Agents

If you're an AI agent working on Leantime code:

- **Tag names are predictable:** `<x-globals::{category}.{component}>`. If you know the component name and its category from Section 2, you can construct the tag.

- **Token names are predictable:** `--color-{purpose}-{variant}` for colors, `--spacing-{size}` for spacing. Don't guess hex codes.

- **The attribute vocabulary is shared:** `content-role`, `state`, `scale`, `variant`, `leading-visual`, `trailing-visual` work the same on every component.

- **State values map to colors:** `danger`=red, `warning`=yellow, `success`=green, `info`=blue. Always use `state`, never a color class.

- **Content-role values map to emphasis:** `primary`=filled/loud, `secondary`=outlined/medium, `ghost`=transparent/quiet, `accent`=brand secondary color.

- **Theme safety:** Use tokens, not raw values. Tokens automatically adapt to dark mode, custom themes, and accessibility modes. Raw hex codes break.

---

*Leantime Design System · Naming Conventions Reference · v1.0 · February 2026*
*Companion to: leantime-token-migration-spec.docx · Source: Component Updates Tracker spreadsheet*
