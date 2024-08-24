<?php

namespace Leantime\Domain\Help\Services;

use Leantime\Core\Events\DispatchesEvents;
use ProjectIntroStep;
use InviteTeamStep;

/**
 *
 */
class Helper
{
    use DispatchesEvents;

    private $availableModals = [
        "dashboard.show" => "dashboard",
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

    /**
     * Constructor for the class.
     * Initializes the availableModals property by dispatching the "addHelperModal" event.
     *
     * @return void
     */
    public function __construct()
    {

        $this->availableModals = self::dispatch_filter("addHelperModal", $this->availableModals);
    }

    /**
     * Returns an array of all available helper modals.
     *
     * @return array The array of available helper modals.
     */
    public function getAllHelperModals(): array
    {
        return $this->availableModals;
    }

    /**
     * Retrieves the corresponding helper modal for a given route.
     *
     * @param string $route The route for which to retrieve the helper modal.
     * @return string The helper modal associated with the given route. If not found, 'notfound' is returned.
     */
    public function getHelperModalByRoute(string $route): string
    {
        return $this->availableModals[$route] ?? 'notfound';
    }

    /**
     * Retrieves the first login steps.
     *
     * This method returns an array of steps that a user needs to follow during the first login.
     *
     * Each step consists of a template and a button label.
     *
     * @return array The first login steps.
     */
    public function getFirstLoginSteps(): array
    {

        $steps = array(
            0 => array("class" => "Leantime\Domain\Help\Services\ProjectIntroStep", "next" => 20),
            //10 => array("class" => "Leantime\Domain\Help\Services\ProjectDefinitionStep", "next" => 20),
            20 => array("class" => "Leantime\Domain\Help\Services\InviteTeamStep", "next" => "end"),
        );

        //make array of onboarding steps.
        $steps = self::dispatch_filter("filterSteps", $steps);

        return $steps;
    }


}
