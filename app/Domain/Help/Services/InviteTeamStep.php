<?php

namespace Leantime\Domain\Help\Services;

use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Template;
use Leantime\Domain\Help\Contracts\OnboardingSteps;
use Leantime\Domain\Projects\Services\Projects;
use Leantime\Domain\Setting\Repositories\Setting;
use Leantime\Domain\Users\Services\Users;

/**
 *
 */
class InviteTeamStep implements OnboardingSteps
{
    use DispatchesEvents;

    public function __construct(
        private Setting $settingsRepo,
        private Projects $projectService,
        private Users $userService,
        private Template $tplService
    ) {
    }

    public function getTitle(): string
    {
        return "Invite your team";
    }

    /**
     * Retrieves the action for the current request.
     *
     * This method is responsible for returning the action to be performed based on the current request.
     * The action is returned as a string.
     *
     * @return string The action to be performed.
     */
    public function getAction(): string
    {
        // TODO: Implement getAction() method.
        return "InviteTeam";
    }

    /**
     * Retrieves the template for the current request.
     *
     * This method is responsible for returning the template to be used for rendering the content based on the current request.
     * The template is returned as a string.
     *
     * @return string The template to be used for rendering the content.
     */
    public function getTemplate(): string
    {
        return "help.inviteTeamStep";
    }


    /**
     * Handles the given parameters for performing a specific action.
     *
     * This method is responsible for processing and handling the given parameters for performing a specific action.
     * It iterates over the parameters and checks if the corresponding email is set and not empty.
     * If the email is valid and does not exist as a username, it creates a new user invite and then establishes a relation
     * between the new user and the current project.
     * In the end, a success notification is set.
     *
     * @param array $params The parameters to be handled.
     * @return bool True if the handling was successful, false otherwise.
     */
    public function handle($params): bool
    {

        for ($i = 1; $i <= 3; $i++) {
            if (isset($params['email' . $i]) && $params['email' . $i] != '') {
                $values = array(
                    'firstname' => '',
                    'lastname' => '',
                    'user' => ($params['email' . $i]),
                    'phone' => '',
                    'role' => '20',
                    'password' => '',
                    'pwReset' => '',
                    'status' => '',
                    'clientId' => '',
                );

                if (filter_var($params['email' . $i], FILTER_VALIDATE_EMAIL)) {
                    if ($this->userService->usernameExist($params['email' . $i]) === false) {
                        $userId = $this->userService->createUserInvite($values);
                        $this->projectService->editUserProjectRelations($userId, array(session("currentProject")));
                    }
                }

                $this->tplService->setNotification(__("notification.invitation_sent"), "success", "user_invited");
            }
        }


        return true;
    }
}
