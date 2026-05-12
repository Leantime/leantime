# Workflow Simplification Plan

Leantime is powerful, but the current experience can feel heavy because many project-management concepts are visible at the same time: projects, clients, goals, milestones, tickets, boards, calendars, files, reports, timesheets, canvases, and admin tools. The main goal should be to make the default workflow feel like this:

1. See what needs attention.
2. Add or update work quickly.
3. Know what is blocked, late, or done.
4. Invite people only when needed.
5. Hide advanced planning tools until the user asks for them.

## Product Direction

Make the app feel like a "My Work" and "Project Work" system first, not a full project-management toolbox on the first screen.

The default user should only need to understand five concepts:

- Work item: a todo, task, bug, or request.
- Project: a container for related work.
- Due date: when something needs attention.
- Status: not started, in progress, waiting, done.
- Owner: who is responsible.

Everything else should be optional, progressive, or admin-only.

## Most Important New Workflow: Weekly Team Assignment

The app should support a weekly work cycle between a Team Lead and each employee. This should be treated as a core workflow, not just a report.

Example:

1. Team Lead opens `Team Work`.
2. Team Lead chooses an employee.
3. Team Lead creates that employee's weekly plan.
4. Employee sees assigned work in `My Work`.
5. Employee marks tasks as done, in progress, blocked, or not completed.
6. If work is not completed, employee must enter a reason.
7. Team Lead reviews progress, blockers, reasons, and next week priorities.
8. Weekly history is saved so managers can see patterns over time.

### Weekly Plan Structure

Each weekly plan should contain these sections, matching the 1:1 spreadsheet format but built into the app so it is easier to use than a spreadsheet:

Header fields:

- Employee name
- Manager (Team Lead) name
- Frequency: Weekly
- Month
- Week label (e.g. 1stWeek)
- Date range (e.g. 4th May - 9th May)
- Date of 1:1 meeting

Sections (each maps to a column in the reference spreadsheet):

1. **Top Priorities** — numbered list of this week's most important tasks (1, 2, 3).
2. **Wins & Progress** — numbered list of what went well or was completed (1, 2, 3).
3. **Challenges / Blockers** — numbered list of obstacles faced (1, 2, 3).
4. **Manager Support Needed** — numbered list of areas where the employee needs help (1, 2).
5. **Ideas / Suggestions** — numbered list of improvement ideas (1, 2).
6. **Feedback Exchange** — four specific feedback types, all within one section:
   - Manager → Employee (What's working)
   - Manager → Employee (Improve)
   - Employee → Manager (What's helping)
   - Employee → Manager (Improve)
7. **Growth & Development** — three sub-fields:
   - Current Focus Area
   - Support Needed
   - Next Milestone
8. **Next Week Priorities** — numbered list of tasks planned for next week (1, 2, 3).
9. **Commitments & Follow-ups** — each commitment is structured as:
   - Task description
   - Owner (who is responsible)
   - Deadline

The Team Lead view should group employees by month, with each employee's weekly block visible in sequence (just like the spreadsheet rows), so a manager can scroll through the month and see all team members' weekly plans at a glance.

### Employee Task Completion Rules

Every assigned task should have one of these states:

- Not started
- In progress
- Blocked
- Completed
- Not completed

When an employee marks a task as `Blocked` or `Not completed`, require:

- Reason
- What help is needed
- New expected completion date
- Optional attachment or comment

Examples of reasons:

- Waiting for information
- Requirement was unclear
- More time needed
- Dependency not completed
- Technical issue
- Client approval pending
- Other

### Team Lead Review Screen

Add a Team Lead screen called `Team Work`.

It should show:

- Team members
- Current week completion percentage
- Completed tasks
- Blocked tasks
- Not completed tasks with reasons
- Upcoming deadlines
- Employee feedback
- Team Lead follow-ups

Suggested navigation:

- Team Work
- Weekly Plans
- Blockers
- Team Calendar
- Reports

Suggested code areas:

- New domain: `app/Domain/WeeklyPlanning`
- Or extend existing domains first:
  - `app/Domain/Tickets`
  - `app/Domain/Timesheets`
  - `app/Domain/Oneonone`
  - `app/Domain/Dashboard`
  - `app/Domain/Menu`

Recommended approach:

- Start with a new `WeeklyPlanning` domain if this workflow is central to the product.
- Reuse tickets/tasks for assigned work instead of creating a second task system.
- Store weekly plans as their own records that link to tasks, users, and projects.

### Suggested Weekly Planning Data Model

Add tables similar to:

- `zp_weekly_plans`
- `zp_weekly_plan_items`
- `zp_weekly_plan_feedback`

`zp_weekly_plans` should store:

- `id`
- `employeeId`
- `teamLeadId`
- `month` — grouping month (e.g. "May 26")
- `weekLabel` — display label (e.g. "1stWeek")
- `weekStart` — date range start (e.g. 2026-05-04)
- `weekEnd` — date range end (e.g. 2026-05-09)
- `dateOfOneOnOne` — the actual 1:1 meeting date
- `status` — draft, active, reviewed, closed
- `topPriorities` — JSON or text, numbered list
- `winsAndProgress` — JSON or text, numbered list
- `challengesAndBlockers` — JSON or text, numbered list
- `managerSupportNeeded` — JSON or text, numbered list
- `ideasAndSuggestions` — JSON or text, numbered list
- `growthCurrentFocus` — text
- `growthSupportNeeded` — text
- `growthNextMilestone` — text
- `nextWeekPriorities` — JSON or text, numbered list
- `summary` — optional overall summary
- `createdAt`
- `updatedAt`

`zp_weekly_plan_items` should store (links weekly plan to existing tickets/tasks):

- `id`
- `weeklyPlanId`
- `ticketId` — references existing task in the system
- `priority` — order/importance
- `expectedOutcome`
- `status` — not started, in progress, blocked, completed, not completed
- `completionReason` — required when status is blocked or not completed
- `supportNeeded`
- `newDueDate`

`zp_weekly_plan_feedback` should store:

- `id`
- `weeklyPlanId`
- `fromUserId`
- `toUserId`
- `type` — one of: manager_to_employee_working, manager_to_employee_improve, employee_to_manager_helping, employee_to_manager_improve
- `message`
- `createdAt`

`zp_weekly_plan_commitments` should store (new table for the Commitments & Follow-ups column):

- `id`
- `weeklyPlanId`
- `task` — description of the commitment
- `ownerId` — who is responsible
- `deadline` — when it must be done
- `status` — pending, done
- `createdAt`

### Weekly Workflow Pages

Add these pages:

- `My Weekly Plan`: employee view of this week's assigned work with all 9 sections visible.
- `Team Weekly Plans`: team lead view grouped by month, showing all employees' weekly blocks in sequence (mirrors the spreadsheet layout).
- `Weekly Review`: 1:1 style review for one employee and one week, with the 4-direction feedback exchange form.
- `Blockers`: all blocked or not completed work with reasons.
- `Commitments`: all commitments and follow-ups with task, owner, and deadline.
- `Weekly History`: previous weeks for employee and manager review, browsable by month.

Use HTMX for:

- Marking task status.
- Adding blocker reasons.
- Saving feedback.
- Moving unfinished work to next week.
- Updating Team Lead dashboards without full-page reloads.

### Weekly Plan Permissions

Developer:

- Can see own weekly plan.
- Can update own task status.
- Must provide reasons for blocked or not completed tasks.
- Can add comments, support requests, and suggestions.

Team Lead:

- Can create weekly plans for direct team members.
- Can assign tasks to direct team members.
- Can review reasons and blockers.
- Can add feedback and next week priorities.

Company Manager:

- Can see all team leads and team summaries.
- Can review completion rates, blockers, and delivery risks.
- Should not need to edit every task by default.

Administrator:

- Can configure users, teams, permissions, workflow settings, and project access.

Owner:

- Full system access, billing, ownership, and global configuration.

Client access:

- Can see project progress only for projects they are allowed to access.
- Should see summary, timeline, task completion percentage, and shared files.
- Should not see internal employee reasons, manager feedback, private blockers, or 1:1 notes unless explicitly shared.
- Should not appear in the main internal role hierarchy.

## Highest Priority Changes

### 1. Make `My Work` the default home

Current behavior separates project dashboard, personal dashboard, project hub, tickets, timesheets, calendar, and one-on-ones. New users can get lost before they know where to start.

Change:

- Route regular users to `/dashboard/home` after login instead of a project-specific dashboard.
- Make `My Work` the main daily page.
- Show only:
  - My open work
  - Due today
  - Overdue
  - Recently updated
  - Waiting on me
  - Quick add input
- Keep project switcher available, but do not make it the first required decision.

Suggested code areas:

- `app/Domain/Dashboard`
- `app/Domain/Menu/Repositories/Menu.php`
- `app/Domain/Menu/Composers/Menu.php`
- `app/Domain/Auth` login redirect logic

### 2. Simplify the left navigation

The current menu exposes too many mental models. The default menu already has `Overview`, `Make`, `Think`, and `Data Room`, but even this requires users to decode product language.

Change default navigation to:

- Home
- My Work
- Projects
- Calendar
- Files
- Reports
- Settings

For project pages, use:

- Overview
- Tasks
- Timeline
- Files
- Team
- Settings

Hide advanced strategy/canvas tools behind an `Advanced` or `Planning Tools` section.

Suggested code area:

- `app/Domain/Menu/Repositories/Menu.php`

Specific change:

- Keep `personal` and `company` menus.
- Replace the default `Make`, `Think`, and `Data Room` grouping with plain labels.
- Move canvas and strategy modules out of the default menu.
- Add a setting like `LEAN_SIMPLE_WORKFLOW=true` so existing power users can keep the full menu.

### 3. Rename confusing labels

Use simple words everywhere. Current language includes terms such as `blueprints`, `canvases`, `dataroom`, `materialize`, `story points`, and `milestones`. These are useful for advanced teams but hard for general users.

Change labels:

- `Todos` -> `Tasks`
- `Milestones` -> `Timeline`
- `Dataroom` -> `Files`
- `Blueprints` -> `Planning`
- `Ideas` -> `Ideas`
- `Story Points` -> `Size`
- `Effort` -> `Size`
- `Current Project` -> `Active Project`
- `Client/Product` -> `Client`

Suggested code areas:

- `app/Language`
- `app/Domain/Menu/Repositories/Menu.php`
- Ticket templates in `app/Domain/Tickets/Templates`
- Dashboard templates in `app/Domain/Dashboard/Templates`

### 4. Create one universal quick-add flow

Users should not need to know whether to create a ticket, milestone, idea, wiki page, or canvas item before capturing work.

Add:

- A global `+ New` button in the header.
- A quick-add modal with these choices:
  - Task
  - Project
  - Note
  - File
  - Invite teammate
- Default to `Task`.
- Let the user type a title first, then optionally add due date, project, owner, and details.

Suggested code areas:

- `app/Views/Templates/sections/header.blade.php`
- `app/Domain/Dashboard/Templates/partials/createEntityButton.blade.php`
- `app/Domain/Tickets/Hxcontrollers`
- `app/Domain/Tickets/Templates/partials`

Implementation pattern:

- Build the modal as a Blade partial.
- Use HTMX for create/update actions.
- Keep the first version focused on task creation.

### 5. Reduce fields on task creation

Task creation should be fast. Advanced fields can stay, but they should not be visible first.

Default task form should show:

- Title
- Project
- Owner
- Due date
- Status

Move these into an `More options` section:

- Size/story points
- Priority
- Milestone
- Tags
- Dependencies
- Detailed description
- Time estimate
- Subtasks

Suggested code areas:

- `app/Domain/Tickets/Templates/newTicket.tpl.php`
- `app/Domain/Tickets/Templates/newTicketModal.tpl.php`
- `app/Domain/Tickets/Templates/showTicketModal.blade.php`
- `app/Domain/Tickets/Controllers`

### 6. Replace dashboard density with a focused summary

The project dashboard currently mixes project metadata, checklist, description, latest todos, team, project updates, comments, progress, and milestone details. This is useful but dense.

Change the project dashboard to show:

- Project name and status
- Next 5 tasks
- Overdue tasks
- Current timeline/milestone
- Team members
- Recent activity

Move long description, project update history, detailed progress charts, and milestone lists into secondary tabs.

Suggested code area:

- `app/Domain/Dashboard/Templates/show.blade.php`

### 7. Add empty states that tell the user what to do next

Some current empty states use phrases like `Nothing to see here. Move on.` This is playful but not helpful for new users.

Change empty states to clear actions:

- No tasks: `Create your first task`
- No project: `Create a project to group your work`
- No team: `Invite a teammate`
- No files: `Upload a file or add a link`
- No due dates: `Add due dates to see work on your calendar`

Suggested code areas:

- `app/Domain/Dashboard/Templates`
- `app/Domain/Tickets/Templates`
- `app/Domain/Projects/Templates`
- `app/Domain/Files/Templates`
- Shared component idea: `app/Views/Templates/components/emptyState.blade.php`

## Things To Add

### Guided setup checklist

Add a small checklist for new accounts and empty workspaces:

1. Create your first project.
2. Add three tasks.
3. Invite one teammate.
4. Set one due date.
5. Mark one task done.

This should appear on `My Work` and disappear after completion.

Suggested code areas:

- `app/Domain/Help`
- `app/Domain/Dashboard`
- Existing project checklist partials in `app/Domain/Projects/Templates/partials`

### Simple mode setting

Add an app-level setting called `Simple Workflow`.

When enabled:

- Hide advanced canvas modules from main navigation.
- Hide project strategy tools from regular users.
- Collapse advanced ticket fields.
- Use simplified labels.
- Prefer task/timeline wording over todo/milestone wording.

Suggested config:

- `LEAN_SIMPLE_WORKFLOW=true`

Suggested code areas:

- `app/Core/Configuration/DefaultConfig.php`
- `config/.env.sample`
- `app/Domain/Menu/Repositories/Menu.php`
- `app/Domain/Setting`

### Role-based navigation

Show fewer items for normal users.

Only these internal roles should be visible in user-facing role selection:

- Developer
- Team Lead
- Company Manager
- Administrator
- Owner

Do not expose these as selectable business roles:

- Read Only
- Commenter
- Editor

If the system still needs low-level compatibility with existing roles, keep them internally only and map them to the new product roles.

Developer:

- Home
- My Work
- Projects
- Calendar
- Files

Team lead:

- Everything developers see
- Team Work
- Weekly Plans
- Blockers
- Reports
- Timesheets if enabled

Company Manager:

- Company Overview
- Teams
- Weekly Reports
- Project Reports
- Clients

Administrator:

- Everything
- Users
- Company Settings
- Apps/Plugins
- Integrations

Owner:

- Everything administrators see
- Ownership and global system controls

Client:

- Client Portal
- Project progress
- Timeline
- Shared files
- Approved updates only

Suggested code area:

- `app/Domain/Menu/Repositories/Menu.php`

Suggested role mapping in the current codebase:

- Current `editor` role should be renamed or displayed as `Developer`.
- Current `teamlead` role maps directly to Team Lead.
- Current `manager` role can become Company Manager in user-facing language.
- Current `admin` role maps to Administrator.
- Current `owner` role maps to Owner.
- Current `readonly` and `commenter` roles should be hidden from the normal role dropdown.
- Client access should be handled as project-scoped external access, not as part of the internal role hierarchy.

Final role dropdown should show:

1. Developer
2. Team Lead
3. Company Manager
4. Administrator
5. Owner

Client visibility should be configured separately from employee role selection.

### Better project creation wizard

The current new project screen asks for several fields at once. Make project creation a short wizard:

Step 1:

- Project name
- What are we trying to accomplish?

Step 2:

- Start date
- End date
- Client

Step 3:

- Who can access this?
- Invite team members

Advanced fields should be available after the project exists.

Suggested code area:

- `app/Domain/Projects/Templates/newProject.tpl.php`

### Saved views for tasks

Users need simple views they can trust.

Add task views:

- My Tasks
- Due Today
- Overdue
- Unassigned
- Recently Updated
- Done
- Blocked
- Not Completed
- Assigned This Week

Suggested code areas:

- `app/Domain/Tickets`
- `app/Domain/Tickets/Hxcontrollers`
- `app/Domain/Tickets/Templates`

### Inline editing for common task changes

Make the task list editable without opening a full modal for every small change.

Add inline editing for:

- Status
- Owner
- Due date
- Project

The codebase already has HTMX patterns for ticket cards and timer buttons. Follow that approach.

Suggested code areas:

- `app/Domain/Tickets/Hxcontrollers/TicketCard.php`
- `app/Domain/Tickets/Templates/partials/ticketCard.blade.php`
- `app/Domain/Tickets/Htmx/HtmxTicketEvents.php`

### Consistent command language

Buttons should use direct verbs:

- Create task
- Save changes
- Invite
- Upload
- Mark done
- Add due date
- Move to project

Avoid internal nouns in button labels.

## Things To Hide Or Remove From The Default Experience

Do not delete advanced modules immediately. Hide them from the default workflow and keep them accessible through settings or an advanced menu.

Hide by default:

- Canvas variants
- Strategy blueprints
- Retrospective canvas
- Advanced idea boards
- Reports for non-managers
- Company administration for non-admins
- Timesheet management for users who do not track time
- Plugin marketplace for non-admins
- Integrations for non-admins

Delete or replace:

- Unhelpful empty-state copy.
- Duplicate create buttons that lead to different task creation experiences.
- Inline JavaScript in templates where a shared HTMX or JS module can handle the behavior.
- Repeated dashboard sections that show the same task or milestone information in multiple ways.

## Screen-by-Screen Recommendations

### Home / My Work

Make this the user command center.

Must include:

- Quick add task
- My tasks grouped by urgency
- Today calendar preview
- Recent project updates
- Small onboarding checklist for new users

Should not include:

- Advanced analytics
- Strategy tools
- Admin prompts
- Large illustrations after first login

### Project Dashboard

Make this answer: `What is happening in this project?`

Must include:

- Project health
- Next tasks
- Overdue tasks
- Timeline preview
- Team
- Recent activity

Move to tabs:

- Description
- Full task list
- Files
- Updates
- Settings

### Task Board

Make this answer: `What needs to move forward?`

Must include:

- Clear columns
- Fast status changes
- Filters for owner and due date
- Quick task creation
- Empty states per column

Avoid:

- Too many badges on every card.
- Showing size, milestone, dependency, priority, time, comments, and timer all at the same visual weight.

### Team Work

Make this answer: `How is my team doing this week?`

Must include:

- Month selector with team members grouped under it (matching spreadsheet layout)
- Team member list with each member's current week visible
- Current week selector
- Work assigned this week
- Done, blocked, and not completed counts
- Reasons for not completed work
- Support requested from manager
- Commitments and follow-ups with owner and deadline
- Next week carry-over actions

Primary actions:

- Create weekly plan for an employee
- Assign task
- Review employee week
- Give feedback (4 types: what's working, improve, what's helping, improve)
- Add growth and development notes
- Add commitments with owner and deadline
- Move unfinished work to next week

### My Weekly Plan

Make this answer: `What am I responsible for this week?`

Must include all 9 sections from the weekly plan:

- This week's assigned tasks with status controls
- Top priorities (numbered)
- Wins & Progress (employee fills in)
- Challenges / Blockers (employee fills in, with reason form)
- Manager Support Needed (employee fills in)
- Ideas / Suggestions (employee fills in)
- Feedback Exchange — employee sees manager feedback and can write their own:
  - Manager → Employee (What's working) — read only for employee
  - Manager → Employee (Improve) — read only for employee
  - Employee → Manager (What's helping) — editable by employee
  - Employee → Manager (Improve) — editable by employee
- Growth & Development — Current Focus Area, Support Needed, Next Milestone
- Next Week Priorities (Team Lead fills in, employee can view)
- Commitments & Follow-ups — Task, Owner, Deadline

Employees should not need to visit multiple project pages to understand their weekly responsibilities.

### Client Portal

Make this answer: `How much of my project is completed?`

Must include:

- Project completion percentage
- Current milestone or timeline status
- Completed work summary
- Upcoming work summary
- Shared files
- Approved project updates

Must hide by default:

- Internal team comments
- Employee performance notes
- Weekly 1:1 feedback
- Internal blocker reasons
- Private manager notes

### Project Creation

Make this answer: `What are we working on and who is involved?`

Required fields:

- Name

Recommended fields:

- Outcome
- Dates
- Team

Advanced fields:

- Client
- Access control
- Project type
- Custom configuration

### Settings

Split settings into plain categories:

- My Profile
- Notifications
- Company
- Users
- Apps
- Integrations
- Advanced

## Technical Implementation Plan

### Phase 1: Navigation and language

Change:

- Simplify `app/Domain/Menu/Repositories/Menu.php`.
- Add `LEAN_SIMPLE_WORKFLOW` config.
- Update common labels in language files.
- Hide advanced modules in simple mode.

Expected impact:

- Users see fewer choices immediately.
- No major data model changes needed.

### Phase 2: My Work dashboard

Change:

- Make `/dashboard/home` the default landing page.
- Add quick task creation.
- Add grouped task lists.
- Add helpful empty states.

Expected impact:

- Users always know where to start.

### Phase 3: Weekly team assignment workflow

Change:

- Add `Team Work` navigation for Team Leads.
- Add `My Weekly Plan` for Developers.
- Link weekly plan items to existing tickets/tasks.
- Require reasons when assigned work is blocked or not completed.
- Add weekly review and carry-over workflow.

Expected impact:

- Team Leads can assign work weekly.
- Employees have one clear weekly responsibility page.
- Managers get real reasons for missed work instead of guessing.

### Phase 4: Task creation and task cards

Change:

- Simplify new task modal.
- Move advanced fields behind `More options`.
- Add inline status, due date, and owner editing.
- Use HTMX partial updates.

Expected impact:

- Less form fatigue.
- Fewer full-page transitions.

### Phase 5: Project dashboard cleanup

Change:

- Reduce `app/Domain/Dashboard/Templates/show.blade.php` density.
- Move secondary sections into tabs or HTMX-loaded partials.
- Keep the first viewport focused on project health and next actions.

Expected impact:

- Project pages become easier to scan.

### Phase 6: Client project portal

Change:

- Add a client-safe project progress page.
- Show completion percentage, timeline, approved updates, and shared files.
- Hide internal notes, employee reasons, and team feedback.

Expected impact:

- Clients can check progress without needing internal access.
- Teams do not need to manually prepare basic status updates.

### Phase 7: Onboarding and empty states

Change:

- Add a reusable Blade empty-state component.
- Replace vague empty-state text.
- Add first-project and first-task guidance.

Expected impact:

- New users get direction without reading documentation.

## Suggested New Components

Add these shared Blade components:

- `app/Views/Templates/components/emptyState.blade.php`
- `app/Views/Templates/components/quickAdd.blade.php`
- `app/Views/Templates/components/actionBar.blade.php`
- `app/Views/Templates/components/simpleTabs.blade.php`

Add these HTMX partial patterns:

- `app/Domain/Tickets/Templates/partials/quickCreateTask.blade.php`
- `app/Domain/Tickets/Templates/partials/taskList.blade.php`
- `app/Domain/Dashboard/Templates/partials/myWorkSummary.blade.php`
- `app/Domain/Projects/Templates/partials/projectSetupChecklist.blade.php`
- `app/Domain/WeeklyPlanning/Templates/partials/weeklyPlan.blade.php`
- `app/Domain/WeeklyPlanning/Templates/partials/weeklyPlanItem.blade.php`
- `app/Domain/WeeklyPlanning/Templates/partials/blockerReason.blade.php`
- `app/Domain/WeeklyPlanning/Templates/partials/teamWeeklySummary.blade.php`

## Suggested Metrics

Track whether simplification works:

- Time from login to first task created.
- Number of clicks to create a task.
- Number of users who create a project and task on day one.
- Number of users who return to `My Work`.
- Number of empty project dashboards.
- Most used navigation items.
- Least used navigation items.
- Weekly plan completion rate.
- Number of blocked or not completed tasks with reasons.
- Number of tasks carried over to the next week.
- Average time for Team Lead review.
- Client portal views per project.

## Recommended First Sprint

Build these first:

1. Add `LEAN_SIMPLE_WORKFLOW`.
2. Simplify the default menu.
3. Rename the most confusing labels.
4. Make `My Work` the default landing page.
5. Add a quick task button.
6. Add `Team Work` and `My Weekly Plan`.
7. Require reasons for blocked or not completed weekly tasks.
8. Replace weak empty states.

This gives the app a simpler feel quickly without risky architectural changes.

## Final Product Rule

Every main screen should answer one question:

- Home: What needs my attention?
- Project: What is happening here?
- Tasks: What work needs to move?
- My Weekly Plan: What am I responsible for this week?
- Team Work: How is my team doing this week?
- Calendar: What is due when?
- Files: Where is the shared material?
- Reports: Are we on track?
- Client Portal: How much of my project is completed?
- Settings: How do I configure this?

If a screen answers more than one question, split it into tabs, hide advanced details, or move secondary information lower on the page.

---

## Client Requirement Request Workflow

### Overview

Clients should be able to raise new requirements directly from their Client Portal. Each request is sent as an important notification to the assigned Company Manager and Team Lead. Either of them can take ownership, discuss it with the team internally, and then respond by sharing an external Drive link (Google Drive, OneDrive, etc.) containing a document that shows whether each requested requirement has been fulfilled or not. Clients can track the status of every request they have submitted and mark them as reviewed once satisfied.

### Full Flow

1. Client opens their Client Portal and submits a new requirement as a text message.
2. The request is immediately sent as a high-priority notification to the assigned Company Manager and Team Lead.
3. Either the Company Manager or the Team Lead picks up the request and changes its status to `Under Review`.
4. They discuss it internally with the team.
5. Once the team has assessed the requirements, either of them uploads a response by attaching an external Drive link (Google Drive, OneDrive, etc.) pointing to a document that details which requirements are approved, partially fulfilled, rejected, or pending.
6. The status is updated to `Response Shared` and the client receives a notification.
7. The client views the link and the response document inside their Client Portal.
8. The client or the Manager/Team Lead can mark the request as `Completed / Reviewed`.

Multiple requirements can be submitted by the same client, each tracked independently with its own status and response link.

### Request Statuses

Each client requirement request moves through these statuses:

- `Submitted` — Client has sent the request, awaiting pickup.
- `Under Review` — A Company Manager or Team Lead has acknowledged it and is discussing internally.
- `Discussed` — Team has assessed the requirements internally.
- `Response Shared` — An external Drive link has been attached and the client has been notified.
- `Completed / Reviewed` — The request has been fully addressed and closed.

### Notifications

- When a client submits a request: high-priority notification sent to the assigned Company Manager and Team Lead.
- When the status changes to `Response Shared`: notification sent to the client.
- When the request is marked `Completed / Reviewed`: optional confirmation notification sent to the client.

### Client Portal View

The Client Portal should have a `My Requirements` section that shows:

- List of all submitted requirements (most recent first).
- Status badge on each request (`Submitted`, `Under Review`, `Discussed`, `Response Shared`, `Completed / Reviewed`).
- The submitted text of each request.
- Date submitted and date of last update.
- When a response is available: a clearly visible external link button (e.g. `View Response Document`) that opens the Drive link.
- A `Mark as Reviewed` button visible once the status is `Response Shared`.

### Manager / Team Lead View

Inside `Team Work` or the project view, add a `Client Requests` tab that shows:

- All open client requirement requests across their projects.
- Client name, project, request text, current status, and date submitted.
- A button to change status (`Pick Up`, `Mark as Discussed`, `Share Response Link`).
- An input field to paste the external Drive link when sharing the response.
- A button to mark the request `Completed / Reviewed`.

The `Share Response Link` action should:

- Accept a plain URL (Google Drive, OneDrive, or any external link).
- Save the link against the request.
- Automatically update status to `Response Shared`.
- Trigger a notification to the client.

### Data Model

Add the following table:

`zp_client_requirements`

- `id`
- `clientId` — the user or client account submitting the request
- `projectId` — the project this requirement belongs to
- `requestText` — the free-text requirement message from the client
- `status` — submitted, under_review, discussed, response_shared, completed
- `assignedTo` — userId of the Manager or Team Lead who picked it up (nullable until picked up)
- `responseLink` — external URL shared as the response document (nullable until shared)
- `notifiedAt` — timestamp when the client was last notified
- `createdAt`
- `updatedAt`

### Suggested Code Areas

- New domain or extend existing: `app/Domain/ClientRequirements` or extend `app/Domain/Projects`
- Client Portal template: `app/Domain/Projects/Templates/partials/clientRequirements.blade.php`
- Manager view partial: `app/Domain/Projects/Templates/partials/clientRequestsTab.blade.php`
- Notifications: `app/Domain/Notifications`
- New HTMX controller: `app/Domain/ClientRequirements/Hxcontrollers/RequirementRequest.php`
- Menu addition for Client role: `app/Domain/Menu/Repositories/Menu.php`

### Permissions

Client:

- Can submit new requirements.
- Can view all their own requests and statuses.
- Can open the response link when shared.
- Can mark a request as `Completed / Reviewed`.
- Cannot see internal team discussion notes.

Team Lead and Company Manager:

- Receive high-priority notifications for new client requests on their projects.
- Can pick up, update status, and share the response Drive link.
- Can mark requests as completed.

Administrator and Owner:

- Can see all client requests across all projects.
- Can reassign requests to a different Manager or Team Lead.

### Screen Answer

Add this to the Final Product Rule list:

- Client Requirements: What have I requested and what was the response?
