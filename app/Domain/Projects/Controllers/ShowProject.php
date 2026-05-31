<?php

namespace Leantime\Domain\Projects\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Clients\Services\Clients as ClientService;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Symfony\Component\HttpFoundation\Response;

class ShowProject extends Controller
{
    private ProjectService $projectService;

    private TicketService $ticketService;

    private ClientService $clientService;

    /**
     * Initializes dependencies.
     */
    public function init(
        ProjectService $projectService,
        TicketService $ticketService,
        ClientService $clientService
    ): void {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager]);

        $this->projectService = $projectService;
        $this->ticketService = $ticketService;
        $this->clientService = $clientService;

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
            $this->projectService->saveMattermostWebhook($id, $_POST['mattermostWebhookURL']);
            $this->tpl->setNotification($this->language->__('notification.saved_mattermost_webhook'), 'success');
        }

        // Handle Slack integration
        if (isset($_POST['slackSave'])) {
            $this->projectService->saveSlackWebhook($id, $_POST['slackWebhookURL']);
            $this->tpl->setNotification($this->language->__('notification.saved_slack_webhook'), 'success');
        }

        // Handle Zulip integration
        if (isset($_POST['zulipSave'])) {
            $zulipResult = $this->projectService->saveZulipWebhook($id, $_POST);

            if ($zulipResult['saved']) {
                $this->tpl->setNotification($this->language->__('notification.saved_zulip_webhook'), 'success');
            } else {
                $this->tpl->setNotification($this->language->__('notification.error_zulip_webhook_fill_out_fields'), 'error');
            }

            $this->tpl->assign('zulipHook', $zulipResult['hook']);
        }

        // Handle Discord integration
        if (isset($_POST['discordSave'])) {
            $this->projectService->saveDiscordWebhooks($id, $_POST);
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
                    $this->projectService->editProjectAndNotify(
                        $values,
                        $id,
                        $project,
                        CURRENT_URL,
                        session('userdata.id'),
                        session('userdata.name')
                    );
                    $this->tpl->setNotification($this->language->__('notification.project_saved'), 'success');

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
        $settings = $this->projectService->getProjectIntegrationSettings($projectId);

        array_map([$this->tpl, 'assign'], array_keys($settings), array_values($settings));
    }

    /**
     * Assigns common template variables.
     */
    private function assignTemplateVars(int $projectId, array $project): void
    {
        $this->tpl->assign('availableUsers', $this->projectService->getAllUsers());
        $this->tpl->assign('clients', $this->clientService->getAll());
        $this->tpl->assign('todoStatus', $this->ticketService->getStatusLabels());
        $this->tpl->assign('employees', $this->projectService->getEmployees());
        $this->tpl->assign('project', $project);
        $this->tpl->assign('menuTypes', $this->projectService->getMenuTypes());
        $this->tpl->assign('projectTypes', $this->projectService->getProjectTypes());
        $this->tpl->assign('state', ['open', 'closed']);
        $this->tpl->assign('role', session('userdata.role'));
        $this->tpl->assign('projectMuteCount', $this->projectService->getMuteCountForProject($projectId));
    }
}
