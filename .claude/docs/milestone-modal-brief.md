# Milestone Modal Redesign — Implementation Brief

## What We're Building

Redesign the milestone modal from a basic form into a richer view showing linked entities (docs, goals, tasks) with a Properties side panel — matching the wiki article layout pattern.

## Current State → Target State

| Now | After |
|-----|-------|
| Form-only modal (title, dates, status, comments) | Two-column layout with Properties panel |
| No visibility into linked entities | Shows linked docs, goals, and tasks |
| Full page refresh on save | Inline editing with auto-save |

## Layout

```
┌────────────────────────────────────┬──────────────────┐
│  MAIN CONTENT                      │  PROPERTIES      │
│  • Title (editable)                │  • Status        │
│  • Progress bar + dates            │  • Owner         │
│  • Summary pills (counts)          │  • Start/Due     │
│  • Linked Docs (cards)             │  • Depends on    │
│  • Linked Goals (cards)            │  • Color         │
│  • Related Tasks (list, max 5)     │  • Last Saved    │
│  • Discussion                      │                  │
└────────────────────────────────────┴──────────────────┘
```

**Mobile:** Properties panel collapses into expandable section.

## Key Files

**Modify:**
- `app/Domain/Tickets/Templates/milestoneDialog.tpl.php` — main template
- `app/Domain/Tickets/Controllers/EditMilestone.php` — add linked entity data
- `app/Domain/Tickets/Hxcontrollers/Milestones.php` — HTMX endpoints for auto-save

**Reference for styling:**
- `app/Domain/Wiki/Templates/show.tpl.php` — Properties panel pattern to match

**Create new partials in `app/Domain/Tickets/Templates/partials/`:**
- `milestonePropertiesPanel.blade.php`
- `milestoneLinkedDocs.blade.php`
- `milestoneLinkedGoals.blade.php`
- `milestoneRelatedTasks.blade.php`

## Data Needed

```php
// Existing
$milestone, $statusLabels, $users, $milestones

// New queries needed
$linkedDocs = $wikiService->getArticlesByMilestone($milestoneId);
$linkedGoals = $goalService->getGoalsByMilestone($milestoneId);
$relatedTasks = $ticketService->getTasksByMilestone($milestoneId, limit: 5);
$taskStats = ['total' => X, 'done' => Y];
```

## Behavior Notes

1. **Inline editing** — Title and all Properties fields save on blur via HTMX, update "Last Saved" timestamp
2. **Max 5 items** per section (docs, goals, tasks) — show "View all →" link if more
3. **Link buttons** navigate to existing linking interfaces (not inline add)
4. **Cards click through** to the linked entity (doc → wiki, goal → canvas, task → ticket)

## If It Feels Too Heavy

The goals and tasks sections might be overkill. If so, options:
- Collapse those sections by default
- Show only counts in summary pills, remove detail sections
- Move to tabs: Overview | Docs | Goals | Tasks

Start with full design, evaluate after.

## Out of Scope (Phase 1)

- Activity feed (future phase)
- Inline entity linking
- Doc thumbnail previews

---

**Full PRD:** See `milestone-modal-prd.md` for complete specs, acceptance criteria, and component details.
