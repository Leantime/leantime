<?php

namespace Leantime\Domain\Help\Services;

use Leantime\Core\Events\DispatchesEvents;
use Leantime\Domain\Help\Contracts\OnboardingSteps;
use Leantime\Domain\Projects\Services\Projects;
use Leantime\Domain\Setting\Repositories\Setting;

/**
 *
 */
class ProjectIntroStep implements OnboardingSteps
{
    use DispatchesEvents;

    public function __construct(
        private Setting $settingsRepo,
        private Projects $projectService
    ) {
    }

    /**
     * Get the title of the current project.
     *
     * @return string The title of the current project.
     */
    public function getTitle(): string
    {
        return "Name your project";
    }

    /**
     * Get the action for the current request.
     *
     * @return string The action for the current request.
     */
    public function getAction(): string
    {
        // TODO: Implement getAction() method.
        return "ProjectIntro";
    }

    /**
     * Retrieves the template for the project introduction step.
     *
     * @return string The template name for the project introduction step.
     */
    public function getTemplate(): string
    {
        return "help.projectIntroStep";
    }


    /**
     * Handle the given parameters.
     *
     * @param array $params The parameters passed to the handle method.
     * @return bool Returns true on success.
     */
    public function handle($params): bool
    {

        if (isset($params['projectname'])) {
            $this->projectService->patch(session("currentProject"), array("name" => $_POST['projectname']));
            $this->projectService->changeCurrentSessionProject(session("currentProject"));
        }

        $this->settingsRepo->saveSetting("companysettings.completedOnboarding", true);

        return true;
    }
}
