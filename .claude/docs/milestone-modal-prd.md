# PRD: Milestone Modal Redesign

## Overview

**Feature:** Enhanced Milestone Modal with Linked Entities & Properties Panel  
**Owner:** Gloria Folaron  
**Status:** Draft  
**Target Release:** TBD  

### Problem Statement

The current milestone modal in Leantime is a basic form-only view that shows title, status, dates, owner, and comments. Users cannot see the relationships between milestones and other entities (wiki docs, goals, tasks) without navigating away. This makes milestones feel disconnected and reduces their value as an organizing concept.

### Goals

1. Surface linked relationships (docs, goals, tasks) directly in the milestone view
2. Create visual consistency with the wiki/docs Properties panel pattern
3. Enable inline editing with auto-save for faster workflows
4. Provide at-a-glance progress and health metrics
5. Maintain modal format for consistency with existing UX patterns

### Non-Goals (Phase 1)

- Activity feed showing task completions, time logged, status changes (see Future Considerations)
- Inline entity linking (will navigate to linking interface)
- Changing from modal to drawer/page format
- Thumbnail previews for linked docs (text-only cards)

---

## User Stories

| As a... | I want to... | So that... |
|---------|--------------|------------|
| Project Manager | See all docs linked to a milestone | I can quickly access relevant documentation |
| Team Lead | View task completion status at a glance | I know milestone health without opening another view |
| Product Owner | See which goals a milestone supports | I understand strategic alignment |
| Any User | Edit milestone details inline | I can make quick updates without extra clicks |

---

## Design Specification

### Layout Structure

The modal uses a **two-column layout** inspired by the wiki Properties panel:

```
┌─────────────────────────────────────────────────────────────┐
│  [Close X]                                                   │
├───────────────────────────────────┬─────────────────────────┤
│  MAIN CONTENT (Left ~65%)         │  PROPERTIES (Right ~35%)│
│  - Title (editable)               │  - Status dropdown      │
│  - Tags                           │  - Owner dropdown       │
│  - Progress visualization         │  - Start date picker    │
│  - At-a-glance summary pills      │  - Due date picker      │
│  - Linked Docs section            │  - Dependency dropdown  │
│  - Linked Goals section           │  - Color picker         │
│  - Related Tasks section          │  - Last Saved timestamp │
│  - Discussion/Comments            │                         │
└───────────────────────────────────┴─────────────────────────┘
```

### Component Specifications

#### 1. Header Section

| Element | Behavior | Notes |
|---------|----------|-------|
| Title | Inline editable, click to edit | Auto-save on blur, show "Saving..." then "Saved" |
| Tags | Existing tag input pattern | Color picker integration |
| Close button | X icon, top right | Standard modal close |

#### 2. Properties Panel (Right Column)

| Property | Component | Data Source |
|----------|-----------|-------------|
| Status | Dropdown | `ticketRepository->getStateLabels()` |
| Owner | User dropdown with avatar | `projectRepo->getUsersAssignedToProject()` |
| Start Date | Date picker | `milestone->editFrom` |
| Due Date | Date picker | `milestone->editTo` |
| Depends On | Milestone dropdown | `ticketService->getAllMilestones()` |
| Color | Color picker (existing) | `milestone->tags` |
| Last Saved | Timestamp, auto-update | `milestone->modified` |

**Styling:**
- Panel background: `bg-gray-50` or similar subtle contrast
- Property rows: Label left-aligned, value right-aligned
- Consistent with wiki Properties panel CSS

#### 3. Progress Section

| Element | Calculation | Display |
|---------|-------------|---------|
| Progress bar | `(doneTickets / allTickets) * 100` | Filled bar with percentage |
| Date range | `editFrom` to `editTo` | "Jan 15 ━━━━━━━━━━━━ Feb 28" |
| Days remaining | `editTo - today` | "14 days remaining" or "3 days overdue" (red) |

#### 4. At-a-Glance Summary Pills

Three horizontal pill/cards showing:

| Pill | Data | Secondary Info |
|------|------|----------------|
| Tasks | Count of related tasks | "{done} done" |
| Goals | Count of linked goals | "{onTrack} on track" |
| Docs | Count of linked docs | — |

**Styling:**
- Rounded corners, subtle border
- Icon + count prominent
- Secondary text smaller, muted

#### 5. Linked Docs Section

| Element | Behavior |
|---------|----------|
| Section header | "📄 LINKED DOCS" with [Link →] button |
| Doc cards | Small cards with doc icon + title |
| Card click | Navigate to wiki article |
| Link button | Navigate to linking interface |
| Empty state | "No linked docs" with Link button |
| Layout | Horizontal scroll if > 5 items, or wrap |

**Card Design:**
```
┌─────────┐
│   📄    │  ← Document icon (fa-file-alt)
│  Title  │  ← Truncate at ~15 chars
└─────────┘
```

**Data Source:** Query wiki articles where `milestoneId = currentMilestone.id`

#### 6. Linked Goals Section

| Element | Behavior |
|---------|----------|
| Section header | "🎯 LINKED GOALS" with [Link →] button |
| Goal cards | Card with colored left border (status), title, progress |
| Card click | Navigate to goal canvas |
| Empty state | "No linked goals" with Link button |

**Card Design:**
```
┌─[status color border]──────────────────┐
│  Goal Title                            │
│  Canvas Name    [═══════░░] 70%        │
└────────────────────────────────────────┘
```

**Data Source:** Query goals via `goalcanvasService` where milestone relationship exists

#### 7. Related Tasks Section

| Element | Behavior |
|---------|----------|
| Section header | "✓ RELATED TASKS" with [View all →] link |
| Task list | Compact list, max 5 items shown |
| Task row | Status icon + title + status pill |
| View all link | Navigate to `/tickets/showAll?milestone={id}` |
| Empty state | "No tasks in this milestone" |

**Task Row Design:**
```
✓ Task title here                    [Done]
● Another task                       [In Progress]
○ Yet another task                   [To Do]
```

**Status Icons:**
- Done: ✓ (checkmark, green)
- In Progress: ● (filled circle, blue)
- To Do: ○ (empty circle, gray)

**Data Source:** `ticketRepository->getAllByMilestone()` limited to 5

#### 8. Discussion Section

Keep existing comments functionality, styled consistently:
- Avatar + input for new comment
- Threaded replies
- Timestamp + actions

---

## Technical Implementation

### Files to Modify

| File | Changes |
|------|---------|
| `app/Domain/Tickets/Templates/milestoneDialog.tpl.php` | Complete redesign with new layout |
| `app/Domain/Tickets/Controllers/EditMilestone.php` | Add data for linked entities |
| `app/Domain/Tickets/Hxcontrollers/Milestones.php` | Add HTMX endpoints for inline save |
| `app/Domain/Wiki/Repositories/Wiki.php` | Method to get articles by milestone |
| `app/Domain/Goalcanvas/Services/Goalcanvas.php` | Method to get goals by milestone |

### New Components to Create

| Component | Location | Purpose |
|-----------|----------|---------|
| `milestonePropertiesPanel.blade.php` | `Templates/partials/` | Right-side properties panel |
| `milestoneProgressBar.blade.php` | `Templates/partials/` | Progress visualization |
| `milestoneSummaryPills.blade.php` | `Templates/partials/` | At-a-glance metrics |
| `milestoneLinkedDocs.blade.php` | `Templates/partials/` | Docs section with cards |
| `milestoneLinkedGoals.blade.php` | `Templates/partials/` | Goals section with cards |
| `milestoneRelatedTasks.blade.php` | `Templates/partials/` | Tasks list section |

### Data Requirements

The controller needs to provide:

```php
$this->tpl->assign('milestone', $milestone);
$this->tpl->assign('statusLabels', $statusLabels);
$this->tpl->assign('users', $users);
$this->tpl->assign('milestones', $otherMilestones); // for dependency dropdown

// NEW DATA
$this->tpl->assign('linkedDocs', $wikiService->getArticlesByMilestone($milestoneId));
$this->tpl->assign('linkedGoals', $goalService->getGoalsByMilestone($milestoneId));
$this->tpl->assign('relatedTasks', $ticketService->getTasksByMilestone($milestoneId, limit: 5));
$this->tpl->assign('taskStats', [
    'total' => $totalTasks,
    'done' => $doneTasks,
    'inProgress' => $inProgressTasks
]);
$this->tpl->assign('goalStats', [
    'total' => $totalGoals,
    'onTrack' => $onTrackGoals
]);
```

### Auto-Save Implementation

Use HTMX for inline field updates:

```html
<input type="text" 
       name="headline" 
       value="{{ $milestone->headline }}"
       hx-post="{{ BASE_URL }}/hx/tickets/milestones/{{ $milestone->id }}/update"
       hx-trigger="blur changed"
       hx-swap="none"
       hx-indicator="#save-indicator" />

<span id="save-indicator" class="htmx-indicator">Saving...</span>
<span id="last-saved">Last saved: {{ format($milestone->modified)->diffForHumans() }}</span>
```

HTMX endpoint returns updated `Last Saved` timestamp.

---

## Design Decisions

| Question | Decision |
|----------|----------|
| Max items per section | 5 items max for Docs, Goals, and Tasks — show "View all" if more |
| Mobile behavior | Properties panel collapses into expandable section below main content |
| Empty state messaging | Generic text ("No linked docs", etc.) for now |

## Acceptance Criteria

### Must Have (P0)

- [ ] Modal displays with two-column layout (main content + properties panel)
- [ ] Title is editable inline with auto-save
- [ ] Properties panel shows: Status, Owner, Start Date, Due Date, Depends On, Color, Last Saved
- [ ] Progress bar displays with percentage and days remaining
- [ ] "At a Glance" pills show counts for Tasks, Goals, Docs
- [ ] Linked Docs section displays wiki articles linked to milestone
- [ ] Linked Goals section displays goals linked to milestone
- [ ] Related Tasks section displays up to 5 tasks with status
- [ ] All sections have appropriate empty states
- [ ] Discussion/comments section retained and functional
- [ ] "Link" buttons navigate to appropriate linking interfaces
- [ ] "View all" link on tasks navigates to filtered task list
- [ ] Modal closes properly and updates parent view if changes made

### Should Have (P1)

- [ ] Smooth transitions/animations on section expand/collapse
- [ ] Keyboard navigation support (tab through fields)
- [ ] Loading states for async data (linked entities)
- [ ] Error handling for failed saves with user feedback

### Nice to Have (P2)

- [ ] Drag-and-drop reordering of linked docs
- [ ] Quick-add task from milestone modal
- [ ] Keyboard shortcut to open milestone modal

### Implementation Note

**Potential Simplification:** If the Linked Goals and Related Tasks sections make the modal feel too heavy or cluttered during implementation, consider:
- Collapsing these sections by default (user clicks to expand)
- Showing only counts in the "At a Glance" pills and removing the detail sections
- Moving to a tabbed interface (Overview | Docs | Goals | Tasks)

Start with the full design and evaluate after seeing it live.

---

## Future Considerations (Phase 2+)

### Activity Feed

Add an activity section to the Properties panel showing:
- Task completions: "Johnny completed 'Design login flow'"
- Time logged: "Tina logged 4 hours on 'API Development'"
- Status changes: "Milestone moved to 'In Progress'"
- Comments added: "New comment from Sarah"

**Implementation Notes:**
- Would require new activity tracking table or leveraging existing audit log
- Consider HTMX polling or websockets for real-time updates
- Filterable by activity type

### Inline Entity Linking

Allow users to link docs/goals/tasks directly from the modal:
- Search/typeahead for entities
- Quick-link without navigation
- Unlink with confirmation

### Thumbnail Previews for Docs

Generate and display document thumbnails:
- First page preview for uploaded files
- Icon + excerpt for wiki articles
- Would require background processing

---

## Design Assets

Reference screenshots provided:
1. Goals Dashboard wireframe - card layout patterns
2. Task view wireframe - Files section pattern
3. Wiki Properties panel - target consistency pattern

---

## Open Questions

1. Should there be a "pin to top" for important linked docs?
2. If linked goals/tasks feels too heavy after implementation, consider: collapsing by default, or moving to a tabbed interface within the modal

---

## Changelog

| Date | Author | Changes |
|------|--------|---------|
| 2026-01-30 | Gloria Folaron | Initial draft |
