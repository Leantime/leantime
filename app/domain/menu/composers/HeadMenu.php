<?php

namespace leantime\domain\composers\menu;

use leantime\core;
use leantime\core\Composer;
use leantime\domain\services;

class HeadMenu extends Composer
{
    public static $views = [
        'menu::headMenu',
    ];

    private services\notifications $notificationService;
    private services\timesheets $timesheets;
    private services\users $userService;
    private services\auth $authService;

    public function init(
        services\notifications $notificationService,
        services\timesheets $timesheets,
        services\users $userService,
        services\auth $authService
    ) {
        $this->notificationService = $notificationService;
        $this->timesheets = $timesheets;
        $this->userService = $userService;
        $this->authService = $authService;
    }

    public function with()
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
            core\frontcontroller::redirect(BASE_URL . '/auth/login');
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
            'activePath' => core\frontcontroller::getCurrentRoute(),
            'user' => $user ?? [],
            'modal' => $modal,
        ];
    }
}
