<?php

namespace Leantime\Domain\Help\Composers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Controller\Composer;
use Leantime\Core\Controller\Frontcontroller as FrontcontrollerCore;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Help\Services\Helper;
use Leantime\Domain\Setting\Repositories\Setting;

class Helpermodal extends Composer
{
    private Setting $settingsRepo;

    private Helper $helperService;
    private Auth $authService;

    public static array $views = [
        'help::helpermodal',
    ];

    public function init(
        Setting $settingsRepo,
        Helper $helperService,
        Auth $authService
    ): void {
        $this->settingsRepo = $settingsRepo;
        $this->helperService = $helperService;
        $this->authService = $authService;
    }

    /**
     * @throws BindingResolutionException
     */
    public function with(): array
    {
        $action = FrontcontrollerCore::getCurrentRoute();

        $showHelperModal = false;
        $completedOnboarding = $this->settingsRepo->getSetting('companysettings.completedOnboarding');
        $isFirstLogin = $this->helperService->isFirstLogin($this->authService->getUserId());

        //Backwards compatibilty
        if($isFirstLogin && $completedOnboarding) {
            $isFirstLogin = false;
        }

        $currentModal = $this->helperService->getHelperModalByRoute($action);

        if (
            $isFirstLogin === false
            && $currentModal['template'] !== 'notfound'
            && (
                session()->exists('usersettings.modals.'.$currentModal['id']) === false
                || session('usersettings.modals.'.$currentModal['id']) == 0)
        ) {
            if (! session()->exists('usersettings.modals')) {
                session(['usersettings.modals' => []]);
            }

            if (! session()->exists('usersettings.modals.'.$currentModal['id'])) {
                session(['usersettings.modals.'.$currentModal['id'] => 1]);
                $showHelperModal = true;
            }
        }

        // For development purposes, always show the modal
        return [
            'completedOnboarding' => $completedOnboarding,
            'showHelperModal' => $showHelperModal,
            'currentModal' => is_array($currentModal) ? $currentModal['template'] : $currentModal,
            'isFirstLogin' => $isFirstLogin,
        ];
    }
}
