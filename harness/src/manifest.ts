/**
 * Page manifest — all user-facing pages to evaluate, organized by priority.
 *
 * URL pattern: /{module}/{controller}
 * The Frontcontroller maps: /tickets/showAll → Tickets/Controllers/ShowAll
 */

export interface PageEntry {
  id: string;
  path: string;
  name: string;
  priority: 1 | 2 | 3;
  /** Page requires a specific entity (project, ticket) to exist */
  needsContext?: boolean;
}

export const pages: PageEntry[] = [
  // =========================================================================
  // Priority 1: Core pages — most used, most likely broken
  // =========================================================================
  {
    id: "dashboard-home",
    path: "/dashboard/home",
    name: "Dashboard Home",
    priority: 1,
  },
  {
    id: "tickets-showAll",
    path: "/tickets/showAll",
    name: "All Tickets (Table)",
    priority: 1,
  },
  {
    id: "tickets-showKanban",
    path: "/tickets/showKanban",
    name: "Kanban Board",
    priority: 1,
  },
  {
    id: "tickets-showList",
    path: "/tickets/showList",
    name: "Tickets List View",
    priority: 1,
  },
  {
    id: "tickets-showTicket",
    path: "/tickets/showTicket",
    name: "Ticket Detail",
    priority: 1,
    needsContext: true,
  },
  {
    id: "projects-showAll",
    path: "/projects/showAll",
    name: "All Projects",
    priority: 1,
  },
  {
    id: "projects-showMy",
    path: "/projects/showMy",
    name: "My Projects",
    priority: 1,
  },
  {
    id: "projects-showProject",
    path: "/projects/showProject",
    name: "Project Detail",
    priority: 1,
    needsContext: true,
  },

  // =========================================================================
  // Priority 2: Secondary core pages
  // =========================================================================
  {
    id: "timesheets-showMy",
    path: "/timesheets/showMy",
    name: "My Timesheets",
    priority: 2,
  },
  {
    id: "timesheets-showAll",
    path: "/timesheets/showAll",
    name: "All Timesheets",
    priority: 2,
  },
  {
    id: "timesheets-showMyList",
    path: "/timesheets/showMyList",
    name: "Timesheet List",
    priority: 2,
  },
  {
    id: "calendar-showMyCalendar",
    path: "/calendar/showMyCalendar",
    name: "My Calendar",
    priority: 2,
  },
  {
    id: "wiki-show",
    path: "/wiki/show",
    name: "Wiki",
    priority: 2,
  },
  {
    id: "tickets-roadmap",
    path: "/tickets/roadmap",
    name: "Roadmap",
    priority: 2,
  },
  {
    id: "tickets-roadmapAll",
    path: "/tickets/roadmapAll",
    name: "Roadmap All",
    priority: 2,
  },
  {
    id: "tickets-showAllMilestones",
    path: "/tickets/showAllMilestones",
    name: "All Milestones",
    priority: 2,
  },
  {
    id: "tickets-showAllMilestonesOverview",
    path: "/tickets/showAllMilestonesOverview",
    name: "Milestones Overview",
    priority: 2,
  },
  {
    id: "tickets-showProjectCalendar",
    path: "/tickets/showProjectCalendar",
    name: "Project Calendar",
    priority: 2,
  },
  {
    id: "reports-show",
    path: "/reports/show",
    name: "Reports",
    priority: 2,
  },
  {
    id: "users-editOwn",
    path: "/users/editOwn",
    name: "Edit Own Profile",
    priority: 2,
  },
  {
    id: "users-showAll",
    path: "/users/showAll",
    name: "All Users",
    priority: 2,
  },
  {
    id: "setting-editCompanySettings",
    path: "/setting/editCompanySettings",
    name: "Company Settings",
    priority: 2,
  },
  {
    id: "tickets-newTicket",
    path: "/tickets/newTicket",
    name: "New Ticket Form",
    priority: 2,
  },

  // =========================================================================
  // Priority 3: Canvas & Strategy pages
  // =========================================================================
  {
    id: "strategy-showBoards",
    path: "/strategy/showBoards",
    name: "Strategy Boards",
    priority: 3,
  },
  {
    id: "goalcanvas-dashboard",
    path: "/goalcanvas/dashboard",
    name: "Goals Dashboard",
    priority: 3,
  },
  {
    id: "ideas-advancedBoards",
    path: "/ideas/advancedBoards",
    name: "Ideas Board",
    priority: 3,
  },
  {
    id: "retroscanvas-boardDialog",
    path: "/retroscanvas/boardDialog",
    name: "Retro Board",
    priority: 3,
  },
  {
    id: "leancanvas-boardDialog",
    path: "/leancanvas/boardDialog",
    name: "Lean Canvas",
    priority: 3,
  },
  {
    id: "swotcanvas-boardDialog",
    path: "/swotcanvas/boardDialog",
    name: "SWOT Canvas",
    priority: 3,
  },
  {
    id: "sbcanvas-boardDialog",
    path: "/sbcanvas/boardDialog",
    name: "Story Board Canvas",
    priority: 3,
  },
  {
    id: "riskscanvas-boardDialog",
    path: "/riskscanvas/boardDialog",
    name: "Risks Canvas",
    priority: 3,
  },
  {
    id: "gamecenter-launch",
    path: "/gamecenter/launch",
    name: "Game Center",
    priority: 3,
  },
];

/** Get pages filtered by priority */
export function getPagesByPriority(maxPriority: 1 | 2 | 3 = 3): PageEntry[] {
  return pages
    .filter((p) => p.priority <= maxPriority)
    .sort((a, b) => a.priority - b.priority);
}

/** Get a specific page by ID */
export function getPage(id: string): PageEntry | undefined {
  return pages.find((p) => p.id === id);
}
