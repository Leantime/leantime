<?php

namespace Leantime\Domain\Menu\Composers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Frontcontroller as FrontcontrollerCore;
use Leantime\Core\Composer;
use Leantime\Domain\Notifications\Services\Notifications as NotificationService;
use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
use Leantime\Domain\Users\Services\Users as UserService;
use Leantime\Domain\Auth\Services\Auth as AuthService;

/**
 *
 */

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
        AuthService $authService
    ): void {
        $this->notificationService = $notificationService;
        $this->timesheets = $timesheets;
        $this->userService = $userService;
        $this->authService = $authService;
    }

    /**
     * @return array
     */
    /**
     * @return array
     * @throws BindingResolutionException
     */
    public function with(): array
    {
        $notificationService = $this->notificationService;
        $notifications = array();
        $newnotificationCount = 0;
        if (isset($_SESSION['userdata'])) {
            $notifications = $notificationService->getAllNotifications($_SESSION['userdata']['id']);
            $newnotificationCount = $notificationService->getAllNotifications($_SESSION['userdata']['id'], true);
        }

        $nCount = is_array($newnotificationCount) ? count($newnotificationCount) : 0;
        $totalNotificationCount =
        $totalMentionCount =
        $totalNewMentions =
        $totalNewNotifications = 0;

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
        if (isset($_SESSION['userdata'])) {
            $user = $this->userService->getUser($_SESSION['userdata']['id']);
        }

        if ($user == false) {
            $this->authService->logout();
            FrontcontrollerCore::redirect(BASE_URL . '/auth/login');
        }

        $modal = 'dashboard';
        $requestParams = explode(BASE_URL, CURRENT_URL);
        $urlParts = explode('/', $requestParams[1] ?? '');

        if (count($urlParts) > 2) {
            $urlKey =  $urlParts[1] . '/' . $urlParts[2];

            $availableModals = [
                "tickets/showAll" => "backlog",
                "dashboard/show" => "dashboard",
                "dashboard/home" => "dashboard",
                "leancanvas/showCanvas" => "fullLeanCanvas",
                "leancanvas/simpleCanvas" => "simpleLeanCanvas",
                "ideas/showBoards" => "ideaBoard",
                "ideas/advancedBoards" => "advancedBoards",
                "tickets/roadmap" => "roadmap",
                "retroscanvas/showBoards" => "retroscanvas",
                "tickets/showKanban" => "kanban",
                "timesheets/showMy" => "mytimesheets",
                "projects/newProject" => "newProject",
                "projects/showProject" => "projectSuccess",
                "projects/showAll" => "showProjects",
                "clients/showAll" => "showClients",
            ];

            $modal = $availableModals[$urlKey] ?? 'notfound';
        }

        return [
            'newNotificationCount' => $nCount,
            'totalNotificationCount' => $totalNotificationCount,
            'totalMentionCount' => $totalMentionCount,
            'totalNewMentions' => $totalNewMentions,
            'totalNewNotifications' => $totalNewNotifications,
            'notifications' => $notifications ?? [],
            'onTheClock' => isset($_SESSION['userdata']) ? $this->timesheets->isClocked($_SESSION["userdata"]["id"]) : false,
            'activePath' => FrontcontrollerCore::getCurrentRoute(),
            'action' => FrontcontrollerCore::getActionName(),
            'module' => FrontcontrollerCore::getModuleName(),
            'user' => $user ?? [],
            'modal' => $modal,
        ];
    }
}
