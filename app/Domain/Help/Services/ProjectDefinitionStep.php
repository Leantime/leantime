<?php

namespace Leantime\Domain\Help\Services;

use Leantime\Core\Events\DispatchesEvents;
use Leantime\Domain\Help\Contracts\OnboardingSteps;
use Leantime\Domain\Projects\Services\Projects;
use Leantime\Domain\Setting\Repositories\Setting;

/**
 *
 */
class ProjectDefinitionStep implements OnboardingSteps
{
    use DispatchesEvents;

    public function __construct(
        private Setting $settingsRepo,
        private Projects $projectService
    ) {}

    public function getTitle(): string
    {
       return "Describe your project";
    }

    /**
     * Retrieves the action of the current object.
     *
     * @return string The action of the current object.
     */
    public function getAction() : string{
        // TODO: Implement getAction() method.
        return "ProjectDefinition";
    }

    /**
     * Retrieves the template to render for the current object.
     *
     * @return string The template to render for the current object.
     */
    public function getTemplate() : string{
        return "help.projectDefinitionStep";
    }


    /**
     * Handles the given parameters and performs necessary operations.
     *
     * @param array $params The parameters passed to the method.
     * @return bool Returns true.
     */
    public function handle($params): bool
    {

        $description = "";

        if (isset($params['accomplish'])) {
            $description .= "<h3>" . __('label.what_are_you_trying_to_accomplish') . "</h3>";
            $description .= "" . $params['accomplish'];
        }

        if (isset($params['worldview'])) {
            $description .= "<br /><h3>" . __('label.how_does_the_world_look_like') . "</h3>";
            $description .= "" . $params['worldview'];
        }

        if (isset($params['whyImportant'])) {
            $description .= "<br /><h3>" . __('label.why_is_this_important') . "</h3>";
            $description .= "" . $params['whyImportant'];
        }

        $this->projectService->patch(session("currentProject"), array("details" => $description));
        $this->projectService->changeCurrentSessionProject(session("currentProject"));

        $this->settingsRepo->saveSetting("companysettings.completedOnboarding", true);

        return true;

    }

}
