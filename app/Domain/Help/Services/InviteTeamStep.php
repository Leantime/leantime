<?php

namespace Leantime\Domain\Help\Services;

use Leantime\Core\Eventhelpers;
use Leantime\Core\Frontcontroller;
use Leantime\Domain\Help\Contracts\OnboardingSteps;
use Leantime\Domain\Projects\Services\Projects;
use Leantime\Domain\Setting\Repositories\Setting;
use Leantime\Domain\Users\Services\Users;

/**
 *
 */
class InviteTeamStep implements OnboardingSteps
{
    use Eventhelpers;

    public function __construct(
        private Setting $settingsRepo,
        private Projects $projectService,
        private Users $userService
    ) {
    }

    public function getTitle(): string
    {
        return "Invite your team";
    }

    public function getAction(): string
    {
        // TODO: Implement getAction() method.
    }

    public function getTemplate(): string
    {
        return "help.inviteTeamStep";
    }


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
                        $this->projectService->editUserProjectRelations($userId, array($_SESSION['currentProject']));
                    }
                }
            }
        }

        return true;
    }
}
