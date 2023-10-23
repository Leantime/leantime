<?php

namespace Leantime\Domain\Help\Composers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\IncomingRequest as IncomingRequestCore;
use Leantime\Core\Frontcontroller as FrontcontrollerCore;
use Leantime\Core\Composer;
use Leantime\Domain\Help\Services\Helper;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Setting\Repositories\Setting;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Leantime\Domain\Setting\Services\Setting as SettingService;
use Leantime\Domain\Menu\Repositories\Menu as MenuRepository;

/**
 *
 */
class Helpermodal extends Composer
{
    private IncomingRequestCore $incomingRequest;
    private Setting $settingsRepo;
    private Helper $helperService;

    public static array $views = [
        'help::helpermodal',
    ];

    /**

     * @return void
     */
    public function init(
        IncomingRequestCore $request,
        Setting $settingsRepo,
        Helper $helperService
    ): void {
        $this->incomingRequest = $request;
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


        if ($completedOnboarding == "1"
            && $currentModal !== 'notfount'
            && (isset($_SESSION['userdata']['settings']["modals"][$currentModal]) === false || $_SESSION['userdata']['settings']["modals"][$currentModal] == 0)) {

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
            "currentModal" => $currentModal
        ];
    }
}
