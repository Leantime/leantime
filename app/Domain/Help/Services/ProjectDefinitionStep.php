<?php

namespace Leantime\Domain\Help\Services;

use Leantime\Core\Eventhelpers;
use Leantime\Domain\Help\Contracts\OnboardingSteps;
use Leantime\Domain\Projects\Services\Projects;
use Leantime\Domain\Setting\Repositories\Setting;

/**
 *
 */
class ProjectDefinitionStep implements OnboardingSteps
{
    use Eventhelpers;

    public function __construct(
        private Setting $settingsRepo,
        private Projects $projectService
    ) {}

    public function getTitle(): string
    {
       return "Describe your project";
    }

    public function getAction() : string{
        // TODO: Implement getAction() method.
    }

    public function getTemplate() : string{
        return "help.projectDefinitionStep";
    }


    public function handle($params): bool
    {

        $description = "";

        if (isset($params['accomplish'])) {
            $description .= __('label.what_are_you_trying_to_accomplish');
            $description .= "<br />".$params['accomplish'];
        }

        if (isset($params['worldview'])) {
            $description .= __('label.how_does_the_world_look_like');
            $description .= "<br />".$params['worldview'];
        }

        if (isset($params['whyImportant'])) {
            $description .= __('label.why_is_this_important');
            $description .= "<br />".$params['whyImportant'];
        }

        $this->projectService->patch($_SESSION['currentProject'], array("details" => $description));
        $this->projectService->changeCurrentSessionProject($_SESSION['currentProject']);

        $this->settingsRepo->saveSetting("companysettings.completedOnboarding", true);

        return true;

    }

}
