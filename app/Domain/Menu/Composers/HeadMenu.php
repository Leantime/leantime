<?php

namespace Leantime\Domain\Menu\Composers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Controller\Composer;
use Leantime\Core\Controller\Frontcontroller as FrontcontrollerCore;
use Leantime\Core\Theme;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Help\Services\Helper;
use Leantime\Domain\Menu\Repositories\Menu as MenuRepo;
use Leantime\Domain\Notifications\Services\Notifications as NotificationService;
use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
use Leantime\Domain\Users\Services\Users as UserService;

/**
 *
 */
class HeadMenu extends Composer
{
    public static array $views = [
        'menu::headMenu',
    ];

    private NotificationService $notificationService;
    private TimesheetService $timesheets;
    private UserService $userService;
    private AuthService $authService;
    private Helper $helperService;
    private Theme $themeCore;
    private MenuRepo $menuRepo;

    /**
     * @param NotificationService $notificationService
     * @param TimesheetService    $timesheets
     * @param UserService         $userService
     * @param AuthService         $authService
     * @return void
     */
    public function init(
        NotificationService $notificationService,
        TimesheetService $timesheets,
        UserService $userService,
        AuthService $authService,
        Helper $helperService,
        MenuRepo $menuRepo,
        Theme $themeCore
    ): void {
        $this->notificationService = $notificationService;
        $this->timesheets = $timesheets;
        $this->userService = $userService;
        $this->authService = $authService;
        $this->helperService = $helperService;
        $this->menuRepo = $menuRepo;
        $this->themeCore = $themeCore;
    }

    /**
     * @return array
     * @throws BindingResolutionException
     */
    public function with(): array
    {
        $notificationService = $this->notificationService;
        $notifications = array();
        $newnotificationCount = 0;
        if (session()->exists("userdata")) {
            $notifications = $notificationService->getAllNotifications(session("userdata.id"));
            $newnotificationCount = $notificationService->getAllNotifications(session("userdata.id"), true);
        }

        $nCount = is_array($newnotificationCount) ? count($newnotificationCount) : 0;
        $totalNotificationCount =
        $totalMentionCount =
        $totalNewMentions =
        $totalNewNotifications = 0;

        $menuType = $this->menuRepo->getSectionMenuType(FrontcontrollerCore::getCurrentRoute(), "project");

        foreach ($notifications as $notif) {
            if ($notif['type'] == 'mention') {
                $totalMentionCount++;
                if ($notif['read'] == 0) {
                    $totalNewMentions++;
                }
            } else {
                $totalNotificationCount++;
                if ($notif['read'] == 0) {
                    $totalNewNotifications++;
                }
            }
        }

        $user = false;
        if (session()->exists("userdata")) {
            $user = $this->userService->getUser(session("userdata.id"));
        }

        if (!$user) {
            $this->authService->logout();
            FrontcontrollerCore::redirect(BASE_URL . '/auth/login');
        }

        $modal = $this->helperService->getHelperModalByRoute(FrontcontrollerCore::getCurrentRoute());

        if (!session()->exists("companysettings.logoPath")) {
            session(["companysettings.logoPath" => $this->themeCore->getLogoUrl()]);
        }

        return [
            'newNotificationCount' => $nCount,
            'totalNotificationCount' => $totalNotificationCount,
            'totalMentionCount' => $totalMentionCount,
            'totalNewMentions' => $totalNewMentions,
            'totalNewNotifications' => $totalNewNotifications,
            'menuType' => $menuType,
            'notifications' => $notifications ?? [],
            'onTheClock' => session()->exists("userdata") ? $this->timesheets->isClocked(session("userdata.id")) : false,
            'activePath' => FrontcontrollerCore::getCurrentRoute(),
            'action' => FrontcontrollerCore::getActionName(),
            'module' => FrontcontrollerCore::getModuleName(),
            'user' => $user ?? [],
            'modal' => $modal,
        ];
    }
}
