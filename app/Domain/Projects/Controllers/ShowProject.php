<?php

namespace Leantime\Domain\Projects\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Clients\Services\Clients as ClientService;
use Leantime\Domain\Menu\Repositories\Menu as MenuRepository;
use Leantime\Domain\Notifications\Models\Notification;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Symfony\Component\HttpFoundation\Response;

class ShowProject extends Controller
{
    private ProjectService $projectService;

    private TicketService $ticketService;

    private ClientService $clientService;

    private UserRepository $userRepo;

    private MenuRepository $menuRepo;

    /**
     * Initializes dependencies.
     */
    public function init(
        ProjectService $projectService,
        TicketService $ticketService,
        ClientService $clientService,
        UserRepository $userRepo,
        MenuRepository $menuRepo
    ): void {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager]);

        $this->projectService = $projectService;
        $this->ticketService = $ticketService;
        $this->clientService = $clientService;
        $this->userRepo = $userRepo;
        $this->menuRepo = $menuRepo;

        if (! session()->exists('lastPage')) {
            session(['lastPage' => CURRENT_URL]);
        }
    }

    /**
     * Displays the project settings page.
     *
     * @param  array  $params  Request parameters
     */
    public function get(array $params): Response
    {
        if (! isset($params['id'])) {
            return $this->tpl->display('errors.error404', responseCode: 404);
        }

        $id = (int) $params['id'];

        if (Auth::userHasRole(Roles::$manager)) {
            if ($this->projectService->isUserAssignedToProject(session('userdata.id'), $id) === false) {
                return Frontcontroller::redirect(BASE_URL.'/errors/error403');
            }
        }

        $project = $this->projectService->getProject($id);

        if (! isset($project['id'])) {
            return Frontcontroller::redirect(BASE_URL.'/errors/error404');
        }

        if (session('currentProject') != $project['id']) {
            $this->projectService->changeCurrentSessionProject($project['id']);
        }

        session(['lastPage' => BASE_URL.'/projects/showProject/'.$id]);

        $project['assignedUsers'] = $this->projectService->getUsersAssignedToProject($id, true);

        $this->assignIntegrationSettings($id);
        $this->assignTemplateVars($id, $project);

        return $this->tpl->display('projects.showProject');
    }

    /**
     * Handles project settings form submissions.
     *
     * @param  array  $params  Request parameters
     */
    public function post(array $params): Response
    {
        if (! isset($params['id'])) {
            return $this->tpl->display('errors.error404', responseCode: 404);
        }

        $id = (int) $params['id'];

        if (Auth::userHasRole(Roles::$manager)) {
            if ($this->projectService->isUserAssignedToProject(session('userdata.id'), $id) === false) {
                return Frontcontroller::redirect(BASE_URL.'/errors/error403');
            }
        }

        $project = $this->projectService->getProject($id);

        if (! isset($project['id'])) {
            return Frontcontroller::redirect(BASE_URL.'/errors/error404');
        }

        if (session('currentProject') != $project['id']) {
            $this->projectService->changeCurrentSessionProject($project['id']);
        }

        // Handle Mattermost integration
        if (isset($_POST['mattermostSave'])) {
            $webhook = strip_tags($_POST['mattermostWebhookURL']);
            $this->projectService->saveProjectSetting($id, 'mattermostWebhookURL', $webhook);
            $this->tpl->setNotification($this->language->__('notification.saved_mattermost_webhook'), 'success');
        }

        // Handle Slack integration
        if (isset($_POST['slackSave'])) {
            $webhook = strip_tags($_POST['slackWebhookURL']);
            $this->projectService->saveProjectSetting($id, 'slackWebhookURL', $webhook);
            $this->tpl->setNotification($this->language->__('notification.saved_slack_webhook'), 'success');
        }

        // Handle Zulip integration
        if (isset($_POST['zulipSave'])) {
            $zulipHook = [
                'zulipURL' => strip_tags($_POST['zulipURL']),
                'zulipEmail' => strip_tags($_POST['zulipEmail']),
                'zulipBotKey' => strip_tags($_POST['zulipBotKey']),
                'zulipStream' => strip_tags($_POST['zulipStream']),
                'zulipTopic' => strip_tags($_POST['zulipTopic']),
            ];

            if (
                $zulipHook['zulipURL'] == '' ||
                $zulipHook['zulipEmail'] == '' ||
                $zulipHook['zulipBotKey'] == '' ||
                $zulipHook['zulipStream'] == '' ||
                $zulipHook['zulipTopic'] == ''
            ) {
                $this->tpl->setNotification($this->language->__('notification.error_zulip_webhook_fill_out_fields'), 'error');
            } else {
                $this->projectService->saveProjectSetting($id, 'zulipHook', serialize($zulipHook));
                $this->tpl->setNotification($this->language->__('notification.saved_zulip_webhook'), 'success');
            }

            $this->tpl->assign('zulipHook', $zulipHook);
        }

        // Handle Discord integration
        if (isset($_POST['discordSave'])) {
            for ($i = 1; $i <= 3; $i++) {
                $webhook = trim(strip_tags($_POST['discordWebhookURL'.$i]));
                $this->projectService->saveProjectSetting($id, 'discordWebhookURL'.$i, $webhook);
            }
            $this->tpl->setNotification($this->language->__('notification.saved_discord_webhook'), 'success');
        }

        // Handle status label settings
        if (isset($_POST['submitSettings'])) {
            if (isset($_POST['labelKeys']) && is_array($_POST['labelKeys']) && count($_POST['labelKeys']) > 0) {
                if ($this->ticketService->saveStatusLabels($_POST)) {
                    $this->tpl->setNotification($this->language->__('notification.new_status_saved'), 'success');
                } else {
                    $this->tpl->setNotification($this->language->__('notification.error_saving_status'), 'error');
                }
            } else {
                $this->tpl->setNotification($this->language->__('notification.at_least_one_status'), 'error');
            }
        }

        // Handle user assignment
        if (isset($_POST['saveUsers'])) {
            $assignedUsers = (isset($_POST['editorId']) && count($_POST['editorId']))
                ? $_POST['editorId']
                : [];

            $this->projectService->updateProjectUsers($id, $assignedUsers, $_POST);
            $this->tpl->setNotification($this->language->__('notifications.user_was_added_to_project'), 'success');
        }

        // Handle project save
        if (isset($_POST['save'])) {
            $values = [
                'name' => $_POST['name'],
                'details' => $_POST['details'],
                'clientId' => $_POST['clientId'],
                'state' => $_POST['projectState'],
                'hourBudget' => $_POST['hourBudget'],
                'dollarBudget' => $_POST['dollarBudget'],
                'psettings' => $_POST['globalProjectUserAccess'],
                'menuType' => $_POST['menuType'],
                'type' => $_POST['type'] ?? $project['type'],
                'parent' => $_POST['parent'] ?? '',
                'start' => isset($_POST['start']) && dtHelper()->isValidDateString($_POST['start']) ? dtHelper()->parseUserDateTime($_POST['start'])->formatDateTimeForDb() : '',
                'end' => isset($_POST['end']) && dtHelper()->isValidDateString($_POST['end']) ? dtHelper()->parseUserDateTime($_POST['end'])->formatDateTimeForDb() : '',
            ];

            if ($values['name'] !== '') {
                if ($this->projectService->hasTickets($id) && $values['state'] == 1) {
                    $this->tpl->setNotification($this->language->__('notification.project_has_tickets'), 'error');
                } else {
                    $this->projectService->editProject($values, $id);
                    $this->tpl->setNotification($this->language->__('notification.project_saved'), 'success');

                    $subject = sprintf($this->language->__('email_notifications.project_update_subject'), $id, $values['name']);
                    $message = sprintf(
                        $this->language->__('email_notifications.project_update_message'),
                        session('userdata.name'),
                        strip_tags($values['name'])
                    );

                    $notification = app()->make(Notification::class);
                    $notification->url = [
                        'url' => CURRENT_URL,
                        'text' => $this->language->__('email_notifications.project_update_cta'),
                    ];
                    $notification->entity = $project;
                    $notification->module = 'projects';
                    $notification->action = 'updated';
                    $notification->projectId = session('currentProject');
                    $notification->subject = $subject;
                    $notification->authorId = session('userdata.id');
                    $notification->message = $message;

                    $this->projectService->notifyProjectUsers($notification);

                    return Frontcontroller::redirect(BASE_URL.'/projects/showProject/'.$id);
                }
            } else {
                $this->tpl->setNotification($this->language->__('notification.no_project_name'), 'error');
            }
        }

        session(['lastPage' => BASE_URL.'/projects/showProject/'.$id]);

        $project['assignedUsers'] = $this->projectService->getUsersAssignedToProject($id, true);

        $this->assignIntegrationSettings($id);
        $this->assignTemplateVars($id, $project);

        return $this->tpl->display('projects.showProject');
    }

    /**
     * Loads and assigns integration settings to the template.
     */
    private function assignIntegrationSettings(int $projectId): void
    {
        $this->tpl->assign('mattermostWebhookURL', $this->projectService->getProjectSetting($projectId, 'mattermostWebhookURL'));
        $this->tpl->assign('slackWebhookURL', $this->projectService->getProjectSetting($projectId, 'slackWebhookURL'));

        for ($i = 1; $i <= 3; $i++) {
            $this->tpl->assign('discordWebhookURL'.$i, $this->projectService->getProjectSetting($projectId, 'discordWebhookURL'.$i));
        }

        $zulipWebhook = $this->projectService->getProjectSetting($projectId, 'zulipHook');
        if ($zulipWebhook == '') {
            $this->tpl->assign('zulipHook', [
                'zulipURL' => '',
                'zulipEmail' => '',
                'zulipBotKey' => '',
                'zulipStream' => '',
                'zulipTopic' => '',
            ]);
        } else {
            $this->tpl->assign('zulipHook', safe_unserialize($zulipWebhook, []));
        }
    }

    /**
     * Assigns common template variables.
     */
    private function assignTemplateVars(int $projectId, array $project): void
    {
        $this->tpl->assign('availableUsers', $this->userRepo->getAll());
        $this->tpl->assign('clients', $this->clientService->getAll());
        $this->tpl->assign('todoStatus', $this->ticketService->getStatusLabels());
        $this->tpl->assign('employees', $this->userRepo->getEmployees());
        $this->tpl->assign('project', $project);
        $this->tpl->assign('menuTypes', $this->menuRepo->getMenuTypes());
        $this->tpl->assign('projectTypes', $this->projectService->getProjectTypes());
        $this->tpl->assign('state', ['open', 'closed']);
        $this->tpl->assign('role', session('userdata.role'));
        $this->tpl->assign('projectMuteCount', $this->projectService->getMuteCountForProject($projectId));
    }
}
