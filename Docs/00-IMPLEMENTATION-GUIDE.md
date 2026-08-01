# Logic Model Canvas — Implementation Guide

## Overview

The Logic Model Canvas is a new board type in Leantime that provides a five-stage causal chain view for program planning. It ships in **three phases** that must be built sequentially. Each phase produces a working, shippable product.

## Reference Prototype

The definitive visual reference is `logic-model-v11.html`. It includes both core and plugin modes toggled by header buttons. Use it as the source of truth for spacing, colors, transitions, and hierarchy.

---

## Phase 1: Core Board

**Goal:** A functional logic model canvas that ships free with Leantime. Users can create a board, add items to five fixed stages, set hypothesis statuses, and navigate between stages.

**Files:** `01-CORE.md`

**Depends on:** Existing Leantime canvas board architecture (Lean Canvas, Value Canvas, SWOT). Extend the same base patterns — same card component, same modal system, same comment/attachment infrastructure.

**Definition of Done:**
- [ ] New board type selectable from project board menu
- [ ] Five fixed stages render with correct colors, icons, titles, subtitles
- [ ] Click-to-focus stage interaction with pricing-page visual hierarchy
- [ ] Item cards display with title, description, hypothesis status dropdown, comments, attachments, avatar
- [ ] Inactive stages show compact dot + title rows
- [ ] Count badge on stage headers (visible on hover only)
- [ ] Logic model selector dropdown (create, switch between models)
- [ ] Print button (browser native)
- [ ] Card modal opens on item click with edit capabilities
- [ ] Stage titles, colors, order are locked (not editable)

**Do NOT build in Phase 1:**
- Theory of change narrative
- Health badges
- Progress bars
- Status pills / priority indicators
- Project links
- Item-level progress indicators
- Export PNG/PDF
- Board templates
- AI copilot
- Activity log

---

## Phase 2: Strategy Plugin

**Goal:** The paid plugin layer that adds strategic intelligence, validation workflows, system integration, and advanced export.

**Files:** `02-PLUGIN.md`

**Depends on:** Phase 1 complete. Plugin features render conditionally when the Strategy Plugin is active. The board must function fully without the plugin.

**Definition of Done:**
- [ ] Plugin detection — all plugin features hidden when plugin is not active
- [ ] Theory of change narrative strip above columns
- [ ] Stage health badges (top-right corner, with hover popover)
- [ ] Progress bars on stage headers (validation completeness)
- [ ] Status pills on item cards (Validated / In Progress / Draft / etc.)
- [ ] Priority indicators (border color override: high = red, medium = amber)
- [ ] Project link integration (folder icon + name on card footer)
- [ ] Item-level progress indicators (current/target with bar)
- [ ] Export dropdown (PNG, PDF, Print)
- [ ] Board templates (5 framework options)
- [ ] Snapshot versioning (save/restore point-in-time)
- [ ] Empty board narrative template (fill-in-the-blank)

**Do NOT build in Phase 2:**
- AI copilot
- Activity log (build the data capture, but UI is Phase 3)

**Important:** Phase 2 should capture activity log DATA from the start (every status change, item edit, etc.) even though the activity log UI ships in Phase 3. This ensures the copilot has history to work with on launch.

---

## Phase 3: AI Copilot

**Goal:** The AI assistant layer that analyzes the logic model, identifies gaps, and provides an activity audit trail.

**Files:** `03-COPILOT.md`

**Depends on:** Phase 2 complete (needs health badges, progress bars, and activity log data).

**Definition of Done:**
- [ ] Floating trigger button (bottom-right, with notification dot)
- [ ] Hover preview card showing most relevant insight
- [ ] Side panel with chat interface
- [ ] Activity Log tab in copilot panel (audit trail of all changes)
- [ ] AI can analyze model state and identify gaps
- [ ] AI can reference activity history in conversation
- [ ] Suggested actions: walk through, gap analysis, fill gaps, refine narrative
- [ ] Notification system for proactive insights

---

## File Structure

```
00-IMPLEMENTATION-GUIDE.md    ← You are here
01-CORE.md                    ← Phase 1: Core board specification
02-PLUGIN.md                  ← Phase 2: Strategy plugin specification
03-COPILOT.md                 ← Phase 3: AI copilot specification
04-DATA-MODEL.md              ← Shared data model (all phases)
05-VISUAL-REFERENCE.md        ← Design system, colors, spacing, transitions
06-CARD-MODAL.md              ← Card detail modal specification (Phase 2 design)
```

## Conventions

- **Core features** are always visible. They do not check for plugin state.
- **Plugin features** check for plugin activation before rendering. Use the same conditional pattern as other Leantime plugins.
- **Copilot features** are a sub-module of the plugin. They require both plugin activation AND AI service configuration.
- Stage colors, titles, and order are constants defined once and referenced everywhere. Never hardcode them inline.
- The hypothesis status system is CORE, not plugin. Status pills (the visual badges on cards) are plugin, but the status dropdown and status data are core.
- All activity logging (data capture) begins in Phase 2 even though the UI ships in Phase 3.
