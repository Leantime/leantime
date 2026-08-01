# Card Modal & Detail View

## Status

**Phase 1:** Core modal sections defined and ready for implementation.
**Phase 2:** Plugin modal additions defined. Detailed interaction design for evidence, project links, and progress to be finalized during Phase 2 development.

---

## 1. Opening Behavior

- Click an item card on the board → modal opens
- Use Leantime's existing modal/slide-over system
- Modal should not close the active stage or change board state

---

## 2. Core Modal Layout (Phase 1)

```
┌─────────────────────────────────────────────────┐
│  ✕                                               │
│                                                   │
│  ┌─ Stage pill ─┐   ┌─ Status dropdown ──┐  ⋯  │
│  │ Inputs       │   │ In Review ▾        │      │
│  └──────────────┘   └────────────────────┘      │
│                                                   │
│  Title (editable)                                │
│  ─────────────────────────────────────           │
│  $250K annual budget                             │
│                                                   │
│  Description                                     │
│  ─────────────────────────────────────           │
│  Federal Title I funding for after-school        │
│  programming across 3 community centers.         │
│  [Rich text editor]                              │
│                                                   │
│  ─────────────────────────────────────           │
│  Assigned to: [GF ▾]     Created: Feb 1, 2026   │
│                                                   │
│  ─── Comments ─────────────────────────          │
│  GF: Budget confirmed by finance team.           │
│  MJ: Need to verify carry-over amount.           │
│  [Add comment...]                                │
│                                                   │
│  ─── Attachments ──────────────────────          │
│  📎 Budget_Approval_2026.pdf                     │
│  [Add attachment]                                │
│                                                   │
└─────────────────────────────────────────────────┘
```

### Sections

1. **Header bar**
   - Stage label: colored pill (read-only), shows which stage this item belongs to
   - Hypothesis status: clickable dropdown pill (same as board card)
   - Three-dot menu: Edit title, Move to different stage, Delete item
   - Close button (✕)

2. **Title**
   - Editable inline (click to edit)
   - 18-20px, bold
   - Auto-saves on blur

3. **Description**
   - Rich text editor (match Leantime's existing editor)
   - Markdown supported
   - Auto-saves or explicit save button (match existing pattern)

4. **Meta row**
   - Assignee selector (dropdown of project team members)
   - Created date (read-only)
   - Last modified date (read-only)

5. **Comments**
   - Threaded discussion
   - Uses Leantime's existing comment system
   - Module: `logic_model_item`, entity: item ID

6. **Attachments**
   - File upload area
   - Uses Leantime's existing attachment system
   - Module: `logic_model_item`, entity: item ID

---

## 3. Plugin Modal Additions (Phase 2)

When the Strategy Plugin is active, additional sections appear in the modal:

### Evidence & Assumptions
Position: below description, above meta row.

```
│  ─── Evidence & Assumptions ───────────          │
│                                                   │
│  📄 Title I Grant Approval Letter                │
│     Uploaded Feb 1 — confirms $250K              │
│                                                   │
│  🔗 District Budget Dashboard                    │
│     External link — shows allocation              │
│                                                   │
│  [+ Add evidence]                                │
│                                                   │
│  Assumptions:                                    │
│  "Federal funding continues at current levels    │
│   through FY2027"                                │
│  Risk: Medium                                    │
```

- List of evidence items (documents, links, notes)
- Each with a title and brief description
- Assumption text (feeds into health badges)
- Risk level tag

### Project Links
Position: below evidence section.

```
│  ─── Linked Projects ─────────────────           │
│                                                   │
│  📁 Secure Funding Q1                            │
│     Milestone · 3 tasks · 67% complete           │
│     [View project →]                             │
│                                                   │
│  [🔍 Link a project...]                          │
```

- Shows linked projects with entity type detail (milestone, KPI, goal)
- Click to navigate to the project
- Search interface to link new projects
- On the board card, this simplifies to just `📁 Project Name`

### Progress Indicator
Position: below title (for quantifiable items only).

```
│  Progress                                        │
│  87 / 120 students         ████████░░░░  72%    │
│  [Edit target]                                   │
```

- Current value (editable)
- Target value (editable)
- Unit label
- Visual bar
- Only shown when item has a target value defined

### Priority Selector
Position: in the header bar or meta row.

```
│  Priority: [High ▾]                              │
```

- Options: None, Medium, High
- Affects border color on the board card

---

## 4. Move Item Between Stages

From the three-dot menu → "Move to..." → dropdown shows all five stages → select target stage → item moves immediately.

The item retains its status, description, comments, and attachments when moved. Sort order is set to the end of the target stage.

---

## 5. Delete Item

From the three-dot menu → "Delete" → confirmation dialog → item removed.

Deletion is logged in the activity log (Phase 2).

---

## 6. Keyboard Shortcuts (Modal)

| Key | Action |
|-----|--------|
| Escape | Close modal |
| Tab | Move between sections |
| Cmd/Ctrl + Enter | Save and close |
