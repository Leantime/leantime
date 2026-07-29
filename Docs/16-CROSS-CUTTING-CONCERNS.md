# Cross-Cutting Concerns — Integration Contracts

**How Every Feature Plugs into Shared Systems**

| Field | Value |
|---|---|
| Product | Leantime |
| Document | Cross-Cutting Concerns |
| Version | 1.0 |
| Date | February 23, 2026 |
| Author | Gloria Folaron |
| Status | Living Document |
| Purpose | Define how features integrate with language, timezone, notifications, permissions, entity relations, events, search, and audit systems. Every new component, view, service, or plugin must follow these contracts. |

---

## Why This Document Exists

Leantime has multiple systems that every feature touches but no single feature owns. When a developer builds a new view and hardcodes "Due Today" instead of using a translation key, or stores a datetime without UTC conversion, or creates records without firing events — it works in their dev environment and breaks somewhere else.

This document defines the integration contract for each cross-cutting system. It answers: **what must every new feature do** to plug in correctly?

---

## 1. Language and Internationalization (i18n)

### 1.1 How It Works

Leantime uses INI-based translation files in `app/Language/{locale}/`. The `__()` helper function retrieves translated strings by key. Plugins register their own language files via `$registration->registerLanguageFiles()`.

**Translation management happens through an external management platform** — developers don't edit translation files directly for non-English languages. The workflow is:

1. Developer adds English translation keys to the `en-US.ini` file
2. Keys sync to the translation management platform
3. Translators work in the platform
4. Translated files sync back to the codebase

This means: **developers are responsible for creating keys and English strings.** Translation into other languages is handled externally. But keys must be structured, descriptive, and stable — renaming a key breaks all translations.

### 1.2 The Contract

**Every user-facing string MUST go through the translation system.** No exceptions.

| Context | Pattern | Example |
|---|---|---|
| Blade template | `{{ __('domain.key') }}` | `{{ __('pgmpro.resources.add_person') }}` |
| Blade with variables | `{{ sprintf(__('domain.key'), $var) }}` | `{{ sprintf(__('pgmpro.resources.hours_of_total'), $used, $total) }}` |
| JavaScript (inline) | `data-label="{{ __('domain.key') }}"` | Pass as data attribute, read in JS |
| JavaScript (Alpine) | `x-text="$el.dataset.label"` | Read from data attribute |
| Notifications | `$this->language->__('domain.key')` | In notification service |
| Validation messages | Translation keys | `__('validation.required')` |
| Tooltips / aria-labels | `aria-label="{{ __('domain.key') }}"` | Accessibility text is also translatable |
| Error messages | Translation keys | `__('pgmpro.errors.budget_exceeded')` |

### 1.3 Key Naming Convention

```
{plugin_or_domain}.{feature}.{element}

pgmpro.resources.title           = "Resource Allocation"
pgmpro.resources.people_section  = "People"
pgmpro.resources.add_person      = "Add person"
pgmpro.resources.hours_allocated = "%d hours allocated"
pgmpro.resources.capacity_full   = "At capacity"

strategypro.wizard.step_mapping  = "Map to Projects"
strategypro.wizard.next          = "Next"
strategypro.wizard.generating    = "Generating..."
```

**Rules:**
- Lowercase, dot-separated
- Plugin/domain prefix first, then feature, then specific element
- Use `%s` / `%d` for variable substitution (sprintf style)
- Never embed HTML in translation strings — keep markup in templates
- Never rename existing keys — this breaks all translations in the management platform
- If a string changes meaning, create a new key and deprecate the old one

### 1.4 Pluralization

Leantime's INI format doesn't natively support pluralization rules. Pattern:

```ini
pgmpro.resources.person_count_one = "1 person"
pgmpro.resources.person_count_other = "%d people"
```

```php
$key = $count === 1 ? 'pgmpro.resources.person_count_one' : 'pgmpro.resources.person_count_other';
echo sprintf(__($key), $count);
```

For languages with complex plural rules (Arabic, Polish), this two-form pattern covers common cases. The translation platform can handle the complexity — just provide the two English forms.

### 1.5 Date and Number Formatting

| Data type | Storage format | Display format | Notes |
|---|---|---|---|
| Dates | `Y-m-d` (ISO 8601) | User's locale format | Use Leantime's date formatter |
| Datetimes | `Y-m-d H:i:s` UTC | User locale + timezone | Formatter handles TZ conversion |
| Numbers | Raw numeric | User's locale (comma/period) | `number_format()` with locale |
| Currency | Cents (integer) or float | Locale + currency symbol | Format in display layer only |
| Percentages | 0-100 float | `XX%` | Display layer formatting |

**Rule:** Never format dates or numbers in the storage/service layer. Store raw, format on display.

### 1.6 RTL Support

Leantime supports right-to-left languages (Arabic, Hebrew). New components must:

- Use CSS logical properties: `margin-inline-start` not `margin-left`, `padding-inline-end` not `padding-right`
- Or use Tailwind logical utilities: `tw:ms-4` (margin-start) not `tw:ml-4`
- Never hardcode directional icons — use `dir`-aware alternatives
- Flex layouts with `gap` work in RTL automatically — but verify
- Test: `document.documentElement.dir = 'rtl'`

### 1.7 Plugin Language File Registration

```php
// In plugin's register.php
$registration->registerLanguageFiles(['en-US']);
```

Files live at:
```
app/Plugins/PgmPro/Language/en-US.ini
app/Plugins/StrategyPro/Language/en-US.ini
```

### 1.8 What to Audit

```bash
# Hardcoded English strings in Blade templates
grep -rn ">[A-Z][a-z].*</" app/Plugins/ --include="*.blade.php" | \
  grep -v "__(\|{{\|@\|<!--" | head -30

# Labels/placeholders not going through translation
grep -rn "label=\"[A-Z]\|placeholder=\"[A-Z]\|title=\"[A-Z]\|aria-label=\"[A-Z]" \
  app/Plugins/ --include="*.blade.php" | grep -v "__(" | head -20
```

---

## 2. Timezones

### 2.1 The Golden Rule

**Store UTC. Display local. Compare in UTC.**

### 2.2 How It Works

- All datetimes in the database are stored in UTC
- User's timezone is stored in their settings (`usersettings.timezone`)
- Application converts to user-local on display
- Application converts from user-local to UTC on input

### 2.3 The Contract

| Operation | Timezone | How |
|---|---|---|
| Storing a new datetime | Convert to UTC before saving | `$utc = $this->convertToUTC($userInput, $userTimezone)` |
| Displaying a datetime | Convert from UTC to user's TZ | `$local = $this->convertToUserTimezone($utcDatetime)` |
| Comparing dates (overdue?) | Compare in UTC | `$now = new DateTime('now', new DateTimeZone('UTC'))` |
| "Due today" calculation | Convert "today" boundaries to UTC | See 2.4 |
| Recurring events | Store rule + timezone | Expand in user's timezone |
| Cross-user deadlines | Store UTC | Each user sees their own TZ display |
| Timesheets | Date only (no time) + hours float | Calendar date — no TZ conversion needed |
| Scheduled notifications | Compute send time in UTC from recipient's TZ | "9am" means 9am in their timezone |
| Relative time ("3 hours ago") | Compute from UTC, display relative | No TZ conversion needed for relative |

### 2.4 "Due Today" — The Common Bug

"Today" is different for each user. This is where most timezone bugs live.

```php
// WRONG — uses server timezone
$dueToday = $query->whereDate('dueDate', Carbon::today());

// RIGHT — compute "today" in user's timezone, convert boundaries to UTC
$userTz = new DateTimeZone($userTimezone);
$startOfDay = new DateTime('today', $userTz);
$endOfDay = new DateTime('tomorrow', $userTz);
$startOfDay->setTimezone(new DateTimeZone('UTC'));
$endOfDay->setTimezone(new DateTimeZone('UTC'));
$dueToday = $query->whereBetween('dueDate', [$startOfDay, $endOfDay]);
```

### 2.5 Date Input Components

When a user picks a date/time in a form:

1. The date picker shows dates in the user's local timezone
2. On submission, the value is converted to UTC before storage
3. The conversion happens in the controller or service layer, NOT in JavaScript
4. Hidden field carries the UTC value; visible field shows the local value

```html
<!-- Pattern for date/time inputs -->
<input type="datetime-local"
       name="dueDate_display"
       value="{{ $localDateTime }}"
       data-utc="{{ $utcDateTime }}"
       @change="convertToUTC($event)">
<input type="hidden" name="dueDate" :value="utcValue">
```

### 2.6 What to Audit

```bash
# Direct date comparisons without timezone conversion
grep -rn "Carbon::today()\|Carbon::now()\|date('Y-m-d')\|new DateTime()" \
  app/Domain/ app/Plugins/ --include="*.php" | \
  grep -v "UTC\|timezone\|userTimezone" | head -20

# Date formatting in service/repository layer (should be display-only)
grep -rn "->format(\|date_format(\|strftime(" \
  app/Domain/*/Repositories/ app/Domain/*/Services/ --include="*.php" | head -20
```

---

## 3. Notifications

### 3.1 How It Works

Leantime has an event-driven notification system. Actions dispatch events, listeners determine who to notify and through which channels (in-app, email, Slack/Discord webhooks). The cron job processes the notification queue.

### 3.2 The Contract

Every feature that creates, changes, assigns, or completes something should fire the appropriate event. The notification system handles the rest.

| Action type | Event pattern | Who gets notified |
|---|---|---|
| Item created | `{domain}.{entity}.created` | Assignee, watchers |
| Item updated | `{domain}.{entity}.updated` | Assignee, watchers, commenters |
| Item assigned | `{domain}.{entity}.assigned` | New assignee |
| Item completed | `{domain}.{entity}.completed` | Assignee, watchers, parent owner |
| Status changed | `{domain}.{entity}.statusChanged` | Assignee, watchers |
| Comment added | `{domain}.{entity}.commented` | All participants |
| Deadline approaching | `{domain}.{entity}.deadlineApproaching` | Assignee (cron-triggered) |
| Deadline passed | `{domain}.{entity}.overdue` | Assignee, manager (cron-triggered) |

### 3.3 Notification Properties

Every notification must provide:

| Property | Description | Example |
|---|---|---|
| `type` | Event type identifier | `pgmpro.resource.assigned` |
| `subject` | Translation key for subject line | `__('pgmpro.notifications.resource_assigned_subject')` |
| `message` | Translation key for message body | `__('pgmpro.notifications.resource_assigned_body')` |
| `entity_type` | What kind of thing | `'resource'`, `'milestone'`, `'ticket'` |
| `entity_id` | ID of the thing | `42` |
| `url` | Deep link to the relevant view | `/pgmpro/program/5/resources#person-42` |
| `recipients` | Array of user IDs | `[3, 7, 12]` |
| `sender` | User ID of who triggered it | `session('userdata.id')` |
| `priority` | Notification urgency | `'normal'`, `'high'` |

### 3.4 Channel Rules

| Channel | When | User control |
|---|---|---|
| In-app (notification bell) | Always | Cannot disable |
| Email | Default on, user can disable per-type | User settings |
| Slack/Discord webhook | If integration configured | Project-level setting |

### 3.5 Notification Content Rules

- All notification text goes through translation keys (i18n contract)
- Notification subject must make sense in an email inbox (no "Update" — be specific)
- Message body should include enough context to act without clicking through
- URLs must be absolute (include `$appUrl`) — emails open in browser
- Datetimes in notifications display in the RECIPIENT's timezone
- Never send notifications to the person who triggered the action

### 3.6 Batching and Deduplication

- If multiple changes happen to the same entity within 5 minutes, batch into one notification
- Never send more than one notification per entity per user per hour for the same event type
- The cron job handles batching — features just fire events, they don't manage delivery

### 3.7 PgmPro / StrategyPro Notification Events

| Event | Trigger | Recipients |
|---|---|---|
| `pgmpro.resource.assigned` | Person allocated to a project | The allocated person |
| `pgmpro.resource.overallocated` | Person exceeds capacity | The person + program manager |
| `pgmpro.resource.stubCreated` | Wizard seeds resource stubs | Program manager |
| `pgmpro.budget.thresholdReached` | Spending hits 80% or 90% of budget | Program manager |
| `strategypro.program.created` | Wizard completes program creation | Program manager |
| `strategypro.mapping.completed` | Logic model mapped to projects | Program manager |

### 3.8 What to Audit

```bash
# Find service methods that create/update records without dispatching events
grep -rn "function create\|function update\|function save\|function delete" \
  app/Plugins/*/Services/ --include="*.php" | head -20
# Then check: do these methods call dispatch() or events->dispatch()?

# Find events that are dispatched but have no listener
grep -rn "dispatch(" app/Plugins/ --include="*.php" | head -20
# Cross-reference with registered listeners
```

---

## 4. Permissions and Access Control

### 4.1 How It Works

Leantime uses a role-based permission system. Users have a role within the system and optionally per-project roles. Permissions are checked in controllers before rendering views and in services before performing actions.

### 4.2 Role Hierarchy

| Role | Level | Scope |
|---|---|---|
| Owner / Admin | Highest | Full system access |
| Manager | High | Can manage projects, assign users, view reports |
| Editor | Medium | Can create/edit items in assigned projects |
| Commenter | Low | Can view and comment, not edit |
| Client | Restricted | Can view limited project data, provide feedback |

### 4.3 The Contract

**Every controller method and every service method that modifies data must check permissions.**

| Layer | What to check | How |
|---|---|---|
| Controller (view) | Can user see this page? | `$this->authService->userHasRole(['manager', 'admin'])` |
| Controller (action) | Can user perform this action? | `$this->authService->userCanAccess($projectId)` |
| Service (data) | Can user access this entity? | Check project membership before returning data |
| API | Same as controller | Middleware or explicit check |

### 4.4 Program-Level Permissions (PgmPro)

Programs add a layer above projects:

| Permission | Who | Description |
|---|---|---|
| View program dashboard | Program manager + project members | Anyone in a child project can see the program |
| Edit resource allocation | Program manager + admin | Budget and people allocation is sensitive |
| View budget details | Program manager + admin | Budget amounts may be confidential |
| Create/modify projects under program | Program manager + admin | Structural changes |
| View individual timesheets | Program manager + admin | Personal time data is sensitive |
| View aggregated metrics | All program members | Totals are fine, individual detail is not |

### 4.5 Data Sensitivity Rules

| Data type | Who can see individual records | Who can see aggregates |
|---|---|---|
| Budget amounts | Program manager, admin | All program members (total only) |
| Individual hours worked | The person themselves, their manager, admin | All program members (totals) |
| Salary/rate information | Admin only | Never exposed in UI |
| Resource allocation (who works on what) | All project members | All program members |
| Performance metrics (on-track/behind) | Program manager, admin | All program members |

### 4.6 What to Audit

```bash
# Controller methods without permission checks
grep -rn "function get\|function post\|function put\|function delete" \
  app/Plugins/*/Controllers/ --include="*.php" | head -20
# Check: does each method verify permissions before proceeding?

# Service methods that return sensitive data without access check
grep -rn "function getBudget\|function getHours\|function getTimesheets" \
  app/Plugins/*/Services/ --include="*.php" | head -20
```

---

## 5. Entity Relations

### 5.1 How It Works

`zp_entityrelations` is Leantime's system for linking entities across domains. It's a flexible many-to-many relation table that connects any entity type to any other entity type.

### 5.2 Table Structure

| Column | Purpose | Example |
|---|---|---|
| `entityA_id` | Source entity ID | `42` (canvas item) |
| `entityA_type` | Source entity type | `'canvasItem'` |
| `entityB_id` | Target entity ID | `7` (project) |
| `entityB_type` | Target entity type | `'project'` |
| `relationship_type` | What kind of link | `'generates'`, `'seeded_from'`, `'assigned_to'` |
| `meta` | JSON metadata | `'{"mappingStep": "wizard"}'` |
| `created` | When the link was made | Timestamp |

### 5.3 The Contract

Every feature that creates a relationship between entities across domains MUST use `zp_entityrelations`. Don't create custom link tables.

| Relationship | entityA | entityB | relationship_type |
|---|---|---|---|
| Logic Model output → Project milestone | canvasItem (output) | milestone | `generates` |
| Logic Model input → Resource canvas item | canvasItem (input) | canvasItem (resource) | `seeded_from` |
| Resource person → Project | canvasItem (resource) | project | `assigned_to` |
| Program → Child project | project (program) | project (child) | `parent_of` |
| Goal → Project | goal | project | `tracks` |
| Canvas item → Ticket | canvasItem | ticket | `generates` |

### 5.4 Querying Relations

```php
// Find all projects generated from a canvas output
$relations = $this->entityRelationsRepo->getRelationsByType(
    entityA_id: $canvasItemId,
    entityA_type: 'canvasItem',
    relationship_type: 'generates'
);

// Find the source canvas item for a resource allocation
$source = $this->entityRelationsRepo->getRelationsByType(
    entityB_id: $resourceItemId,
    entityB_type: 'canvasItem',
    relationship_type: 'seeded_from'
);
```

### 5.5 Rules

- **Always bidirectional-queryable:** you should be able to find both "what did this generate?" and "where did this come from?"
- **Use specific relationship_type strings:** not generic "related_to" — be explicit about the nature of the link
- **Clean up on delete:** when an entity is deleted, its relations should be cleaned up (or marked inactive)
- **Don't duplicate:** if a link already exists, don't create a second one. Check first.
- **Metadata is for context:** use the `meta` JSON for information about HOW the link was made (wizard step, manual, auto-seeded), not for the link's data itself

### 5.6 What to Audit

```bash
# Custom link tables that should use entityrelations
grep -rn "CREATE TABLE.*link\|CREATE TABLE.*map\|CREATE TABLE.*rel" \
  app/Domain/ app/Plugins/ --include="*.php" | head -10

# Hardcoded joins instead of entityrelations queries
grep -rn "JOIN.*ON.*\.id\s*=\s*.*\..*_id" \
  app/Plugins/ --include="*.php" | grep -v "entityrelations" | head -20
```

---

## 6. Events System

### 6.1 How It Works

Leantime has an event dispatch system that allows plugins and core code to hook into actions. Events follow a naming convention and carry payload data. This is the backbone that notifications, webhooks, integrations, and audit logging plug into.

### 6.2 Event Naming Convention

```
leantime.{domain}.{entity}.{action}

leantime.pgmpro.resource.created
leantime.pgmpro.resource.updated
leantime.pgmpro.resource.deleted
leantime.pgmpro.budget.thresholdReached
leantime.strategypro.program.created
leantime.strategypro.mapping.completed
leantime.core.ticket.statusChanged
leantime.core.project.userAssigned
```

### 6.3 The Contract

**Every state-changing operation MUST dispatch an event.** This enables:
- Notifications to be sent
- Webhooks to fire
- Audit trail to be recorded
- Plugin extensions to react

| When to dispatch | Event type | Payload must include |
|---|---|---|
| Entity created | `*.created` | Entity ID, entity data, creator user ID |
| Entity updated | `*.updated` | Entity ID, changed fields (old + new values), updater user ID |
| Entity deleted | `*.deleted` | Entity ID, entity data (snapshot before delete), deleter user ID |
| Status changed | `*.statusChanged` | Entity ID, old status, new status, changer user ID |
| Assignment changed | `*.assigned` | Entity ID, old assignee, new assignee, assigner user ID |
| Threshold reached | `*.thresholdReached` | Entity ID, metric name, threshold value, current value |

### 6.4 Event Payload Structure

```php
$this->eventsDispatcher->dispatch('leantime.pgmpro.resource.created', [
    'entityId' => $resourceItemId,
    'entityType' => 'resourceCanvasItem',
    'projectId' => $programId,
    'data' => $resourceData,
    'userId' => session('userdata.id'),
    'timestamp' => gmdate('Y-m-d H:i:s'), // UTC
]);
```

### 6.5 Listening to Events

```php
// In plugin's register.php
$this->events->listen('leantime.pgmpro.resource.created', function ($payload) {
    // React to resource creation
    $this->notificationService->notifyResourceAssigned($payload);
});
```

### 6.6 Rules

- Fire events AFTER the database operation succeeds (not before — don't notify about failed saves)
- Event payloads should be serializable (no objects, no closures — plain arrays)
- Listeners should be fast — if heavy work is needed, push to the queue
- Don't rely on event ordering — listeners may execute in any order
- Idempotent listeners — the same event firing twice should not cause double-effects

---

## 7. Search and Filtering

### 7.1 How It Works

Leantime provides search and filtering across entities. Each domain that stores user-facing data should be searchable.

### 7.2 The Contract

Any new entity type that users interact with should be findable through search.

| Requirement | Implementation |
|---|---|
| Full-text search | Entity title/name/description indexed |
| Filter by status | Status field is filterable |
| Filter by assignee | User ID field is filterable |
| Filter by project | Project ID field is filterable |
| Filter by date range | Date fields support range queries |
| Sort options | At minimum: name, date created, date modified, status |

### 7.3 Search Integration Pattern

```php
// Register searchable fields for a new entity type
public function getSearchableFields(): array
{
    return [
        'title' => ['weight' => 10, 'type' => 'text'],
        'description' => ['weight' => 5, 'type' => 'text'],
        'status' => ['weight' => 0, 'type' => 'filter'],
        'assignedUserId' => ['weight' => 0, 'type' => 'filter'],
        'projectId' => ['weight' => 0, 'type' => 'filter'],
    ];
}
```

### 7.4 Filter UI Consistency

All filter interfaces should follow the same pattern:

| Element | Standard |
|---|---|
| Filter bar position | Top of content area, below page header |
| Filter chips | Removable pills showing active filters |
| Clear all | Single action to reset all filters |
| Result count | Show "N results" or "N of M" when filtered |
| Empty state | Clear message when filters return no results, with suggestion to adjust |
| Persistence | Filters persist within session for the same page |
| URL parameters | Filters should be reflected in URL for shareability |

---

## 8. Error Handling

### 8.1 The Contract

| Layer | Error handling pattern |
|---|---|
| Controller | Catch service exceptions, display user-friendly message (translated), log technical details |
| Service | Throw domain-specific exceptions with context. Never silently swallow errors. |
| Repository | Throw data-layer exceptions (connection failures, constraint violations) |
| Blade view | Use `@error` directive for form validation. Show inline errors. |
| HTMX | Return appropriate HTTP status codes. 422 for validation, 403 for permission, 500 for server errors. |
| JavaScript | Catch promise rejections. Show user feedback. Never silent failures. |

### 8.2 User-Facing Error Messages

- Always go through translation system: `__('errors.permission_denied')`
- Be specific enough to act on: "You don't have permission to edit resource allocations" not just "Access denied"
- Never expose stack traces, SQL queries, or internal paths to the user
- Provide a next step: "Contact your admin" or "Try again" or "Check your input"

### 8.3 Error Display Patterns

| Context | Pattern |
|---|---|
| Form validation | Inline red text below the field, `aria-invalid="true"` + `aria-describedby` |
| Page-level error | Banner at top of content area, dismissible, `role="alert"` |
| HTMX swap error | Replace target with error message, or show banner |
| Network error | Persistent banner: "Connection lost. Changes will save when reconnected." |
| Permission error | Redirect to appropriate page with flash message |
| 404 | Friendly page with navigation back to safety |

### 8.4 Cognitive Accessibility for Errors

- Never blame the user ("Invalid input" → "Please enter a number between 1 and 100")
- Don't clear the form on error — preserve what they entered
- Focus the first error field automatically (for screen readers and attention)
- Group multiple errors at the top AND show inline (users may miss one or the other)
- Use gentle language. The user isn't "wrong" — the system needs different input.

---

## 9. Audit Trail

### 9.1 The Contract

Every significant data change should be traceable. At minimum:

| What to log | Fields |
|---|---|
| Who | User ID |
| What | Entity type, entity ID, action (create/update/delete) |
| When | UTC timestamp |
| Details | Changed fields with old and new values |
| Context | IP address, session ID (for security audit) |

### 9.2 What Counts as "Significant"

| Log | Don't log |
|---|---|
| Create, update, delete of any entity | Read/view operations |
| Status changes | Filter or sort changes |
| Assignment changes | UI state (collapsed sections, etc.) |
| Permission changes | Session heartbeats |
| Budget changes | Draft saves (log on publish only) |
| Login/logout | Page navigation |

### 9.3 Implementation

Audit logging should happen via event listeners — not hardcoded into every service method. The events system (section 6) fires events, and an audit listener records them.

```php
// Audit listener registered globally
$this->events->listen('leantime.*.*.*', function ($eventName, $payload) {
    if ($this->isAuditableEvent($eventName)) {
        $this->auditRepo->log([
            'event' => $eventName,
            'userId' => $payload['userId'] ?? session('userdata.id'),
            'entityType' => $payload['entityType'] ?? null,
            'entityId' => $payload['entityId'] ?? null,
            'data' => json_encode($payload['data'] ?? []),
            'timestamp' => gmdate('Y-m-d H:i:s'),
        ]);
    }
});
```

---

## 10. Cron and Background Jobs

### 10.1 The Contract

Features that need periodic processing must plug into Leantime's cron system — not create their own schedulers.

| Job type | Frequency | Examples |
|---|---|---|
| Notification delivery | Every 5-15 minutes | Email queue, Slack webhook queue |
| Deadline checks | Daily | Overdue detection, approaching deadline alerts |
| Data aggregation | Daily or weekly | Timesheet summaries, budget rollups |
| Cleanup | Weekly | Old session data, expired tokens |

### 10.2 Rules

- Jobs must be idempotent — running the same job twice produces the same result
- Jobs must be fast — if a job takes > 30 seconds, it should be chunked
- Jobs must log their execution (start, end, items processed, errors)
- Jobs must handle partial failure gracefully — one failed item shouldn't stop the batch
- All times in cron jobs use UTC

---

## 11. URL and Routing Conventions

### 11.1 URL Structure

```
/{plugin}/{entity}/{id}/{action}

/pgmpro/program/5/resources          — resource allocation view
/pgmpro/program/5/dashboard          — program dashboard
/strategypro/wizard/start            — wizard entry
/strategypro/wizard/step/mapping     — wizard step
```

### 11.2 Rules

- RESTful where possible: GET for reading, POST for creating, PUT for updating, DELETE for removing
- HTMX endpoints return partial HTML (fragments), not full pages
- IDs in URLs are entity IDs, not slugs (simpler, no collision risk)
- Plugin routes are prefixed with plugin name
- Core routes follow existing Leantime conventions
- All URLs should be bookmarkable/shareable — state reflected in URL, not just session

### 11.3 HTMX Route Conventions

```
GET  /pgmpro/program/{id}/resources                  — full page
GET  /pgmpro/program/{id}/resources/people            — partial: people section only
POST /pgmpro/program/{id}/resources/people             — create person allocation
PUT  /pgmpro/program/{id}/resources/people/{personId}  — update person allocation
GET  /pgmpro/program/{id}/resources/summary            — partial: summary strip only
```

HTMX requests include `HX-Request: true` header. Controllers detect this and return partials instead of full pages.

---

## 12. Integration Checklist

**Before shipping any new feature, verify all of the following:**

### Language
- [ ] All user-facing strings use `__()` translation helper
- [ ] Translation keys added to `en-US.ini` with English strings
- [ ] No HTML embedded in translation strings
- [ ] Date and number formatting uses locale-aware helpers
- [ ] RTL layout tested (or at minimum, logical CSS properties used)

### Timezone
- [ ] All datetimes stored in UTC
- [ ] All datetimes displayed in user's timezone
- [ ] "Due today" logic uses user's timezone boundaries
- [ ] Date inputs convert to UTC before storage
- [ ] No bare `Carbon::today()` or `date('Y-m-d')` without timezone context

### Notifications
- [ ] State-changing actions dispatch events
- [ ] Notification content uses translation keys
- [ ] Notification includes deep link URL
- [ ] Notification datetimes use recipient's timezone
- [ ] Actor is excluded from notification recipients

### Permissions
- [ ] Controller checks role/access before rendering view
- [ ] Service checks access before returning sensitive data
- [ ] Budget and timesheet data respects visibility rules
- [ ] Admin-only features are gated appropriately

### Entity Relations
- [ ] Cross-domain links use `zp_entityrelations`
- [ ] No custom link tables created
- [ ] Relations cleaned up on entity deletion
- [ ] Relations are queryable in both directions

### Events
- [ ] All create/update/delete operations dispatch events
- [ ] Events fire AFTER successful database operation
- [ ] Event payloads are serializable (plain arrays)
- [ ] Event naming follows convention

### Search and Filtering
- [ ] New entity types are searchable
- [ ] Filters follow standard UI pattern
- [ ] Active filters reflected in URL
- [ ] Empty states show helpful message

### Error Handling
- [ ] User-facing errors use translation keys
- [ ] Errors are specific and actionable
- [ ] Form errors show inline + summary
- [ ] No stack traces exposed to user
- [ ] Forms preserve input on error

### Accessibility
- [ ] (Refer to 14-DESIGN-TOKENS.md section 8 — full accessibility checklist)

### Theming
- [ ] (Refer to 14-DESIGN-TOKENS.md section 1 — full theming checklist)

---

## Changelog

| Date | Change | Author |
|---|---|---|
| 2026-02-23 | v1.0 — initial cross-cutting concerns: i18n (with management platform note), timezones, notifications, permissions, entity relations, events, search, error handling, audit trail, cron, routing, integration checklist | GF |
