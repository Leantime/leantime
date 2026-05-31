<?php

namespace Leantime\Domain\Users\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Users\Services\Users as UserService;
use Symfony\Component\HttpFoundation\Response;

class PatchUserSettings extends Controller
{
    private Auth $authService;

    private UserService $userService;

    /**
     * Initializes dependencies.
     */
    public function init(
        Auth $authService,
        UserService $userService
    ): void {
        $this->authService = $authService;
        $this->userService = $userService;
    }

    /**
     * Handles PATCH requests for user UI settings (e.g. dismissing modals).
     *
     * @param  array  $params  Request parameters
     */
    public function patch(array $params): Response
    {
        if (! $this->authService->isLoggedIn()) {
            return $this->tpl->displayJson(['status' => 'error', 'message' => 'Not authorized'], 401);
        }

        // Handle modal dismissal updates
        if (
            isset($params['patchModalSettings'], $params['settings'])
            && $params['patchModalSettings'] == 1
        ) {
            $permanent = isset($params['permanent']) && $params['permanent'] == 1;
            $this->userService->saveModalDismissal($params['settings'], $permanent);

            return $this->tpl->displayJson(['status' => 'success']);
        }

        return $this->tpl->displayJson(['status' => 'error', 'message' => 'Invalid request'], 400);
    }
}
