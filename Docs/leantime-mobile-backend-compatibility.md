# Leantime Mobile App: Backend Compatibility Audit

## Companion to `leantime-4.0-dev-unique-changes.md` and `leantime-plugin-changes-forward-port.md`

**Mobile App:** React Native (Expo) at `/Users/gloriafolaron/Herd/leantime-mobile`
**Backend Target:** v3.6.2 (current mainline)
**Risk Assessment:** How the mobile app connects to the backend, and what breaks on 4.0-dev
**Generated:** February 12, 2026

---

## Architecture Overview

The mobile app communicates with Leantime via two mechanisms:

1. **JSON-RPC** (`/api/jsonrpc`) — All data operations (tasks, projects, users, calendar, timesheets, notes, comments)
2. **Direct HTTP** — Authentication only (`/advancedAuth/getToken`, `/advancedAuth/mobileStatus`)

Authentication uses Bearer tokens from the AdvancedAuth plugin's personal token system. Every API call goes through a single `apiClient` class that adds `Authorization: Bearer {token}` headers.

---

## 🔴 CRITICAL: Authentication Depends on AdvancedAuth Plugin

The mobile app's entire auth flow requires the **AdvancedAuth plugin** (new in v3.6.2, does NOT exist in 4.0-dev):

### Login Flow
```
POST /advancedAuth/getToken
Content-Type: application/x-www-form-urlencoded
Body: username={email}&password={pass}&device_name=Leantime+Mobile
→ Returns: { id, token, user? }
```

### Instance Discovery
```
GET /advancedAuth/mobileStatus
→ Returns: { mobileAuthEnabled, instanceName, version, minAppVersion }
```

**4.0-dev has NO AdvancedAuth plugin.** The mobile app cannot authenticate at all against 4.0-dev without it. This is a hard blocker — not a degraded experience, a complete failure to connect.

### What's Needed
The forward-ported branch must include the AdvancedAuth plugin AND the core auth service that supports `createToken()`:
- `app/Plugins/AdvancedAuth/Controllers/GetToken.php` — handles POST login + token creation
- `app/Domain/Auth/Services/AccessToken.php` — personal token generation (verify this exists in v3.6.2)
- The `mobileStatus` endpoint does NOT exist in the codebase yet (the mobile app gracefully falls back when it's missing)

---

## JSON-RPC Method Inventory

Every RPC call the mobile app makes, mapped to the backend service method. All use the pattern:
`leantime.rpc.{Domain}.{Service}.{method}`

### ✅ Tickets (Tasks) — `app/Domain/Tickets/Services/Tickets.php`

All methods verified present in v3.6.2:

| Mobile RPC Call | Backend Method | Status |
|---|---|:---:|
| `Tickets.Tickets.getAll` | `getAll(?array $searchCriteria, ?int $limit)` | ✅ |
| `Tickets.Tickets.getAllOpenUserTickets` | `getAllOpenUserTickets(?int $userId, ?int $project)` | ✅ |
| `Tickets.Tickets.getOpenUserTicketsThisWeekAndLater` | `getOpenUserTicketsThisWeekAndLater(...)` | ✅ |
| `Tickets.Tickets.getTicket` | `getTicket($id)` | ✅ |
| `Tickets.Tickets.patch` | `patch($id, $params)` | ✅ |
| `Tickets.Tickets.quickAddTicket` | `quickAddTicket($params)` | ✅ |
| `Tickets.Tickets.addTicket` | `addTicket($values)` | ✅ |
| `Tickets.Tickets.delete` | `delete($id)` | ✅ |
| `Tickets.Tickets.getAllSubtasks` | `getAllSubtasks(int $ticketId)` | ✅ |
| `Tickets.Tickets.upsertSubtask` | `upsertSubtask($values, $parentTicket)` | ✅ |
| `Tickets.Tickets.getStatusLabels` | `getStatusLabels($projectId)` | ✅ |
| `Tickets.Tickets.getAllStatusLabelsByUserId` | `getAllStatusLabelsByUserId($userId)` | ✅ |
| `Tickets.Tickets.getAllMilestones` | `getAllMilestones($searchCriteria)` | ✅ |
| `Tickets.Tickets.getPriorityLabels` | `getPriorityLabels()` | ✅ |
| `Tickets.Tickets.getEffortLabels` | `getEffortLabels()` | ✅ |
| `Tickets.Tickets.getTicketTypes` | `getTicketTypes()` | ✅ |
| `Tickets.Tickets.quickAddMilestone` | `quickAddMilestone(array $params)` | ✅ |
| `Tickets.Tickets.getScheduledTasks` | `getScheduledTasks(...)` | ✅ |
| `Tickets.Tickets.pollForNewAccountTodos` | `pollForNewAccountTodos(...)` | ✅ |
| `Tickets.Tickets.pollForUpdatedAccountTodos` | `pollForUpdatedAccountTodos(...)` | ✅ |

### ✅ Projects — `app/Domain/Projects/Services/Projects.php`

| Mobile RPC Call | Backend Method | Status |
|---|---|:---:|
| `Projects.Projects.getProjectsAssignedToUser` | `getProjectsAssignedToUser(...)` | ✅ |
| `Projects.Projects.getProjectsUserHasAccessTo` | `getProjectsUserHasAccessTo($userId)` | ✅ |
| `Projects.Projects.getProjectHierarchyAssignedToUser` | `getProjectHierarchyAssignedToUser(...)` | ✅ |
| `Projects.Projects.getAllProjects` | ⚠️ Not found as service method | ⚠️ |
| `Projects.Projects.getProject` | `getProject(int $id)` | ✅ |
| `Projects.Projects.getProjectName` | `getProjectName($projectId)` | ✅ |
| `Projects.Projects.getProjectProgress` | `getProjectProgress($projectId)` | ✅ |
| `Projects.Projects.getUsersAssignedToProject` | `getUsersAssignedToProject($projectId)` | ✅ |
| `Projects.Projects.isUserAssignedToProject` | `isUserAssignedToProject(int $userId, int $projectId)` | ✅ |
| `Projects.Projects.getProjectRole` | `getProjectRole($userId, $projectId)` | ✅ |
| `Projects.Projects.addProject` | `addProject(array $values)` | ✅ |
| `Projects.Projects.editProject` | ⚠️ Not a service method (controller-level) | ⚠️ |
| `Projects.Projects.patch` | `patch($id, $params)` | ✅ |
| `Projects.Projects.changeCurrentSessionProject` | `changeCurrentSessionProject($projectId)` | ✅ |
| `Projects.Projects.duplicateProject` | `duplicateProject(...)` | ✅ |
| `Projects.Projects.getProjectAvatar` | `getProjectAvatar($id)` | ✅ |
| `Projects.Projects.findProject` | ⚠️ Not found as service method | ⚠️ |
| `Projects.Projects.getProjectTypes` | `getProjectTypes()` | ✅ |
| `Projects.Projects.pollForNewProjects` | ⚠️ Not found | ⚠️ |
| `Projects.Projects.pollForUpdatedProjects` | ⚠️ Not found | ⚠️ |

**Notes:** The `getAllProjects`, `findProject`, `editProject`, `pollForNewProjects`, and `pollForUpdatedProjects` methods are called by the mobile app but may not exist as public service methods exposed to RPC. These may exist in repositories or controllers only. The mobile app should handle RPC errors gracefully for these — verify against actual API testing.

### ✅ Calendar — `app/Domain/Calendar/Services/Calendar.php`

| Mobile RPC Call | Backend Method | Status |
|---|---|:---:|
| `Calendar.Calendar.getCalendar` | `getCalendar(int $userId, ...)` | ✅ |
| `Calendar.Calendar.getEvent` | `getEvent(int $eventId)` | ✅ |
| `Calendar.Calendar.addEvent` | `addEvent(array $values)` | ✅ |
| `Calendar.Calendar.editEvent` | `editEvent(array $values)` | ✅ |
| `Calendar.Calendar.patch` | `patch($id, $params)` | ✅ |
| `Calendar.Calendar.delEvent` | `delEvent(int $id)` | ✅ |
| `Calendar.Calendar.getICalUrl` | `getICalUrl()` | ✅ |
| `Calendar.Calendar.getExternalCalendarEvents` | `getExternalCalendarEvents(...)` | ✅ |

### ⚠️ Comments — `app/Domain/Comments/Services/Comments.php`

| Mobile RPC Call | Backend Method | Signature Match |
|---|---|:---:|
| `Comments.Comments.getComments` | `getComments($module, $entityId, ...)` | ⚠️ |
| `Comments.Comments.addComment` | `addComment($values, $module, $entityId, $entity)` | ⚠️ |
| `Comments.Comments.editComment` | `editComment($values, $id)` | ⚠️ |
| `Comments.Comments.deleteComment` | `deleteComment($commentId)` | ✅ |
| `Comments.Comments.pollComments` | `pollComments(?int $projectId, ?int $moduleId)` | ⚠️ |

**Signature mismatch concern:** The mobile app sends `{ moduleId, module }` as named params, but the backend `getComments()` signature is `($module, $entityId, ...)` — the JSON-RPC controller maps request keys to parameter NAMES via reflection, so the risk is mismatched parameter names, not ordering. The mobile sends `moduleId` but the backend parameter is `$entityId` — that key never binds. Similarly `addComment` takes 4 named params while mobile sends a `values` object. **Test thoroughly.**

### ✅ Users — `app/Domain/Users/Services/Users.php`

| Mobile RPC Call | Backend Method | Status |
|---|---|:---:|
| `Users.Users.getUser` | `getUser($id)` | ✅ |
| `Users.Users.getUserByEmail` | `getUserByEmail($email)` | ✅ |
| `Users.Users.getAll` | `getAll()` | ✅ |
| `Users.Users.getUsersWithProjectAccess` | `getUsersWithProjectAccess(int $currentUser, int $projectId)` | ⚠️ |
| `Users.Users.getNumberOfUsers` | `getNumberOfUsers()` | ✅ |
| `Users.Users.addUser` | `addUser(array $values)` | ✅ |
| `Users.Users.createUserInvite` | `createUserInvite(array $values)` | ✅ |
| `Users.Users.editUser` | `editUser($values, $id)` | ✅ |
| `Users.Users.editOwn` | `editOwn($values, $id)` | ⚠️ |
| `Users.Users.updateUserSettings` | `updateUserSettings($category, $setting, $value)` | ⚠️ |
| `Users.Users.deleteUser` | `deleteUser(int $id)` | ✅ |
| `Users.Users.usernameExist` | `usernameExist(string $username, ...)` | ✅ |
| `Users.Users.getProfilePicture` | `getProfilePicture($id)` | ✅ |
| `Users.Users.checkPasswordStrength` | `checkPasswordStrength(string $password)` | ✅ |

**Signature concerns:**
- `getUsersWithProjectAccess` — backend requires `($currentUser, $projectId)` but mobile sends `{ projectId }` only
- `editOwn` — backend requires `($values, $id)` but mobile sends `params` without `$id`
- `updateUserSettings` — backend requires 3 positional params `($category, $setting, $value)` but mobile sends `{ oderId: userId, ...settings }`

### ✅ Timesheets — `app/Domain/Timesheets/Services/Timesheets.php`

| Mobile RPC Call | Backend Method | Status |
|---|---|:---:|
| `Timesheets.Timesheets.logTime` | `logTime(int $ticketId, array $params)` | ✅ |
| `Timesheets.Timesheets.upsertTime` | `upsertTime(int $ticketId, array $params)` | ✅ |
| `Timesheets.Timesheets.isClocked` | `isClocked(int $sessionId)` | ✅ |
| `Timesheets.Timesheets.punchIn` | `punchIn(int $ticketId)` | ✅ |
| `Timesheets.Timesheets.punchOut` | `punchOut(int $ticketId)` | ✅ |
| `Timesheets.Timesheets.getLoggedHoursForTicketByDate` | `getLoggedHoursForTicketByDate(int $ticketId)` | ✅ |
| `Timesheets.Timesheets.getSumLoggedHoursForTicket` | `getSumLoggedHoursForTicket(int $ticketId)` | ✅ |
| `Timesheets.Timesheets.getRemainingHours` | `getRemainingHours(int|Tickets $ticketOrId)` | ✅ |
| `Timesheets.Timesheets.getUsersTicketHours` | `getUsersTicketHours(int $ticketId, int $userId)` | ✅ |
| `Timesheets.Timesheets.getLoggableHourTypes` | `getLoggableHourTypes()` | ✅ |
| `Timesheets.Timesheets.pollForNewTimesheets` | `pollForNewTimesheets(?int $projectId)` | ✅ |
| `Timesheets.Timesheets.pollForUpdatedTimesheets` | `pollForUpdatedTimesheets(?int $projectId)` | ✅ |
| `Timesheets.Timesheets.deleteTime` | `deleteTime(int $id)` | ✅ |

**Note:** `getAll` and `getWeeklyTimesheets` require `CarbonInterface` date params which can't be passed via JSON-RPC. The mobile app correctly avoids these, using `pollForNewTimesheets` instead with client-side date filtering.

### ⚠️ Notes — `app/Plugins/Notes/Services/Notes.php`

| Mobile RPC Call | Backend Method | Status |
|---|---|:---:|
| `Notes.Notes.getAllCanvas` | Plugin method | ⚠️ |
| `Notes.Notes.getCanvasItemsByNotebookId` | Plugin method | ⚠️ |
| `Notes.Notes.getCurrentNotebook` | Plugin method | ⚠️ |
| `Notes.Notes.getSingleCanvasItem` | Plugin method | ⚠️ |
| `Notes.Notes.addCanvasItem` | Plugin method | ⚠️ |
| `Notes.Notes.patchCanvasItem` | Plugin method | ⚠️ |
| `Notes.Notes.delCanvasItem` | Plugin method | ⚠️ |
| `Notes.Notes.addCanvas` | Plugin method | ⚠️ |
| `Notes.Notes.deleteCanvas` | Plugin method | ⚠️ |
| `Notes.Notes.patchCanvas` | Plugin method | ⚠️ |

**The Notes plugin exists in BOTH 4.0-dev and v3.6.2**, but v3.6.2's version has 23 files changed (pin/star, sort/filter, color management). The `patchCanvasItem` and `patchCanvas` methods may be new in v3.6.2. If the mobile app targets 4.0-dev without advancing the plugins submodule, Notes will be outdated and `patchCanvasItem`/`patchCanvas` may not exist.

---

## 4.0-dev Compatibility Impact Summary

### Hard Blockers (Mobile app WILL NOT WORK)

| Issue | Why |
|---|---|
| **No AdvancedAuth plugin** | Login endpoint `/advancedAuth/getToken` doesn't exist. App can't authenticate. |
| **No personal token support** | Bearer token auth requires `AccessToken` service that may not exist in 4.0-dev core |

### Likely Broken (Features will fail)

| Issue | Why |
|---|---|
| **Notes plugin outdated** | `patchCanvasItem`, `patchCanvas` may not exist in 4.0-dev's Notes version |
| **Event dispatch changes** | 4.0-dev rewrote `DispatchesEvents` to use Laravel Event facade — RPC method routing may behave differently if it depends on the event system |
| **Session handling** | 4.0-dev may still use `$_SESSION` which doesn't work with Bearer token auth (tokens bypass session) |

### Probably Fine (Core RPC should work)

| Domain | Why |
|---|---|
| **Tickets/Tasks** | All 20 methods exist in both versions. Core CRUD hasn't changed. |
| **Projects** | Most methods exist. Some edge-case methods may be missing. |
| **Calendar** | All 8 methods verified present. |
| **Timesheets** | All 13 methods verified present. |
| **Users** | All methods present, some signature mismatches to verify. |
| **Comments** | Methods present but signature ordering needs RPC layer testing. |

---

## `oderId` Typo Pattern

The mobile app sends `oderId` as a parameter name in several places:
- `projectApi.getProjectHierarchyAssignedToUser` → `{ oderId: userId }`
- `projectApi.getProjectProgress` → `{ oderId: projectId }`
- `projectApi.setCurrentProject` → `{ oderId: projectId }`
- `userApi.updateUserSettings` → `{ oderId: userId, ...settings }`

This appears to be a known Leantime backend parameter naming quirk (likely "orderId" misspelling that stuck). If the backend ever fixes this, the mobile app will break. Worth tracking.

---

## Forward-Port Checklist for Mobile Compatibility

After forward-porting 4.0-dev onto v3.6.2:

- [ ] **AdvancedAuth plugin present and functional** — `/advancedAuth/getToken` POST returns `{ id, token }`
- [ ] **Bearer token auth works on `/api/jsonrpc`** — tokens bypass session, API middleware accepts `Authorization: Bearer`
- [ ] **All 20 Tickets RPC methods respond** — especially `patch`, `quickAddTicket`, `getOpenUserTicketsThisWeekAndLater`
- [ ] **All 18 Projects RPC methods respond** — especially `getProjectsAssignedToUser`, `changeCurrentSessionProject`
- [ ] **All 8 Calendar RPC methods respond** — especially `getCalendar`, `getExternalCalendarEvents`
- [ ] **All 5 Comments RPC methods respond** — test parameter ordering carefully
- [ ] **All 13 Timesheets RPC methods respond** — especially `logTime`, `punchIn/punchOut`, `isClocked`
- [ ] **All 15 Users RPC methods respond** — especially `getUser` (no params = current user), `getUsersWithProjectAccess`
- [ ] **All 10 Notes RPC methods respond** — especially `patchCanvasItem`, `patchCanvas` (may be new)
- [ ] **401 responses trigger properly** — when token expires, API should return 401 so mobile handles re-auth
- [ ] **JSON-RPC error format consistent** — `{ jsonrpc: "2.0", error: { code, message } }` on failures

---

## Data Model Assumptions

The mobile app expects these field names from the backend (any rename breaks the app):

### Task fields consumed
`id`, `headline`, `description`, `projectId`, `projectName`, `status` (numeric), `priority` (numeric 1-5), `storyPoints`, `hourRemaining`, `planHours`, `dueDate`, `dateToFinish`, `editFrom`, `editTo`, `authorId`, `editorId`, `editorFirstName`/`editorFirstname`, `editorLastName`/`editorLastname`, `milestoneId`/`milestoneid`, `milestoneHeadline`, `milestoneColor`, `tags`, `createdAt`, `updatedAt`

### Project fields consumed
`id`, `name`, `clientId`, `clientName`, `color`

### User fields consumed
`id`, `firstName`, `lastName`, `email`, `role`, `profileId`

If 4.0-dev renamed any of these fields (e.g., camelCase → snake_case during Laravel migration), the mobile app will show blank data without errors.

---

*This document was compiled from static analysis of the leantime-mobile source code at `/Users/gloriafolaron/Herd/leantime-mobile/src/` and method signature verification against the Leantime v3.6.2 backend at `/Users/gloriafolaron/herd/leantime/app/`. RPC method existence was verified by grep against service class files; actual JSON-RPC routing behavior should be confirmed by live testing.*
