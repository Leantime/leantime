# Mobile Responsiveness Standards

**Adaptive Layout Patterns for Leantime**

| Field | Value |
|---|---|
| Product | Leantime |
| Document | Mobile Responsiveness Standards |
| Version | 1.0 |
| Date | February 23, 2026 |
| Author | Gloria Folaron |
| Status | Living Document |
| Purpose | Define how every view, component, and interaction adapts across screen sizes. Leantime is a desktop-first project management tool, but must be usable on tablets and phones for quick actions, status checks, and approvals on the go. |

---

## 1. Philosophy

### 1.1 Desktop-First, Mobile-Usable

Leantime is a project management tool. The primary work surface is desktop — users build project plans, manage budgets, write detailed descriptions, and analyze dashboards on large screens. But they also need to:

- Check status on their phone while commuting
- Approve a request from a tablet during a meeting
- Quickly add a comment or update a status from anywhere
- View a dashboard on a projector or conference room display

This means: **desktop is the authoring environment, mobile is the consumption and quick-action environment.** We don't try to make every desktop interaction work identically on a 375px screen. Instead, we prioritize the mobile use cases that matter and make those excellent.

### 1.2 What Mobile Must Support Well

| Use case | Priority | Notes |
|---|---|---|
| View project/program status | **Critical** | Dashboard, kanban, list views |
| Update ticket status | **Critical** | Quick status change, drag-to-column on tablet |
| Add comments | **Critical** | Text input + mention |
| View notifications | **Critical** | Notification list, mark as read |
| View and respond to assignments | **Critical** | "What's assigned to me" |
| Time tracking (log hours) | **High** | Quick timesheet entry |
| View calendar | **High** | Milestone and deadline overview |
| Basic navigation | **Critical** | Find projects, switch context |
| Create new ticket | **High** | Quick capture |
| Edit ticket details | **Medium** | Full editing is desktop-preferred |
| Resource allocation | **Low** | Desktop only — too data-dense for phone |
| Wizard flows | **Low** | Desktop only — multi-step complex forms |
| Canvas board editing | **Low** | Desktop only — spatial manipulation |
| Budget detail editing | **Low** | Desktop only — tabular data entry |

### 1.3 Progressive Disclosure on Small Screens

The same information exists at every breakpoint — but how much is immediately visible changes:

| Screen | Strategy |
|---|---|
| Desktop (1200px+) | Full detail. Side panels. Multi-column layouts. |
| Tablet (768-1199px) | Reduce columns. Collapse side panels to overlays. Slightly smaller text/padding. |
| Phone (< 768px) | Single column. Bottom navigation. Collapsible sections. Summary views that expand on tap. |

**Never hide critical information on mobile.** Instead, restructure it — summaries up top, details behind a tap.

---

## 2. Breakpoints

### 2.1 Breakpoint Scale

| Name | Min-width | Tailwind | Primary target |
|---|---|---|---|
| Phone | 0px | (default) | iPhone SE through iPhone Pro Max (375-430px) |
| Phone landscape | 480px | `tw:xs:` | Phone in landscape orientation |
| Tablet portrait | 768px | `tw:md:` | iPad Mini, iPad (768-834px) |
| Tablet landscape | 1024px | `tw:lg:` | iPad landscape, small laptops |
| Desktop | 1200px | `tw:xl:` | Standard laptop/desktop |
| Wide | 1440px | `tw:2xl:` | Large monitors |

### 2.2 How to Use

Leantime is desktop-first in design but mobile-first in CSS (Tailwind convention). Write the mobile styles as default, then override with responsive prefixes:

```html
<!-- Single column on phone, two columns on tablet, three on desktop -->
<div class="tw:grid tw:grid-cols-1 tw:md:grid-cols-2 tw:xl:grid-cols-3 tw:gap-4">
```

### 2.3 Breakpoint Rules

| Rule | Details |
|---|---|
| Never use arbitrary breakpoints | Only the six defined breakpoints |
| Test at actual device widths | 375px (iPhone SE), 390px (iPhone 14), 768px (iPad), 1024px (iPad landscape), 1280px (laptop), 1440px (desktop) |
| Content determines breakpoints, not the other way around | If a layout breaks at 900px, it needs a fix at `tw:md:` (768px), not a custom 900px breakpoint |
| Browser zoom at 200% must work | At 1280px viewport with 200% zoom, effective layout width is 640px — must still be usable |

---

## 3. Layout Patterns

### 3.1 Page Shell

The page shell (navigation + content area) adapts across breakpoints:

| Component | Desktop (1200px+) | Tablet (768-1199px) | Phone (< 768px) |
|---|---|---|---|
| Left sidebar nav | Visible, 220-260px wide | Collapsed to icons (60px) or hidden | Hidden, hamburger trigger |
| Top header | Sticky, full width | Sticky, full width | Sticky, simplified |
| Content area | Full width minus sidebar | Full width minus icon sidebar | Full width |
| Right panel (details) | Inline side panel, 320-400px | Overlay/drawer from right | Full-screen overlay |

### 3.2 Navigation on Mobile

| Element | Phone behavior |
|---|---|
| Primary navigation | Hamburger menu → slide-out drawer from left |
| Project switcher | In the drawer, or top header dropdown |
| User menu | Top-right avatar → dropdown |
| Breadcrumbs | Truncate to last 2 levels with "..." for ancestors |
| Tab bars (page-level) | Horizontally scrollable with fade edges |
| Bottom nav (optional) | For high-frequency actions: Home, My Work, Notifications, Timer |

### 3.3 Sidebar-to-Drawer Pattern

The left sidebar collapses in stages:

```
Desktop (1200px+):    [Full sidebar 240px] [Content ─────────────]
Tablet (768-1199px):  [Icons 60px] [Content ──────────────────────]
Phone (< 768px):      [Content ────────────────────────────────────]
                      [Hamburger ☰ triggers drawer overlay]
```

Drawer behavior:
- Slides in from left
- Backdrop overlay (`rgba(0,0,0,0.3)`)
- Close on backdrop tap, swipe left, or X button
- `aria-hidden="true"` when closed, focus trapped when open
- Escape key closes

### 3.4 Content Area Patterns

| Layout | Desktop | Tablet | Phone |
|---|---|---|---|
| **Multi-column grid** | 3-4 columns | 2 columns | 1 column |
| **Table** | Full table | Horizontally scrollable OR priority columns | Card list (see 3.5) |
| **Kanban board** | All columns visible | Horizontally scrollable | Single column with tab/swipe |
| **Dashboard widgets** | 2-3 column grid | 2 column grid | Stacked single column |
| **Form** | 2-column layout possible | Single column | Single column |
| **Detail + sidebar** | Content + side panel | Content + overlay panel | Content, panel is full-screen |
| **Canvas / flow** | Horizontal flow | Horizontally scrollable | Vertical stack or simplified |

### 3.5 Table-to-Card Transformation

Data tables don't work on phone screens. When a table has more than 3 columns, transform it on mobile:

**Desktop (table):**
```
| Name     | Status    | Due Date   | Assigned | Priority |
|----------|-----------|------------|----------|----------|
| Task A   | In Prog   | Mar 15     | MJ       | High     |
| Task B   | Done      | Mar 10     | GF       | Low      |
```

**Phone (card list):**
```
┌──────────────────────────┐
│ Task A                   │
│ In Progress · Mar 15     │
│ MJ · High                │
└──────────────────────────┘
┌──────────────────────────┐
│ Task B                   │
│ Done · Mar 10            │
│ GF · Low                 │
└──────────────────────────┘
```

Pattern:
- Row 1: Title/name (full width, bold)
- Row 2: Status + primary date
- Row 3: Assignee + secondary info
- Tap card to expand or navigate to detail

```html
<!-- Responsive table/card -->
<div class="tw:hidden tw:md:block">
  <!-- Full table for tablet+ -->
  <table>...</table>
</div>
<div class="tw:block tw:md:hidden">
  <!-- Card list for phone -->
  @foreach($items as $item)
    <div class="tw:p-3 tw:border-b tw:border-[var(--color-border-light)]">
      <div class="tw:font-semibold tw:text-sm">{{ $item->title }}</div>
      <div class="tw:text-xs tw:text-[var(--color-text-muted)] tw:mt-1">
        {{ $item->status }} · {{ $item->dueDate }}
      </div>
    </div>
  @endforeach
</div>
```

### 3.6 Canvas and Flow Layouts

Canvas boards (Logic Model, resource allocation) are inherently spatial. On small screens:

| Approach | When to use |
|---|---|
| **Horizontal scroll** | Tablet — user can swipe through stages. Add scroll indicators. |
| **Vertical stack** | Phone — stages become accordion sections, one expanded at a time |
| **Summary only** | Phone — show summary metrics, link to desktop for full editing |
| **Simplified view** | Phone — show a card list per stage, omit spatial relationships |

Logic Model specifically:
- Desktop: horizontal 5-column flow (current design)
- Tablet landscape: horizontal scroll with snap points per stage
- Tablet portrait: 2-column wrap (Inputs+Activities, Outputs+Outcomes, Impact full-width)
- Phone: vertical accordion — tap stage header to expand, only one open at a time

### 3.7 Modal Behavior on Mobile

| Screen | Modal behavior |
|---|---|
| Desktop | Centered floating dialog with backdrop |
| Tablet | Same, but max-width constrained to 90% viewport |
| Phone | **Full-screen sheet** sliding up from bottom |

Phone modal (bottom sheet) pattern:
- Slides up from bottom edge
- Drag handle at top for dismiss gesture
- Full viewport width, 90-100% viewport height
- Close button (X) always visible top-right
- Back/cancel at top-left
- Primary action button fixed at bottom (above keyboard if input focused)

```html
<!-- Responsive modal -->
<dialog class="
  tw:w-full tw:h-full tw:m-0 tw:rounded-none
  tw:md:w-[600px] tw:md:h-auto tw:md:m-auto tw:md:rounded-xl
  tw:md:max-h-[85vh]
">
```

---

## 4. Touch Interactions

### 4.1 Touch Target Sizes

Reiterated from 14-DESIGN-TOKENS.md but critical here:

| Element | Minimum touch target | Notes |
|---|---|---|
| Primary buttons | 44x44px | Full tap area, not just visual bounds |
| Icon buttons | 44x44px | Extend hit area with padding |
| List items / rows | 44px height minimum | Entire row tappable |
| Close / dismiss | 44x44px | Even if the X icon is 12px, hit area is 44px |
| Checkboxes / toggles | 44x44px | Include label in tap area |
| Links in text | 44px vertical spacing | Enough space between links to not mis-tap |
| Dropdown items | 44px height | Finger-friendly item height |

### 4.2 Touch Gestures

| Gesture | Usage | Implementation |
|---|---|---|
| Tap | Primary interaction | Standard click events |
| Long press | Context menu (optional) | `@touchstart` + timeout, cancel on move |
| Swipe left on row | Quick action (archive, delete) | Reveal action buttons behind row |
| Swipe right on row | Quick action (complete, approve) | Reveal action buttons behind row |
| Pull to refresh | Refresh list/dashboard | Native browser or HTMX reload |
| Pinch to zoom | Disabled on canvas views | `touch-action: pan-x pan-y` (no zoom) |
| Swipe between tabs | Tab navigation | Scroll snap on tab content |

### 4.3 Hover States on Touch

Hover doesn't exist on touch devices. Every interaction that relies on hover must have a touch alternative:

| Desktop (hover) | Mobile (touch) |
|---|---|
| Tooltip on hover | Tap to show, tap elsewhere to dismiss |
| Dropdown on hover | Tap to toggle open/close |
| Card hover elevation | No visual change (or use active/pressed state) |
| Row actions revealed on hover | Swipe to reveal, or tap row to show actions |
| Preview on hover | Tap to navigate directly |

**Rule:** Never put critical functionality behind hover-only. Every hover interaction must be reachable by tap.

### 4.4 Keyboard on Mobile

When an input field is focused on mobile, the virtual keyboard covers ~50% of the screen.

| Issue | Solution |
|---|---|
| Submit button hidden by keyboard | Fix submit button above keyboard (`position: sticky; bottom: 0`) or use `env(safe-area-inset-bottom)` |
| Active field scrolled off screen | `scrollIntoView({ block: 'center' })` on focus |
| Form too long to scroll while typing | Keep forms short on mobile. Use multi-step if needed. |
| Dropdown behind keyboard | Position dropdown above input when near bottom of viewport |

---

## 5. Typography on Mobile

### 5.1 Scale Adjustments

The type scale from 14-DESIGN-TOKENS.md stays the same — we don't shrink fonts for mobile. The minimum body text (14px) is already appropriate for phone screens.

Adjustments:

| Token | Desktop | Phone | Reason |
|---|---|---|---|
| Display | 22px | 20px | Prevent overflow on narrow screens |
| Heading L | 18px | 17px | Slight reduction acceptable |
| All others | Same | Same | 14px body is fine on mobile |

```html
<h1 class="tw:text-xl tw:md:text-[22px]">Page Title</h1>
```

### 5.2 Line Length on Mobile

On desktop, max line length is ~70-80 characters. On a 375px phone with 16px padding each side, the content width is ~343px. At 14px, this gives ~40-45 characters per line — which is fine for mobile readability. No action needed.

### 5.3 Text Truncation

On small screens, long titles and labels need truncation:

```html
<!-- Truncate with ellipsis -->
<span class="tw:truncate tw:max-w-[200px] tw:md:max-w-none">
  {{ $longTitle }}
</span>

<!-- Multi-line clamp (2 lines max) -->
<p class="tw:line-clamp-2 tw:md:line-clamp-none">
  {{ $description }}
</p>
```

**Rules:**
- Never truncate the primary identifier (ticket title, project name) below readability — use 2-line clamp if needed
- Always show full text somewhere accessible (tooltip on desktop, tap to expand on mobile)
- Truncate supplementary text (descriptions, paths, URLs) aggressively
- Breadcrumbs: show last 2 segments, "..." for the rest

---

## 6. Spacing on Mobile

### 6.1 Spacing Reductions

The 4px spacing scale from 14-DESIGN-TOKENS.md applies at all breakpoints. But some layout-level spacings reduce on mobile:

| Context | Desktop | Phone | Tailwind pattern |
|---|---|---|---|
| Page padding horizontal | 24px (tw:px-6) | 16px (tw:px-4) | `tw:px-4 tw:md:px-6` |
| Page padding vertical | 32px (tw:py-8) | 20px (tw:py-5) | `tw:py-5 tw:md:py-8` |
| Section gap | 24px (tw:gap-6) | 16px (tw:gap-4) | `tw:gap-4 tw:md:gap-6` |
| Card inner padding | 16px (tw:p-4) | 12px (tw:p-3) | `tw:p-3 tw:md:p-4` |
| Card gap | 12px (tw:gap-3) | 8px (tw:gap-2) | `tw:gap-2 tw:md:gap-3` |

### 6.2 Safe Area Insets

Devices with notches, dynamic islands, and home indicators need safe area handling:

```css
/* For fixed bottom elements (bottom nav, sticky buttons) */
padding-bottom: env(safe-area-inset-bottom, 0);

/* For full-screen overlays */
padding-top: env(safe-area-inset-top, 0);
padding-left: env(safe-area-inset-left, 0);
padding-right: env(safe-area-inset-right, 0);
```

```html
<!-- Bottom fixed bar with safe area -->
<div class="tw:fixed tw:bottom-0 tw:left-0 tw:right-0 tw:bg-[var(--color-bg-card)]
            tw:border-t tw:border-[var(--color-border-default)]
            tw:pb-[env(safe-area-inset-bottom)]">
  <div class="tw:p-3 tw:flex tw:gap-2">
    <button>Cancel</button>
    <button>Save</button>
  </div>
</div>
```

**Meta tag required:**
```html
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
```

---

## 7. Images and Media

### 7.1 Responsive Images

| Rule | Implementation |
|---|---|
| Max width 100% | `tw:max-w-full tw:h-auto` on all images |
| Lazy loading | `loading="lazy"` on images below the fold |
| Aspect ratio preservation | `tw:aspect-video` or `tw:aspect-square` containers |
| Avatar sizes | Same across breakpoints (18/28/36px) — already small enough |

### 7.2 Charts and Data Visualization

| Chart type | Desktop | Tablet | Phone |
|---|---|---|---|
| Bar chart | Full width, labels visible | Full width, labels may rotate 45° | Full width, horizontal bars instead of vertical |
| Line chart | Full width, all data points | Full width, fewer labels | Full width, simplified (fewer data points) |
| Pie/donut | 200-300px diameter | 200px diameter | 180px diameter, legend below (not beside) |
| Progress bars | Inline with labels | Same | Same — works at any width |
| Sparklines | Inline | Same | Same — inherently compact |

**Rule:** Charts must have text alternatives. On mobile where chart detail is reduced, ensure the key numbers are shown as text above/below the chart.

---

## 8. Performance on Mobile

### 8.1 Mobile-Specific Performance Rules

| Rule | Why | Implementation |
|---|---|---|
| No layout shift on load | Mobile viewports magnify shifts | Set explicit dimensions on images, embeds, dynamic containers |
| Minimize paint on scroll | Mobile GPUs are weak | Avoid `box-shadow` changes on scroll. Use `will-change` sparingly. |
| Lazy load below-fold content | Mobile bandwidth is variable | HTMX `hx-trigger="revealed"` for sections below the fold |
| Reduce initial payload | 3G connections still exist | Critical content first, enhance with HTMX partials |
| Avoid large DOM | Mobile browsers have less memory | Virtualize long lists (100+ items). Paginate rather than infinite scroll for data tables. |
| Debounce resize/scroll handlers | Mobile fires these rapidly | 100-200ms debounce |
| Touch event passive listeners | Prevents scroll jank | `{ passive: true }` on scroll and touch listeners |

### 8.2 HTMX on Mobile

HTMX is well-suited for mobile — it sends small HTML fragments instead of full JSON payloads that need client-side rendering. But:

| Consideration | Rule |
|---|---|
| Loading indicators | Always show a spinner or skeleton on mobile — network latency is higher |
| Error recovery | Network drops happen more on mobile. Show "retry" button on failed requests. |
| Offline state | Detect `navigator.onLine` and show persistent banner when offline |
| Request size | Keep HTMX responses small — mobile bandwidth is limited |
| Swap animations | Disable on `prefers-reduced-motion` AND on slow connections (use `navigator.connection.effectiveType`) |

---

## 9. Component-Specific Responsive Patterns

### 9.1 Header / Top Bar

| Element | Desktop | Tablet | Phone |
|---|---|---|---|
| Logo + app name | Visible | Logo only | Logo only (smaller) |
| Search | Visible input field | Icon that expands to full-width input | Icon that opens full-screen search overlay |
| Notifications bell | Icon + count badge | Same | Same |
| User avatar + menu | Avatar + name + dropdown | Avatar + dropdown | Avatar + dropdown |
| Page title | In header or breadcrumb area | Same | Truncated, single line |

### 9.2 Kanban Board

| Element | Desktop | Tablet | Phone |
|---|---|---|---|
| Columns | All visible side by side | Horizontally scrollable with snap | Single column with column tab selector |
| Column width | 280-320px | 260px | Full viewport width minus padding |
| Card detail | Click opens side panel | Click opens overlay | Click navigates to full-screen detail |
| Drag and drop | Full drag between columns | Tap + select target column | Tap card → action menu → "Move to..." |
| Column header | Sticky top | Sticky top | Fixed tab bar |
| WIP limits | Shown in column header | Same | Shown in tab label |

Phone kanban pattern:
```
[To Do ▼] [In Progress ▼] [Done ▼]     ← tab selector, scrollable
┌──────────────────────────────────┐
│ Card 1                           │    ← cards for selected column
│ Status · Assignee · Due          │
└──────────────────────────────────┘
┌──────────────────────────────────┐
│ Card 2                           │
│ Status · Assignee · Due          │
└──────────────────────────────────┘
```

### 9.3 Forms

| Element | Desktop | Tablet | Phone |
|---|---|---|---|
| Layout | 2-column where logical | Single column | Single column |
| Labels | Above input (not beside) | Same | Same |
| Select dropdowns | Native or custom | Native (better touch) | **Always native `<select>`** |
| Date pickers | Custom calendar widget | Custom or native | **Native `<input type="date">`** |
| Multi-select | Custom tag input | Same | Bottom sheet with checkboxes |
| Rich text editor | Full toolbar | Condensed toolbar | Minimal toolbar (bold, italic, list, link) |
| Submit button | Bottom of form, right-aligned | Same | Full-width, sticky at bottom |
| Form steps (wizard) | Progress bar + side navigation | Progress bar only | Step counter "2 of 5" + next/back |

**Phone form rule:** Use native form controls (`<select>`, `<input type="date">`, `<input type="time">`) whenever possible on phone. They invoke the OS-native picker which is better for touch than any custom widget.

### 9.4 Notifications Panel

| Element | Desktop | Tablet | Phone |
|---|---|---|---|
| Container | Dropdown from bell icon, 380px wide | Same | Full-screen overlay |
| Item height | Compact (60-70px) | Same | Slightly taller (comfortable tap target) |
| Mark as read | Hover → show mark-read icon | Same | Swipe left to mark read |
| Group by | Today / Earlier / This Week | Same | Same |
| Empty state | "All caught up" + icon | Same | Same |

### 9.5 Dashboard Widgets

| Widget type | Desktop | Tablet | Phone |
|---|---|---|---|
| Metric card (number + label) | 3-4 across | 2 across | 2 across (compact) or 1 across |
| Chart widget | 2 across | 1-2 across | 1 across, full width |
| Activity feed | Side panel or column | Below main content | Below, limited to 5 items + "show more" |
| Quick actions | Button row | Same | Sticky bottom bar |

### 9.6 Logic Model Flow

Detailed breakpoint behavior for the Logic Model specifically:

| Breakpoint | Layout | Behavior |
|---|---|---|
| Desktop 1200px+ | 5-column horizontal flow | All stages visible, one active/expanded |
| Tablet landscape 1024px | Horizontal scroll, 5 columns | Scroll snap per stage, swipe to navigate |
| Tablet portrait 768px | 2x2 + 1 grid | Inputs+Activities row, Outputs+Outcomes row, Impact full-width |
| Phone < 768px | Vertical accordion | Stage headers always visible, tap to expand one at a time |

Phone accordion pattern:
```
┌────────────────────────────┐
│ ▶ Inputs (4)               │  ← collapsed, shows count
├────────────────────────────┤
│ ▼ Activities (3)           │  ← expanded
│   ┌────────────────────┐   │
│   │ Reading assessments │   │
│   │ Status: In Progress │   │
│   └────────────────────┘   │
│   ┌────────────────────┐   │
│   │ 1-on-1 tutoring    │   │
│   │ Status: Validated   │   │
│   └────────────────────┘   │
│   ┌────────────────────┐   │
│   │ Family literacy     │   │
│   │ Status: Draft       │   │
│   └────────────────────┘   │
│   [+ Add activity]         │
├────────────────────────────┤
│ ▶ Outputs (3)              │
├────────────────────────────┤
│ ▶ Outcomes (3)             │
├────────────────────────────┤
│ ▶ Impact (2)               │
└────────────────────────────┘
```

### 9.7 Resource Allocation View

Resource allocation is desktop-only for editing. On mobile, show a read-only summary:

| Breakpoint | View |
|---|---|
| Desktop 1200px+ | Full canvas table with fill-up containers, editable |
| Tablet landscape 1024px | Simplified table, horizontally scrollable, editable |
| Tablet portrait 768px | Card list per person with allocation bars, read-only |
| Phone < 768px | Summary only — total people, total budget, top-line utilization. Link to desktop for detail. |

---

## 10. Orientation Handling

### 10.1 Landscape vs. Portrait

| Rule | Details |
|---|---|
| Support both orientations | Never lock orientation (except for specific canvas views if needed) |
| Landscape phone ≈ tablet portrait | At 667px wide (iPhone landscape), treat like narrow tablet |
| Avoid layout shift on rotation | Use viewport-relative units and flex/grid, not fixed pixel widths |

### 10.2 When Landscape Helps

| Feature | Landscape benefit |
|---|---|
| Kanban board | More columns visible |
| Tables | More columns visible without scroll |
| Canvas / Logic Model | More of the horizontal flow visible |
| Timeline / Gantt | More date range visible |
| Forms | Not helpful — stick to single column |

---

## 11. Accessibility on Mobile

### 11.1 Mobile-Specific Accessibility

These are in addition to the base accessibility standards in 14-DESIGN-TOKENS.md:

| Concern | Rule |
|---|---|
| Screen reader (VoiceOver/TalkBack) | All touch targets must be accessible via screen reader gestures (swipe to navigate) |
| Focus order on mobile | Must follow visual order. Drawer/overlay must trap focus when open. |
| Zoom | Never disable zoom. `user-scalable=yes` always. |
| Dynamic type (iOS) | Respect system text size settings where possible |
| Reduced motion | Applies doubly on mobile — animations cause more nausea on moving devices |
| Color inversion (iOS smart invert) | Images should have `data-smart-invert-ignore` if color-meaningful |
| Voice Control (iOS) | Buttons need visible labels or aria-label for voice targeting |
| Switch Control | All interactions reachable via single switch (tab + activate) |

### 11.2 Viewport Meta Tag

```html
<!-- Required for proper mobile rendering -->
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

<!-- NEVER do this: -->
<meta name="viewport" content="... user-scalable=no ...">   <!-- Blocks zoom accessibility -->
<meta name="viewport" content="... maximum-scale=1 ...">    <!-- Same problem -->
```

---

## 12. Testing Requirements

### 12.1 Device Matrix

Test at minimum:

| Device / Width | Priority | What to test |
|---|---|---|
| 375px (iPhone SE) | **Critical** | Smallest common phone — if it works here, it works on bigger phones |
| 390px (iPhone 14/15) | **Critical** | Most common phone width |
| 430px (iPhone Pro Max) | High | Largest phone — verify it doesn't look stretched |
| 768px (iPad Mini/iPad portrait) | **Critical** | Tablet portrait — major layout shift point |
| 1024px (iPad landscape) | High | Tablet landscape — sidebar collapse point |
| 1200px (laptop) | **Critical** | Desktop layout kicks in |
| 1440px (external monitor) | Medium | Wide layout verification |
| 1280px at 200% zoom | **Critical** | Effective 640px — accessibility requirement |

### 12.2 Test Checklist Per Component

- [ ] Renders correctly at 375px width
- [ ] Renders correctly at 768px width
- [ ] Renders correctly at 1200px width
- [ ] No horizontal overflow / scrollbar at any breakpoint
- [ ] Touch targets meet 44px minimum on phone/tablet
- [ ] All hover interactions have touch alternatives
- [ ] Modals become bottom sheets on phone
- [ ] Tables become card lists on phone
- [ ] Forms use native inputs on phone where appropriate
- [ ] Loading states show on mobile (higher latency expected)
- [ ] Text doesn't overflow containers — truncation or wrapping works
- [ ] Fixed/sticky elements respect safe area insets
- [ ] Orientation change doesn't break layout
- [ ] Virtual keyboard doesn't hide the active input or submit button
- [ ] Screen reader can navigate all content (VoiceOver swipe test)

### 12.3 Browser Testing

| Browser | Priority |
|---|---|
| Safari iOS (latest) | **Critical** — many iOS-specific layout quirks |
| Chrome Android (latest) | **Critical** — most common Android browser |
| Chrome Desktop | **Critical** — development primary |
| Firefox Desktop | High |
| Safari Desktop | High — WebKit differences |
| Edge Desktop | Medium |
| Samsung Internet | Medium — significant Android market share |

### 12.4 Common Mobile Bugs to Watch For

| Bug | Cause | Fix |
|---|---|---|
| 100vh is too tall on mobile | Mobile browser chrome (address bar) eats viewport height | Use `100dvh` (dynamic viewport height) instead of `100vh` |
| Sticky footer overlaps content | Content doesn't account for fixed bottom element | Add `padding-bottom` equal to footer height + safe area |
| Tap delay (300ms) | Old mobile browsers wait to detect double-tap zoom | `touch-action: manipulation` on interactive elements |
| Input zoom on iOS | iOS zooms in when font-size < 16px on inputs | Ensure input font-size is >= 16px on mobile |
| Rubber-band scroll on iOS | Body scroll bounces past edges | `overscroll-behavior: none` on scroll containers |
| Position fixed inside transform | Fixed elements don't work inside transformed parents | Move fixed elements outside transformed containers |
| :hover sticky on touch | Hover state persists after tap on mobile | Use `@media (hover: hover)` for hover-only styles |

```css
/* Fix: hover-only styles that don't stick on touch */
@media (hover: hover) {
  .card:hover {
    box-shadow: var(--shadow-md);
  }
}
```

---

## 13. Audit Methodology

### 13.1 What to Search For

```bash
# Fixed pixel widths that will overflow on mobile
grep -rn "width:\s*[0-9]\{3,\}px" app/Views/ --include="*.blade.php" --include="*.css" | head -30

# Hardcoded heights that may cause issues
grep -rn "height:\s*100vh" app/Views/ --include="*.blade.php" --include="*.css" | head -20

# Missing responsive prefixes on grid/flex layouts
grep -rn "grid-cols-[2-9]\|grid-cols-1[0-2]" app/Views/ --include="*.blade.php" | \
  grep -v "tw:md:\|tw:lg:\|tw:xl:" | head -20

# Hover-only interactions (no touch alternative)
grep -rn ":hover\|@mouseenter\|@mouseover" app/Views/ --include="*.blade.php" --include="*.css" | \
  grep -v "@media.*hover" | head -20

# Viewport zoom disabled (accessibility violation)
grep -rn "user-scalable=no\|maximum-scale=1" app/Views/ --include="*.blade.php" | head -10

# Missing viewport meta tag
grep -rL "viewport" app/Views/Templates/layouts/ --include="*.blade.php"

# Fixed positioning without safe area
grep -rn "position:\s*fixed\|tw:fixed" app/Views/ --include="*.blade.php" | \
  grep -v "safe-area\|env(" | head -20
```

### 13.2 Reporting Format

Same format as 14-DESIGN-TOKENS.md section 11.3:

```
## Sweep: Mobile Responsiveness
### Files checked: N
### Issues found: N (critical / minor / flagged)

#### Critical (broken on mobile, inaccessible, or overflow)
- file.blade.php:42 — grid-cols-3 without responsive prefix, overflows on phone
- file.blade.php:67 — hover-only dropdown, no touch alternative
- layout.blade.php:5 — viewport meta has maximum-scale=1 (blocks zoom)

#### Minor (suboptimal but functional)
- file.blade.php:120 — 100vh used, should be 100dvh for mobile browser chrome
- file.blade.php:135 — touch targets 32px, should be 44px minimum

#### Flagged (needs design decision)
- canvas.blade.php:200 — spatial canvas on phone. Summary view or vertical accordion?
```

---

## Changelog

| Date | Change | Author |
|---|---|---|
| 2026-02-23 | v1.0 — initial mobile responsiveness standards: philosophy, breakpoints, layout patterns, navigation, touch interactions, typography, spacing, safe areas, images/charts, performance, component-specific patterns (kanban, forms, notifications, dashboard, logic model, resources), orientation, mobile accessibility, testing matrix, common bugs, audit methodology | GF |
