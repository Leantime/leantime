# Phase 2: Strategy Plugin

## Summary

The Strategy Plugin adds strategic intelligence, validation workflows, system integration, and advanced features to the logic model canvas. All plugin features render conditionally — the board must function fully without the plugin active.

**Prerequisite:** Phase 1 (Core Board) must be complete.

---

## 1. Plugin Detection Pattern

All plugin features check for plugin activation before rendering. Use the same conditional pattern as other Leantime plugins. In the prototype, this is represented by the `.plugin` CSS class on the workspace container.

```
if (plugin is NOT active):
    render core board only
if (plugin IS active):
    render core board + all plugin enhancements
```

---

## 2. Theory of Change Narrative

### Position
Horizontal bar above the stage columns, below the top bar. 24px margin between narrative and columns.

### Visual Treatment
- Background: white
- Left border: 3px solid `#00aa64` (accent green)
- Border radius: 8px
- Padding: 12px 20px
- Shadow: subtle (`0 1px 3px rgba(0,0,0,0.04)`)

### Content Format
```
**Theory of Change:** If we invest [Inputs text] and deliver [Activities text],
we produce [Outputs text], leading to [Outcomes text], ultimately creating [Impact text].
```

Each bracketed section uses the corresponding stage color:
- Inputs text: `#4A85B5`
- Activities text: `#3E937A`
- Outputs text: `#C09035`
- Outcomes text: `#8E6AAD`
- Impact text: `#2D7D5E`

Content is generated from item titles within each stage. AI can help refine.

### Empty Board Template
When a logic model has no items, the narrative becomes a fill-in-the-blank template:

> If we invest in *[inputs here]* and deliver on *[these activities]*, then we can produce *[these outputs]* because they will lead to *[these outcomes]* and make *[this impact]*.

Italicized placeholder text in each bracket. Teaches the framework while inviting participation.

---

## 3. Stage Health Badges

### Purpose
Indicate the quality of the causal connection BETWEEN stages (not within a stage). The badge on "Inputs" describes whether the assumption that inputs lead to activities is sound.

### Position
Top-right corner of the stage header, absolutely positioned (8px from top, 8px from right).

### Size
24px circle with 10px icon. Border: 1.5px solid in matching color.

### States

| State | Icon | Background | Text Color | Border | Meaning |
|-------|------|------------|------------|--------|---------|
| Strong | check | `#EAF5E8` | `#5a9e4f` | `#c8e0c2` | Assumptions validated |
| Warning | exclamation | `#FEF4E4` | `#c08a2e` | `#edd9ad` | Some assumptions untested |
| Gap | exclamation | `#FDEAEB` | `#BB1B25` | `#edb5b9` | Critical assumptions unvalidated |

### Hover Behavior
- Scale to 1.15x
- Shadow increases
- Popover appears below the badge

### Popover Content
```
┌─────────────────────────────┐
│ INPUTS → ACTIVITIES         │  ← 9px uppercase label
│                             │
│ Funding stable. Qualified   │  ← Assumption text
│ volunteers available.       │
│                             │
│ [Medium Risk]               │  ← Risk level tag (colored)
│                             │
│ Volunteer recruitment       │  ← Notes (10px, tertiary)
│ challenging in Q3           │
└─────────────────────────────┘
```

### No Badge on Impact
Impact (stage 5) has no health badge — there is no next stage to connect to.

### Data
Health badges are manually set by users through a settings interface or inline edit. The AI copilot (Phase 3) can suggest health status changes.

---

## 4. Progress Bars

### Definition
The progress bar = percentage of items with status **"Validated: Valid"** within the stage.

### Calculation
```
progress = (items with status "validated_valid") / (total items in stage) × 100%
```

Only "Validated: Valid" counts. Draft, In Review, On Hold, Validated: Invalid = 0%.

### Visual Treatment
- Height: 4px
- Position: below subtitle in stage header, full width with 16px horizontal padding
- Fill color: stage primary color
- Track color: `#eef0f2` (light gray)
- Border radius: 2px
- Fill transition: 500ms ease

### Opacity by State
- Active stage: full opacity (1.0)
- Inactive stage: 30% opacity (ambient, not competing)

### Hover Tooltip
On hover, show: `"2 of 4 items validated"` (or equivalent count).

---

## 5. Card Enhancements

### Status Pills
Visual badges on expanded cards showing the hypothesis status in a small colored pill. These are a visual enhancement of the core status — the data is already there from Phase 1, the pills add visual prominence.

| Pill | Color | Background |
|------|-------|------------|
| Validated | White text | `#75BB1B` green |
| In Progress | White text | `#1B75BB` blue |
| Draft | White text | `#aaaaaa` gray |
| High (priority) | White text | `#BB1B25` red |

Pills appear in the card footer, before the project link.

### Priority Indicators
Items can have a priority level (High, Medium, or none). In plugin mode, priority overrides the left border color on expanded cards:
- High priority: `#BB1B25` (red) instead of stage color
- Medium priority: `#fdab3d` (amber) instead of stage color
- No priority: stage color (default)

### Project Links
Items can link to Leantime projects. On the card footer, this shows as:
```
📁 Project Name
```
- Icon: folder (7px)
- Text: 10px, truncated with ellipsis at 150px max-width
- Background: 7% tint of accent color
- Color: accent blue (`#00456e`)
- Click: navigates to the linked project
- Only visible in plugin mode on active stage cards

### Item-Level Progress Indicators
For quantifiable items, a mini progress bar appears below the description:
```
87/120                    ████████░░░░  72%
```
- Current value / target value on the left
- 3px bar in the middle
- Percentage on the right (bold, accent green)
- Only visible in plugin mode on active stage cards

---

## 6. Export Dropdown

Replace the core Print button with an Export button (in plugin mode only).

### Dropdown
```
┌─────────────────────┐
│ 🖼  Export as PNG    │
│ 📄 Export as PDF    │
│ 🖨  Print           │
└─────────────────────┘
```

- PNG: high-resolution image capture of the full board
- PDF: formatted document with layout optimization
- Print: browser native print dialog

Core mode continues to show only the Print button.

---

## 7. Board Templates

Templates change stage titles, subtitles, and narrative template while keeping the five-stage structure.

### Available Templates

| Template | Stage 1 | Stage 2 | Stage 3 | Stage 4 | Stage 5 |
|----------|---------|---------|---------|---------|---------|
| **Standard Logic Model** (default) | Inputs | Activities | Outputs | Outcomes | Impact |
| Theory of Change | Problem | Root Causes | Interventions | Outcomes | Long-term Change |
| Results Framework | Resources | Processes | Deliverables | Results | Goals |
| Impact Pathway | Needs | Strategies | Milestones | Changes | Vision |
| Program Logic | Investments | Actions | Products | Effects | Impact |

### Subtitles per Template
Each template defines its own subtitle text for each stage. Define these as constants.

### Selection
- Selected during logic model creation
- Can be changed in model settings
- Changing template updates titles and subtitles but does NOT delete or move items

---

## 8. Snapshot Versioning

### Purpose
Save point-in-time snapshots of the logic model for grant reporting, board meetings, or milestone documentation.

### Behavior
- Manual save: user clicks "Save Snapshot" from model settings or export menu
- Snapshot captures: all items, all statuses, all health badges, progress bar state, narrative text
- Snapshots are read-only historical records
- Users can view and compare past snapshots

---

## 9. Activity Log Data Capture

**CRITICAL:** Begin capturing activity log data in Phase 2, even though the UI ships in Phase 3.

### Events to Capture

| Event | Data |
|-------|------|
| Item created | item_id, stage, title, created_by, timestamp |
| Item edited | item_id, field_changed, old_value, new_value, edited_by, timestamp |
| Item deleted | item_id, stage, title, deleted_by, timestamp |
| Status changed | item_id, old_status, new_status, changed_by, timestamp |
| Priority changed | item_id, old_priority, new_priority, changed_by, timestamp |
| Project linked | item_id, project_id, linked_by, timestamp |
| Project unlinked | item_id, project_id, unlinked_by, timestamp |
| Health badge changed | from_stage, old_health, new_health, changed_by, timestamp |
| Snapshot saved | snapshot_id, saved_by, timestamp |
| Template changed | old_template, new_template, changed_by, timestamp |

Store as JSON detail in the activity_log table (see `04-DATA-MODEL.md`).

---

## 10. Plugin Settings

| Setting | Type | Default | Description |
|---------|------|---------|-------------|
| Board template | Select (5 options) | Standard Logic Model | Framework template |
| Stakeholder visibility | Select | Project team | Who can view/edit |
| Notification preferences | Multi-select | Email on red | Alert triggers |
| Auto-link suggestions | Toggle | On | Suggest project links by title match |
| Validation workflow | Toggle | Off | Require sign-off for status transitions |
| Snapshot mode | Select | Manual | When to auto-save snapshots |
| Default assignee per stage | User select × 5 | Creator | Auto-assign new items |

---

## 11. Card Modal Additions (Plugin)

When plugin is active, the card modal (from Phase 1) gains additional sections:

### Evidence & Assumptions
- Section for attaching evidence supporting or contradicting the hypothesis
- Links to documents, data, research
- Feeds into health badge system

### Project Links
- Search and link Leantime projects
- Shows entity type (milestone, KPI, goal) in the modal — simplified to folder icon on the board card

### Progress Indicator
- For quantifiable items: current value, target value, unit
- Visual bar showing progress toward target

### Priority Selector
- High / Medium / None
- Affects card border color on the board
