# 09-ADDENDUM-MAPPING-CORRECTION.md

## Addendum: Canvas-to-Entity Mapping Correction

**Applies to:** `07-WORKSTRUCTURE.md` (Section 4.2) and `08-WORKSTRUCTURE-PROMPT.md` (Section 2.2)
**Date:** February 20, 2026
**Trigger:** Review of populated Logic Model board with real workforce development data
**Status:** APPROVED — supersedes mapping tables in referenced documents

---

## What Changed

The original PRD mapped Activities → Milestones and Outputs → Tasks. This was backwards. Review of a real populated board (DOL WIOA workforce development program) revealed that the natural mapping follows the Logic Model's bottom-up causal chain.

---

## Corrected Mapping

| Canvas Stage | What It Means | Leantime Entity | Example from Real Board |
|---|---|---|---|
| **Inputs** (Stage 1) | What I have to work with | Resources / dependencies / notes | "DOL WIOA grant funding", "Case management staff" |
| **Activities** (Stage 2) | What I'll do every day | **Tasks (to-dos)** | "Skills assessment and IEP creation", "Job placement and retention coaching" |
| **Outputs** (Stage 3) | How I know I'm on track | **Milestones (delivery markers)** | "200 participants enrolled annually", "150 credentials earned" |
| **Outcomes** (Stage 4) | The results of that work | Goals (Goalcanvas) | "75% credential attainment rate", "80% retention at 6 months" |
| **Impact** (Stage 5) | How it changes the world | Project objective / strategy description | "Increase economic self-sufficiency", "Reduce unemployment in target communities" |

### Previous (Incorrect) Mapping

| Canvas Stage | Was Mapped To | Why It Was Wrong |
|---|---|---|
| Activities (Stage 2) | Milestones | Activities are things people *do*. "Skills assessment and IEP creation" is a task, not a milestone. |
| Outputs (Stage 3) | Tasks | Outputs are what activities *produce*. "200 participants enrolled" is a delivery target — a milestone you hit or miss. |

---

## Impact on Documents

### 07-WORKSTRUCTURE.md — Section 4.2 (Logic Model Source Adapter)

**Replace the mapping table with:**

| Canvas Stage | WorkStructure Property | Mapping |
|---|---|---|
| Impact (Stage 5) | `objective` | Item title → strategic objective / project description |
| Outcomes (Stage 4) | `goals[]` | Each item → ProposedGoal with metrics (startValue, endValue, metricType) |
| Outputs (Stage 3) | `milestones[]` | Each item → ProposedMilestone. These are delivery markers the team works toward. |
| Activities (Stage 2) | `tasks[]` | Each item → ProposedTask, grouped under parent Output's milestone via canvas relationships. |
| Inputs (Stage 1) | `resources[]` | Items → resource notes, budget entries, team requirements |

### 08-WORKSTRUCTURE-PROMPT.md — Section 2.2 (LogicModelAdapter)

**Replace the mapping table with:**

| Canvas Stage | DB Source | WorkStructure Target | Mapping Logic |
|---|---|---|---|
| Impact (stage 5) | `zp_canvas_items` where `box = 'box5'` | `$structure->objective` | First item's title → objective. Additional items → metadata. |
| Outcomes (stage 4) | `zp_canvas_items` where `box = 'box4'` | `$structure->goals[]` | Each item → ProposedGoal. Use item description for metric details. |
| Outputs (stage 3) | `zp_canvas_items` where `box = 'box3'` | `$structure->milestones[]` | Each item → ProposedMilestone. tempId = 'ms-' + item ID. |
| Activities (stage 2) | `zp_canvas_items` where `box = 'box2'` | `$structure->tasks[]` | Each item → ProposedTask. Use `relates_to` field to link to parent Output's milestone. |
| Inputs (stage 1) | `zp_canvas_items` where `box = 'box1'` | `$structure->resources[]` | Each item → ProposedResource. Infer type from content. |

### 08-WORKSTRUCTURE-PROMPT.md — Section 2.5 (HxControllers)

**Step 1 (ScopeStep)** now shows Outputs as proposed milestones, not Activities.

**Step 2 (DeliverablesStep)** now shows Activities as proposed tasks grouped under their parent Output milestones.

### 08-WORKSTRUCTURE-PROMPT.md — Section 2.7 (Step Partials)

**Replace `wizard-scope.blade.php` description with:**

Step 1 shows **Outputs** mapped as proposed milestones. Each row: Output title (editable), description (expandable), start/end date pickers, checkbox to include/exclude. These are the delivery markers the team works toward.

**Replace `wizard-deliverables.blade.php` description with:**

Step 2 shows **Activities** mapped as proposed tasks, grouped under their parent Output milestone. Each milestone section is an accordion. Inside: list of task rows from Activities. "Add task" button per section for tasks not yet on the canvas.

---

## Impact on Wizard UX

### Task-to-Milestone Grouping

The grouping of tasks under milestones comes from the **Output → Activity relationship** on the canvas. Specifically:

- If an Activity's `relates_to` field points to an Output, the resulting task is grouped under that Output's milestone.
- If an Activity has no `relates_to`, it becomes an ungrouped task. The wizard should prompt the user to assign it to a milestone.

**Note to Claude Code:** Verify how the canvas stores relationships between items across stages. The `relates_to` field, parent-child relationships, or a separate relationship table — check the Phase 1 implementation for the actual mechanism.

### Adaptive Weight Implications

For a **rich** board (like the workforce development example), the wizard flow changes:

- Stage 1 has 3 Outputs → 3 proposed milestones. Almost no editing needed.
- Stage 2 has 3 Activities → 3 proposed tasks. User confirms grouping under milestones.
- Stage 3 has 3 Outcomes → 3 proposed goals. Confirm metric types.
- This should collapse to a **single review screen** since the data is clean.

For a **sparse** board (e.g., only Activities filled in):

- No Outputs → no milestones to create. Wizard asks: "What deliverables would tell you these activities are working?"
- This is where the extended prompts kick in to fill gaps.

---

## The Bottom-Up Mental Model

The Logic Model reads as a bottom-up causal chain that maps directly to Leantime's work hierarchy:

```
Inputs       → "Here's what I have"           → Resources / dependencies
Activities   → "Here's what I'll do"           → Tasks (to-dos)
Outputs      → "Here's how I know I'm on track" → Milestones (delivery markers)
Outcomes     → "Here's the results of that work" → Goals (measurable targets)
Impact       → "Here's how it changes the world" → Project objective / strategy
```

This also clarifies the Copilot routing question:

| Starting Point | Direction | Who Thinks This Way |
|---|---|---|
| Inputs forward (bottom up) | "I have these resources, what can I do?" | Scarcity-driven, constrained orgs |
| Impact backward (top down) | "Here's the change I want, what would it take?" | Vision-driven, strategic planning |
| Activities outward (middle out) | "I know what we do, help me frame it" | Existing programs documenting retroactively |

All three fill the same board. The wizard reads the same board. The entities come out identical.

---

## No Other Changes

Everything else in the PRD and prompt doc remains correct:

- WorkStructure core domain architecture — unchanged
- WorkGenerator service — unchanged (it creates milestones and tasks; only the source of each changes)
- WorkStructureReader — unchanged
- Entity links — unchanged
- Events — unchanged
- Component architecture — unchanged
- Blade component list — unchanged
- Execution order — unchanged
- Risk register — unchanged

The only delta is which canvas stage maps to which entity type in the LogicModelAdapter.
