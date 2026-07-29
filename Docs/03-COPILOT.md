# Phase 3: AI Copilot

## Summary

The AI copilot is a conversational assistant embedded in the logic model canvas that analyzes model state, identifies gaps, provides strategic suggestions, and surfaces an activity audit trail. It lives in a side panel triggered by a floating button.

**Prerequisite:** Phase 2 (Strategy Plugin) must be complete, including activity log data capture.

---

## 1. Trigger Button

### Position
Fixed position, bottom-right corner (20px from edges). Always visible when plugin is active.

### Visual
- Size: 44px × 44px
- Border radius: 12px
- Background: gradient from `#00456e` to `#00aa64`
- Icon: sparkle/AI icon, white, 16px
- Shadow: `0 8px 32px rgba(0,0,0,0.10)`

### Notification Dot
- Position: top-right corner of button (-2px offset)
- Size: 10px circle
- Color: `#BB1B25` (red) with 2px white border
- Visible when AI has a proactive suggestion

### Hover Preview
On hover, a dark tooltip card appears above the button showing the most relevant AI insight:

```
┌─────────────────────────────────┐
│ Your Outputs → Outcomes link    │  ← Bold, white
│ has a gap                       │
│                                 │
│ No measurement plan defined yet │  ← 11px, muted
│                                 │
│ ✓ Walk me through this          │  ← Quick action options
│ ✓ What needs attention?         │
│ ✓ Help me fill gaps             │
└─────────────────────────────────┘
```

- Background: `#1e2b38` (dark primary)
- Width: 240px
- Arrow pointing down to the button

---

## 2. Side Panel

### Opening
Click the trigger button to open. Panel slides in from the right.

### Layout
- Width: 360px (or 30% of viewport, whichever is larger, max 440px)
- Height: full viewport
- Background: white
- Shadow: left edge shadow
- Z-index above the board but below modals

### Tabs
Two tabs at the top of the panel:

| Tab | Purpose |
|-----|---------|
| **Chat** | Conversational AI interface |
| **Activity** | Chronological audit trail |

---

## 3. Chat Tab

### Interface
Standard chat interface:
- Message history (scrollable)
- Text input at bottom with send button
- AI responses in styled bubbles
- User messages in simpler bubbles

### AI Capabilities

The copilot can:

1. **Analyze model state** — "What's the current state of my logic model?"
2. **Identify gaps** — "What needs attention?" / surfaces health badge issues
3. **Suggest items** — "What inputs am I missing?" / suggests based on activities
4. **Validate assumptions** — "Is my theory of change sound?"
5. **Refine narrative** — "Help me rewrite my theory of change"
6. **Reference history** — "What changed last week?" / uses activity log
7. **Compare snapshots** — "How has the model evolved since January?"
8. **Guided walkthrough** — "Walk me through this view" / stage-by-stage tour

### Suggested Actions
When the panel first opens (or on a fresh conversation), show quick-action buttons:

- Walk me through this view
- What needs attention?
- Help me fill gaps
- Refine my narrative

These seed the conversation with common starting points.

### Context Awareness
The AI always has access to:
- All items across all stages (titles, descriptions, statuses)
- Health badge states and assumption text
- Progress bar percentages
- Activity log history
- Current template type
- Narrative text

---

## 4. Activity Tab

### Purpose
Chronological audit trail of every change to the logic model. This is the UI for the activity log data captured in Phase 2.

### Entry Format
```
┌─────────────────────────────────────┐
│ GF changed status                    │
│ "$250K annual budget"                │
│ Draft → Validated: Valid             │
│ 2 hours ago                          │
├─────────────────────────────────────┤
│ MJ added item                        │
│ "1 Director, 3 Specialists..."       │
│ Added to Inputs                      │
│ Yesterday at 3:42 PM                 │
├─────────────────────────────────────┤
│ TS linked project                    │
│ "1-on-1 tutoring 3x per week"       │
│ Linked to "Tutoring Program"         │
│ Feb 10 at 10:15 AM                   │
└─────────────────────────────────────┘
```

### Entry Elements
- **Avatar + name** — who made the change
- **Action** — what happened (changed status, added item, linked project, etc.)
- **Item context** — which item was affected (title, truncated)
- **Detail** — specific change (old → new for status changes, stage name for additions)
- **Timestamp** — relative ("2 hours ago") or absolute ("Feb 10 at 10:15 AM")

### Filtering
- Filter by action type (status changes, additions, edits, links)
- Filter by user
- Filter by stage
- Date range selector

### Versioning Integration
Snapshots appear in the activity log as milestone markers:
```
┌─────────────────────────────────────┐
│ 📸 Snapshot saved                    │
│ "Q1 Board Meeting Version"           │
│ by GF — Feb 8 at 9:00 AM            │
│ [View Snapshot]                      │
└─────────────────────────────────────┘
```

---

## 5. Notification System

### Proactive Insights
The AI periodically analyzes the model and generates insights. When a new insight is available:
1. Red notification dot appears on the trigger button
2. Hover preview shows the insight
3. Clicking the button opens the chat with the insight as the first message

### Insight Triggers

| Trigger | Example Insight |
|---------|----------------|
| Health badge at "Gap" | "Your Outputs → Outcomes connection has a gap. No measurement plan defined." |
| Stage has 0 validated items | "None of your Activities have been validated yet. Consider reviewing evidence." |
| Item stuck in Draft > 14 days | "3 items in Outcomes have been in Draft for over 2 weeks." |
| Progress bar regression | "Inputs progress dropped from 75% to 50% after a status change." |
| Imbalanced stages | "You have 6 Inputs but only 1 Impact item. Consider whether your impact is fully defined." |

### Notification Behavior
- Only one insight at a time (most relevant)
- Dismissable (clicking the button clears the dot)
- Does not interrupt workflow — passive notification only
- Frequency cap: max 1 new insight per session

---

## 6. Technical Requirements

### AI Service
- Requires AI service configuration (API key, model selection)
- Copilot features hidden if AI service is not configured
- Graceful degradation: activity log tab works without AI service, chat tab shows "Configure AI to enable" message

### Context Window
The copilot sends the full model state as context with each message:
- All items (id, title, description, status, stage, priority)
- Health badges (from_stage, status, assumption_text)
- Progress percentages per stage
- Recent activity log entries (last 50)
- Current template type
- Narrative text

### Performance
- Chat responses should stream (token by token)
- Activity log should paginate (load 20 entries at a time, load more on scroll)
- Insight generation runs asynchronously, not blocking the board

---

## 7. ARIA Labels

- Trigger button: `"AI Copilot, 1 new suggestion"`
- Panel: `"AI Copilot panel"`
- Chat tab: `"Chat with AI assistant"`
- Activity tab: `"Activity log, 47 entries"`
- Activity entry: `"GF changed status of $250K annual budget from Draft to Validated Valid, 2 hours ago"`
