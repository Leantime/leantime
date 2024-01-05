<?php

namespace Leantime\Domain\Help\Composers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Composer;
use Leantime\Core\Frontcontroller as FrontcontrollerCore;
use Leantime\Domain\Help\Services\Helper;
use Leantime\Domain\Setting\Repositories\Setting;

/**
 *
 */
class Helpermodal extends Composer
{
    private Setting $settingsRepo;
    private Helper $helperService;

    public static array $views = [
        'help::helpermodal',
    ];

    /**
     * @return void
     */
    public function init(
        Setting $settingsRepo,
        Helper $helperService
    ): void {
        $this->settingsRepo = $settingsRepo;
        $this->helperService = $helperService;
    }


    /**
     * @return array
     * @throws BindingResolutionException
     */
    public function with(): array
    {
        $action = FrontcontrollerCore::getCurrentRoute();

        $showHelperModal = false;
        $completedOnboarding = $this->settingsRepo->getSetting("companysettings.completedOnboarding");

        $currentModal = $this->helperService->getHelperModalByRoute($action);

        if (
            $completedOnboarding == "1"
            && $currentModal !== 'notfound'
            && (isset($_SESSION['userdata']['settings']["modals"][$currentModal]) === false || $_SESSION['userdata']['settings']["modals"][$currentModal] == 0)
        ) {
            if (!isset($_SESSION['userdata']['settings']["modals"])) {
                $_SESSION['userdata']['settings']["modals"] = array();
            }

            if (!isset($_SESSION['userdata']['settings']["modals"][$currentModal])) {
                $_SESSION['userdata']['settings']["modals"][$currentModal] = 1;
                $showHelperModal = true;
            }
        }

        return [
            "completedOnboarding" => $completedOnboarding,
            "showHelperModal" => $showHelperModal,
            "currentModal" => $currentModal,
        ];
    }
}
