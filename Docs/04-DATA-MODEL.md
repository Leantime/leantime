# Data Model

## Overview

Data model for the Logic Model Canvas. Tables are organized by phase — Phase 1 tables are required for core functionality, Phase 2 tables are added when the Strategy Plugin is active.

---

## Phase 1 Tables

### logic_models

The top-level entity. One project can have multiple logic models.

| Column | Type | Default | Notes |
|--------|------|---------|-------|
| id | int (PK) | auto | |
| project_id | int (FK) | — | References projects table |
| name | varchar(255) | — | User-defined model name |
| template_type | varchar(50) | 'standard' | Phase 2: template selection |
| active_stage | int | 1 | Currently focused stage (1-5) |
| created_at | datetime | now | |
| updated_at | datetime | now | |
| created_by | int (FK) | — | References users table |

### logic_model_items

Individual items within a stage. Each is a hypothesis.

| Column | Type | Default | Notes |
|--------|------|---------|-------|
| id | int (PK) | auto | |
| logic_model_id | int (FK) | — | References logic_models |
| stage | int | — | 1-5 (Inputs through Impact) |
| title | varchar(500) | — | Item hypothesis title |
| description | text | null | Rich text description |
| hypothesis_status | varchar(30) | 'draft' | draft, in_review, validated_valid, on_hold, validated_invalid |
| sort_order | int | 0 | Order within the stage |
| assigned_to | int (FK) | null | References users table |
| created_at | datetime | now | |
| updated_at | datetime | now | |
| created_by | int (FK) | — | References users table |

### logic_model_item_comments

Reuse Leantime's existing comment system. Link comments to items via the standard module/entity pattern:
- Module: `logic_model_item`
- Entity ID: `item.id`

### logic_model_item_attachments

Reuse Leantime's existing attachment/file system. Link files to items via the standard module/entity pattern.

---

## Phase 2 Tables (Plugin)

### logic_model_health

Connection quality between stages. One record per connection (max 4 per model: 1→2, 2→3, 3→4, 4→5).

| Column | Type | Default | Notes |
|--------|------|---------|-------|
| id | int (PK) | auto | |
| logic_model_id | int (FK) | — | References logic_models |
| from_stage | int | — | 1-4 (no health badge on stage 5) |
| health_status | varchar(20) | 'warning' | ok, warning, risk |
| assumption_text | text | null | Description of assumptions |
| risk_level | varchar(20) | null | For display in popover |
| evidence_notes | text | null | Supporting evidence or gaps |
| updated_at | datetime | now | |
| updated_by | int (FK) | — | References users table |

### logic_model_item_progress

Quantifiable progress tracking on individual items.

| Column | Type | Default | Notes |
|--------|------|---------|-------|
| id | int (PK) | auto | |
| item_id | int (FK) | — | References logic_model_items |
| current_value | decimal | 0 | Current measured value |
| target_value | decimal | — | Goal value |
| unit | varchar(50) | null | "students", "hours", "%" etc. |
| updated_at | datetime | now | |

### logic_model_item_priority

Item priority for border color override.

| Column | Type | Default | Notes |
|--------|------|---------|-------|
| item_id | int (FK, PK) | — | References logic_model_items |
| priority | varchar(20) | null | null, 'medium', 'high' |

**Alternative:** Add `priority` column directly to `logic_model_items` table instead of a separate table.

### logic_model_project_links

Links between items and Leantime projects/milestones/goals.

| Column | Type | Default | Notes |
|--------|------|---------|-------|
| id | int (PK) | auto | |
| item_id | int (FK) | — | References logic_model_items |
| linked_entity_type | varchar(50) | — | 'project', 'milestone', 'goal' |
| linked_entity_id | int | — | ID of the linked entity |
| created_at | datetime | now | |
| created_by | int (FK) | — | References users table |

### logic_model_snapshots

Point-in-time snapshots of the entire model.

| Column | Type | Default | Notes |
|--------|------|---------|-------|
| id | int (PK) | auto | |
| logic_model_id | int (FK) | — | References logic_models |
| name | varchar(255) | null | User-defined snapshot name |
| snapshot_data | json/text | — | Full serialized model state |
| created_at | datetime | now | |
| created_by | int (FK) | — | References users table |

### logic_model_activity_log

Audit trail for all changes. Begin populating in Phase 2 even though UI ships in Phase 3.

| Column | Type | Default | Notes |
|--------|------|---------|-------|
| id | int (PK) | auto | |
| logic_model_id | int (FK) | — | References logic_models |
| item_id | int (FK) | null | Null for model-level events |
| action_type | varchar(50) | — | See action types below |
| action_detail | json/text | — | Structured change data |
| user_id | int (FK) | — | Who made the change |
| created_at | datetime | now | |

**Action Types:**
- `item_created`
- `item_edited`
- `item_deleted`
- `status_changed`
- `priority_changed`
- `project_linked`
- `project_unlinked`
- `health_changed`
- `snapshot_saved`
- `template_changed`
- `model_created`
- `model_renamed`

**action_detail Example (status_changed):**
```json
{
  "field": "hypothesis_status",
  "old_value": "draft",
  "new_value": "validated_valid",
  "item_title": "$250K annual budget"
}
```

---

## Indexes

### Phase 1
- `logic_models`: index on `project_id`
- `logic_model_items`: index on `logic_model_id, stage`, index on `logic_model_id, sort_order`

### Phase 2
- `logic_model_health`: unique index on `logic_model_id, from_stage`
- `logic_model_project_links`: index on `item_id`, index on `linked_entity_type, linked_entity_id`
- `logic_model_activity_log`: index on `logic_model_id, created_at DESC`, index on `item_id`
- `logic_model_snapshots`: index on `logic_model_id, created_at DESC`

---

## Stage Constants

Define these once, reference everywhere:

```php
const STAGES = [
    1 => ['key' => 'inputs',     'title' => 'Inputs',     'subtitle' => 'Resources we invest',  'icon' => 'arrow-right-to-bracket', 'color' => '#4A85B5', 'bg' => '#EDF3F8'],
    2 => ['key' => 'activities',  'title' => 'Activities',  'subtitle' => 'What we do',           'icon' => 'gears',                  'color' => '#3E937A', 'bg' => '#ECF6F2'],
    3 => ['key' => 'outputs',     'title' => 'Outputs',     'subtitle' => 'What we produce',      'icon' => 'boxes-stacked',          'color' => '#C09035', 'bg' => '#FBF5EA'],
    4 => ['key' => 'outcomes',    'title' => 'Outcomes',    'subtitle' => 'Changes we expect',    'icon' => 'chart-line',             'color' => '#8E6AAD', 'bg' => '#F2EDF8'],
    5 => ['key' => 'impact',      'title' => 'Impact',      'subtitle' => 'Ultimate change',      'icon' => 'bullseye',               'color' => '#2D7D5E', 'bg' => '#EAF5F0'],
];

const TEMPLATES = [
    'standard'  => ['Inputs', 'Activities', 'Outputs', 'Outcomes', 'Impact'],
    'toc'       => ['Problem', 'Root Causes', 'Interventions', 'Outcomes', 'Long-term Change'],
    'results'   => ['Resources', 'Processes', 'Deliverables', 'Results', 'Goals'],
    'pathway'   => ['Needs', 'Strategies', 'Milestones', 'Changes', 'Vision'],
    'program'   => ['Investments', 'Actions', 'Products', 'Effects', 'Impact'],
];

const HYPOTHESIS_STATUSES = [
    'draft'              => ['label' => 'Draft',              'color' => '#1B75BB'],
    'in_review'          => ['label' => 'In Review',          'color' => '#F0A030'],
    'validated_valid'    => ['label' => 'Validated: Valid',    'color' => '#75BB1B'],
    'on_hold'            => ['label' => 'On Hold',            'color' => '#BB1B25'],
    'validated_invalid'  => ['label' => 'Validated: Invalid',  'color' => '#BB1B25'],
];
```
