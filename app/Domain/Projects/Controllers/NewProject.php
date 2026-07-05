<?php

namespace Leantime\Domain\Projects\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Support\FromFormat;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Clients\Services\Clients as ClientService;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Symfony\Component\HttpFoundation\Response;

class NewProject extends Controller
{
    private ClientService $clientService;

    private ProjectService $projectService;

    /**
     * Initializes dependencies.
     */
    public function init(
        ClientService $clientService,
        ProjectService $projectService
    ): void {
        $this->clientService = $clientService;
        $this->projectService = $projectService;
    }

    /**
     * Displays the new project form.
     *
     * @param  array  $params  Request parameters
     */
    public function get(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager], true);

        if (! session()->exists('lastPage')) {
            session(['lastPage' => BASE_URL.'/projects/showAll']);
        }

        $defaultParent = $_GET['parent'] ?? '';
        if ($defaultParent === '') {
            // When creating a project from within a container project (e.g. a program or a
            // strategy), nest the new project under it by default so it's immediately
            // associated. The parent picker still lets the user change or clear it.
            $contextProject = $this->projectService->getProject(session('currentProject'));
            if (is_array($contextProject) && in_array($contextProject['type'] ?? '', ['program', 'strategy'], true)) {
                $defaultParent = (string) session('currentProject');
            }
        }

        $values = $this->projectService->getNewProjectDefaults($defaultParent);

        $this->tpl->assign('menuTypes', $this->projectService->getMenuTypes());
        $this->tpl->assign('project', $values);
        $this->tpl->assign('availableUsers', $this->projectService->getAllUsers());
        $this->tpl->assign('clients', $this->clientService->getAll());
        $this->tpl->assign('projectTypes', $this->projectService->getProjectTypes());
        $this->tpl->assign('info', '');

        return $this->tpl->display('projects.newProject');
    }

    /**
     * Handles new project form submission.
     *
     * @param  array  $params  Request parameters
     */
    public function post(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager], true);

        if (! session()->exists('lastPage')) {
            session(['lastPage' => BASE_URL.'/projects/showAll']);
        }

        $hourBudget = (! isset($_POST['hourBudget']) || $_POST['hourBudget'] == '' || $_POST['hourBudget'] == null)
            ? '0'
            : $_POST['hourBudget'];

        $assignedUsers = (isset($_POST['editorId']) && count($_POST['editorId']))
            ? $_POST['editorId']
            : [];

        // A project may only be nested under a CONTAINER (a program or a strategy) — never under
        // another regular project. Resolve a candidate parent from the form field, then the URL
        // ("Add Project" button), then the current project; but only keep it if that candidate is
        // actually a program/strategy. Anything else (including a regular project) leaves the new
        // project un-nested. (The service also validates this, since addProject is JSON-RPC reachable.)
        $parentCandidate = $_POST['parent'] ?? '';
        if ($parentCandidate === '' || $parentCandidate === '0') {
            $parentCandidate = $_GET['parent'] ?? '';
        }
        if ($parentCandidate === '' || $parentCandidate === '0') {
            $parentCandidate = (string) session('currentProject');
        }

        $parent = '';
        if ($parentCandidate !== '' && $parentCandidate !== '0') {
            $parentProject = $this->projectService->getProject((int) $parentCandidate);
            if (is_array($parentProject) && in_array($parentProject['type'] ?? '', ['program', 'strategy'], true)) {
                $parent = (string) $parentCandidate;
            }
        }

        $values = [
            'name' => $_POST['name'] ?? '',
            'details' => $_POST['details'] ?? '',
            'clientId' => $_POST['clientId'] ?? 0,
            'hourBudget' => $hourBudget,
            'assignedUsers' => $assignedUsers,
            'dollarBudget' => $_POST['dollarBudget'] ?? 0,
            'state' => $_POST['projectState'],
            'psettings' => $_POST['globalProjectUserAccess'],
            'menuType' => $_POST['menuType'] ?? 'default',
            'type' => $_POST['type'] ?? 'project',
            'parent' => $parent,
            'start' => format(value: $_POST['start'], fromFormat: FromFormat::UserDateStartOfDay)->isoDateTime(),
            'end' => $_POST['end'] ? format(value: $_POST['end'], fromFormat: FromFormat::UserDateEndOfDay)->isoDateTime() : '',
        ];

        if ($values['name'] === '') {
            $this->tpl->setNotification($this->language->__('notification.no_project_name'), 'error');
        } elseif ($values['clientId'] === '') {
            $this->tpl->setNotification($this->language->__('notification.no_client'), 'error');
        } else {
            $id = $this->projectService->addProject($values);
            $this->projectService->changeCurrentSessionProject($id);

            $this->projectService->notifyProjectCreated($id, $values['name'], session('userdata.name'));

            $this->tpl->sendConfetti();
            $this->tpl->setNotification(
                sprintf($this->language->__('notifications.project_created_successfully'), BASE_URL.'/leancanvas/simpleCanvas/'),
                'success',
                'project_created'
            );

            return Frontcontroller::redirect(BASE_URL.'/projects/showProject/'.$id);
        }

        $this->tpl->assign('menuTypes', $this->projectService->getMenuTypes());
        $this->tpl->assign('project', $values);
        $this->tpl->assign('availableUsers', $this->projectService->getAllUsers());
        $this->tpl->assign('clients', $this->clientService->getAll());
        $this->tpl->assign('projectTypes', $this->projectService->getProjectTypes());
        $this->tpl->assign('info', '');

        return $this->tpl->display('projects.newProject');
    }
}
