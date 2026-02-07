# Leantime Node.js Rewrite Plan

This is a fork — no need for upstream compatibility, plugin marketplace support, or migration tooling from the original PHP codebase.

## Goal

Rewrite Leantime's core project management functionality in Node.js while keeping the existing MySQL database schema and HTMX-driven frontend approach.

---

## Phase 0: Strip Unnecessary Components (PHP Codebase)

Before rewriting, remove components that won't be ported. This reduces noise and makes it easier to identify what actually needs to be rewritten.

### Remove Entirely

| Component | Reason |
|-----------|--------|
| `app/Core/Plugins/` | Plugin system not needed in fork |
| `app/Domain/Plugins/` | Plugin management UI |
| `app/Domain/Modulemanager/` | Plugin/module marketplace |
| `app/Domain/Connector/` | External connector framework |
| `app/Domain/Ldap/` | LDAP auth — not porting |
| `app/Domain/Oidc/` | OIDC auth — not porting |
| `app/Domain/TwoFA/` | 2FA — reimplement later if needed |
| `app/Domain/Gamecenter/` | Gamification features |
| `app/Domain/CsvImport/` | CSV import tool |
| `app/Domain/Queue/` | Laravel queue system |
| `app/Domain/Cron/` | Cron job management |
| `app/Domain/Read/` | Read receipts |

### Consolidate Canvas Modules

All 17 canvas types (`Leancanvas`, `Swotcanvas`, `Retroscanvas`, etc.) are thin wrappers extending `app/Domain/Canvas/`. They differ only in field definitions and labels. In the rewrite, these become a single configurable canvas module with type definitions stored as data, not code.

**Canvas types to collapse into one module:**
Cpcanvas, Dbmcanvas, Eacanvas, Emcanvas, Goalcanvas, Insightscanvas, Lbmcanvas, Leancanvas, Minempathycanvas, Obmcanvas, Retroscanvas, Riskscanvas, Sbcanvas, Smcanvas, Sqcanvas, Swotcanvas, Valuecanvas

### Strip from Core

| Component | Reason |
|-----------|--------|
| `app/Core/Plugins/` | No plugin system |
| `app/Core/Events/` | Replace with standard Node.js event emitter |
| `app/Core/Db/` | Replace with Node ORM |
| `app/Core/UI/` | Replace with Node template engine |

---

## Phase 1: Foundation (Node.js)

Set up the new project alongside the existing PHP code (or in a new repo).

### Tech Stack

| Layer | Choice | Rationale |
|-------|--------|-----------|
| Runtime | Node.js 20+ | LTS, stable |
| Framework | Express or Fastify | Lightweight, flexible |
| ORM | Prisma or Drizzle | Type-safe, MySQL support, migration tools |
| Templates | EJS or Handlebars | Simple server-rendered HTML, works with HTMX |
| Auth | Passport.js + express-session | Session-based auth matching current behavior |
| CSS | Keep existing | Reuse all current CSS/Less/Tailwind as-is |
| JS | Keep existing | Reuse HTMX, TinyMCE, and app JS as-is |

### Tasks

- [ ] Initialize Node.js project with TypeScript
- [ ] Set up Express/Fastify with middleware stack
- [ ] Connect to existing MySQL database
- [ ] Generate ORM schema from existing `zp_*` tables
- [ ] Set up session-based auth (bcrypt password verification matching PHP's `password_hash`)
- [ ] Set up template engine and port the 2 layout files (`app`, `entry`)
- [ ] Port shared components from `app/Views/Templates/components/`
- [ ] Serve existing static assets from `public/assets/`
- [ ] Set up HTMX request handling (detect `HX-Request` header)

### Key Decisions

- **Keep the existing database schema** — the `zp_*` tables stay as-is. No migrations, no renames. This means existing data works immediately.
- **Keep the frontend assets** — CSS, JS, images, fonts all stay. Only the server-side rendering layer changes.
- **Match PHP password hashing** — use `bcryptjs` in Node which is compatible with PHP's `password_hash()` output.

---

## Phase 2: Core Domains

Port the essential domains. Each domain needs: routes, service logic, repository/ORM queries, and templates.

### Priority Order

Port in this order — each builds on the previous:

#### Tier 1: Auth & Navigation (Week 1-2)
| Domain | Controllers | Complexity | Notes |
|--------|------------|------------|-------|
| Auth | 5 | Medium | Login, logout, session management |
| Users | 8 | Medium | User CRUD, roles, profile |
| Setting | 4 | Low | System settings |
| Menu | 1 | Low | Navigation rendering |
| Install | 3 | Low | First-run setup wizard |

#### Tier 2: Project Management Core (Week 3-5)
| Domain | Controllers | Complexity | Notes |
|--------|------------|------------|-------|
| Projects | 10 | High | Project CRUD, access control, settings |
| Clients | 4 | Low | Client/organization management |
| Tickets | 20+ | High | The big one — tasks, subtasks, all views |
| Sprints | 5 | Medium | Sprint management |
| Tags | 2 | Low | Tagging system |
| Comments | 3 | Low | Comment threads on tickets |
| Reactions | 2 | Low | Emoji reactions |
| Entityrelations | 2 | Low | Generic entity relationships |

#### Tier 3: Supporting Features (Week 6-7)
| Domain | Controllers | Complexity | Notes |
|--------|------------|------------|-------|
| Timesheets | 6 | Medium | Time tracking |
| Calendar | 4 | Medium | Calendar views |
| Files | 3 | Medium | File uploads/attachments |
| Dashboard | 3 | Medium | Dashboard widgets |
| Reports | 3 | Medium | Project reports |
| Notifications | 4 | Medium | In-app + email notifications |
| Wiki | 5 | Medium | Wiki/documentation pages |

#### Tier 4: Strategy & Canvas (Week 8)
| Domain | Controllers | Complexity | Notes |
|--------|------------|------------|-------|
| Strategy | 3 | Medium | Strategy boards |
| Canvas | 8 | Medium | Generic canvas engine (replaces all 17 canvas types) |
| Ideas | 4 | Low | Idea board |

#### Tier 5: Remaining (Week 9+)
| Domain | Controllers | Complexity | Notes |
|--------|------------|------------|-------|
| Audit | 2 | Low | Audit logging |
| Help | 2 | Low | Help/onboarding |
| Widgets | 3 | Low | Dashboard widget system |
| Errors | 2 | Low | Error pages |
| Environment | 1 | Low | Environment info |
| Api | 2 | Medium | JSON-RPC API layer (if needed) |

---

## Phase 3: Templates

### Approach

The biggest mechanical effort is porting ~200+ PHP/Blade templates to the Node template engine.

**Strategy:**
1. Start with layouts (`app.blade.php`, `entry.blade.php`) — these are the page skeletons
2. Port shared components (`pageheader`, `button`, `badge`, `tabs`, etc.)
3. Port domain templates as each domain is implemented
4. HTMX partials port directly — they're small HTML fragments

**What stays the same:**
- All CSS classes and structure
- All HTMX attributes (`hx-get`, `hx-post`, `hx-target`, etc.)
- All JavaScript interactions
- Theme system (CSS variables, dark/light modes)

**What changes:**
- `{{ $variable }}` Blade syntax → template engine equivalent
- `@foreach`, `@if` → template engine loops/conditionals
- `$this->language->__('key')` → `t('key')` or similar i18n function
- Blade components → template partials/includes

---

## Phase 4: Polish & Parity

- [ ] Port internationalization (`app/Language/` INI files → i18n library like `i18next`)
- [ ] Port email templates for notifications
- [ ] Implement file upload handling (local + S3)
- [ ] Add rate limiting and security middleware
- [ ] Port theme system (CSS variable switching)
- [ ] Ensure mobile responsiveness still works
- [ ] Performance testing against PHP version

---

## Database Tables Reference

These are the `zp_*` tables the ORM needs to map. No schema changes — use as-is:

**Core:**
- `zp_user` — Users and auth
- `zp_clients` — Client organizations
- `zp_projects` — Projects
- `zp_tickets` — Tasks/tickets/subtasks/milestones (the big table)
- `zp_sprints` — Sprints
- `zp_timesheets` — Time entries
- `zp_comment` — Comments
- `zp_file` — File metadata
- `zp_tags` — Tags
- `zp_entity_relationship` — Generic entity relations
- `zp_reactions` — Reactions
- `zp_notifications` — Notifications
- `zp_setting` — System settings
- `zp_audit` — Audit log

**Canvas:**
- `zp_canvas` — Canvas boards
- `zp_canvas_items` — Canvas items

**Wiki:**
- `zp_wiki` — Wiki categories
- `zp_wiki_articles` — Wiki articles
- `zp_wiki_comments` — Wiki comments

**Calendar:**
- `zp_calendar` — Calendar events

**Other:**
- `zp_dashboard_widgets` — Widget configuration
- `zp_queue` — Job queue (may not need)
- `zp_user_password_reset` — Password reset tokens

---

## What NOT to Port

These exist in the PHP codebase but are not needed for the fork:

- Plugin system and marketplace integration
- LDAP authentication
- OIDC authentication
- 2FA (can add later with a Node library)
- JSON-RPC API (unless external integrations need it — REST is simpler)
- Gamecenter
- CSV import
- Cron domain (use node-cron or system cron directly)
- Queue domain (use BullMQ or similar if needed)
- Module manager

---

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| PHP `password_hash` compatibility | `bcryptjs` is compatible — verify with existing user passwords early |
| Session format differences | New sessions — users will need to log in again after switch |
| Template porting is tedious | Port one domain at a time, test as you go |
| Missing edge cases in business logic | Keep PHP version running in parallel for reference |
| TinyMCE and JS plugins assume PHP endpoints | Update fetch URLs to new routes — same response format |
| Date/time handling differences | Use `dayjs` or `date-fns`, match the UTC-in-DB / user-TZ-in-UI pattern |

---

## Suggested File Structure (Node.js)

```
/
├── src/
│   ├── app.ts                  # Express/Fastify setup
│   ├── config/                 # Configuration (env loading)
│   ├── middleware/              # Auth, session, headers
│   ├── domains/
│   │   ├── auth/
│   │   │   ├── routes.ts
│   │   │   ├── service.ts
│   │   │   └── templates/
│   │   ├── tickets/
│   │   │   ├── routes.ts
│   │   │   ├── service.ts
│   │   │   ├── hx-routes.ts   # HTMX endpoints
│   │   │   └── templates/
│   │   ├── projects/
│   │   └── ...
│   ├── db/
│   │   ├── schema.prisma       # or drizzle schema
│   │   └── client.ts
│   ├── i18n/                   # Language files
│   ├── views/
│   │   ├── layouts/
│   │   └── components/
│   └── utils/
├── public/                     # Existing static assets (copy from PHP)
│   ├── assets/
│   └── theme/
├── package.json
└── tsconfig.json
```

---

## Getting Started

After stripping components in Phase 0:

```bash
# In a new directory or branch
npm init -y
npm install express prisma @prisma/client ejs express-session bcryptjs
npm install -D typescript @types/node @types/express ts-node

# Generate Prisma schema from existing DB
npx prisma db pull

# Start building Phase 1
```
