# Emboss ↔ Leantime Data Model Mapping

## The Core Translation

| Leantime Entity | Emboss Row Type | Visual Treatment |
|---|---|---|
| **Milestone** | `phase` | Colored thin bar spanning children, collapsible, sidebar pill with chevron |
| **Task** | `task` | Glass bar with progress fill, drag handles, status coloring |
| **Subtask** | `subtask` | Smaller glass bar (barHeight - 6px), indented, inherits parent color at 70% |

## What About Diamonds?

The diamond (`type: 'milestone'` in the current spec) becomes **optional deadline markers**. They represent a point-in-time checkpoint — not a container.

In Leantime terms, a diamond could represent:
- A due date for a milestone group (e.g., "Design Review deadline")
- A sprint boundary or release date
- An external dependency date

**Rename in the "+" menu:**
- ~~Add Milestone~~ → **Add Deadline** (creates a diamond row)
- "Add Milestone" → creates a `phase` (collapsible group), matching Leantime's terminology

## "+" Menu Options (Leantime Context)

| Menu Item | Creates | Emboss Row Type |
|---|---|---|
| **Add Milestone** | A new collapsible group | `phase` |
| **Add Task** | A task under the current milestone | `task` |
| **Add Deadline** | A diamond date marker | `milestone` (diamond) |

## Field Mapping

### Leantime Milestone → Emboss Phase Row

```typescript
{
  id: leantime.milestone.id,
  type: 'phase',
  name: leantime.milestone.headline,
  depth: 0,
  parentId: null,
  collapsed: false,
  hidden: false,
  start: dayOffset(leantime.milestone.editFrom),
  duration: daysBetween(editFrom, editTo),
  progress: computedFromChildren,  // average or weighted
  status: derivedFromChildren,
  dependencies: [],
  phaseColor: leantime.milestone.color || autoAssign,
  phaseName: leantime.milestone.headline,
  children: leantime.milestone.tasks.map(t => t.id)
}
```

### Leantime Task → Emboss Task Row

```typescript
{
  id: leantime.task.id,
  type: 'task',
  name: leantime.task.headline,
  depth: 1,
  parentId: leantime.task.milestoneId,
  collapsed: false,
  hidden: false,
  start: dayOffset(leantime.task.editFrom),
  duration: daysBetween(editFrom, editTo),
  progress: leantime.task.percentDone || 0,
  status: mapStatus(leantime.task.status),
  dependencies: leantime.task.dependingTicketId ? [leantime.task.dependingTicketId] : [],
  assignee: leantime.task.editorFirstname,
  assigneeColor: hashToColor(leantime.task.editorId)
}
```

### Leantime Subtask → Emboss Subtask Row

```typescript
{
  id: leantime.subtask.id,
  type: 'subtask',
  name: leantime.subtask.headline,
  depth: 2,
  parentId: leantime.subtask.parentTicketId,
  // ... same pattern, smaller visual treatment
}
```

## Status Mapping

| Leantime Status | Emboss Status |
|---|---|
| `NEW`, `OPEN`, `BLOCKED` | `upcoming` |
| `IN_PROGRESS`, `WAITING` | `active` |
| `DONE`, `CLOSED` | `done` |

## Implications for the Build

1. **Rename internally:** The `type: 'milestone'` row type keeps its diamond rendering, but in Leantime-facing UI it's called "Deadline"
2. **"Add Milestone" in Leantime creates a phase:** The "+" menu labels match Leantime vocabulary
3. **Phase progress is computed:** Roll up from children's progress, not set directly
4. **Phase dates are computed:** Span from earliest child start to latest child end
5. **The demo should use Leantime terminology** in the sidebar labels and creation menu

## What This Doesn't Change

- The Emboss engine is terminology-agnostic — it works with row types, not business labels
- The extension API stays the same
- Other integrations (Jira, Asana, Monday) can map their own terms to the same row types
- The diamond visual is still useful — it just serves a different purpose than "Leantime milestone"
