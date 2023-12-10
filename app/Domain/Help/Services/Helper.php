<?php

namespace Leantime\Domain\Help\Services;

use Leantime\Core\Eventhelpers;

/**
 *
 */
class Helper
{
    use Eventhelpers;

    private $availableModals = [
        "tickets.showAll" => "backlog",
        "dashboard.show" => "projectDashboard",
        "dashboard.home" => "dashboard",
        "leancanvas.showCanvas" => "fullLeanCanvas",
        "leancanvas.simpleCanvas" => "simpleLeanCanvas",
        "ideas.showBoards" => "ideaBoard",
        "ideas.advancedBoards" => "advancedBoards",
        "tickets.roadmap" => "roadmap",
        "retroscanvas.showBoards" => "retroscanvas",
        "tickets.showKanban" => "kanban",
        "timesheets.showMy" => "mytimesheets",
        "projects.newProject" => "newProject",
        "projects.showAll" => "showProjects",
        "clients.showAll" => "showClients",
        "goalcanvas.dashboard" => "goalCanvas",
        "strategy.showBoards" => "blueprints",
        "wiki.show" => "wiki",
    ];

    public function __construct()
    {

        $this->availableModals = self::dispatch_filter("addHelperModal", $this->availableModals);
    }

    public function getAllHelperModals(): array
    {
        return $this->availableModals;
    }

    public function getHelperModalByRoute(string $route): string
    {
        return $this->availableModals[$route] ?? 'notfound';
    }
}
