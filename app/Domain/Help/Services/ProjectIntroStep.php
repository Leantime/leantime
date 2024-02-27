<?php

namespace Leantime\Domain\Help\Services;

use Leantime\Core\Eventhelpers;
use Leantime\Domain\Help\Contracts\OnboardingSteps;
use Leantime\Domain\Projects\Services\Projects;
use Leantime\Domain\Setting\Repositories\Setting;

/**
 *
 */
class ProjectIntroStep implements OnboardingSteps
{
    use Eventhelpers;

    public function __construct(
        private Setting $settingsRepo,
        private Projects $projectService
    ) {}

    public function getTitle(): string
    {
       return "Name your project";
    }

    public function getAction() : string{
        // TODO: Implement getAction() method.
    }

    public function getTemplate() : string{
        return "help.projectIntroStep";
    }


    public function handle($params): bool
    {

        if (isset($params['projectname'])) {

            $this->projectService->patch($_SESSION['currentProject'], array("name" => $_POST['projectname']));
            $this->projectService->changeCurrentSessionProject($_SESSION['currentProject']);

        }

        $this->settingsRepo->saveSetting("companysettings.completedOnboarding", true);

        return true;

    }

}
