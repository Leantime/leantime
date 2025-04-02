<?php

namespace Leantime\Domain\Users\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Users\Repositories\Users;

class PatchUserSettings extends Controller
{
    private Auth $authService;
    private Users $userRepository;

    public function init(
        Auth $authService,
        Users $userRepository
    ) {
        $this->authService = $authService;
        $this->userRepository = $userRepository;
    }

    /**
     * Handle PATCH requests
     */
    public function patch($params)
    {
        // Check if user is logged in
        if (!$this->authService->isLoggedIn()) {
            return $this->tpl->displayJson(['status' => 'error', 'message' => 'Not authorized'], 401);
        }

        $userId = $this->authService->getUserId();

        // Handle modal settings updates
        if (isset($params['patchModalSettings']) && $params['patchModalSettings'] == 1) {
            if (isset($params['settings'])) {
                $modalKey = htmlspecialchars($params['settings']);
                $permanent = isset($params['permanent']) && $params['permanent'] == 1;

                // Store in session
                if (!session()->exists('usersettings.modals')) {
                    session(['usersettings.modals' => []]);
                }

                session(['usersettings.modals.'.$modalKey => 1]);

                // If permanent, also store in user settings
                if ($permanent) {
                    $this->userRepository->updateUserSettings($userId, ['modals.'.$modalKey => 1]);
                }

                return $this->tpl->displayJson(['status' => 'success']);
            }
        }

        return $this->tpl->displayJson(['status' => 'error', 'message' => 'Invalid request'], 400);
    }
}
