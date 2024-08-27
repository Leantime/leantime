<?php

namespace Leantime\Domain\Api\Controllers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Clients\Repositories\Clients as ClientRepository;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Symfony\Component\HttpFoundation\Response;

/**
 * API-key controller.
 */
class ApiKey extends Controller
{
    private ProjectRepository $projectsRepo;
    private UserRepository $userRepo;
    private ClientRepository $clientsRepo;

    /**
     * init - initialize private variables
     *
     * @access public
     *
     * @param ProjectRepository $projectsRepo
     * @param UserRepository    $userRepo
     * @param ClientRepository  $clientsRepo
     *
     * @return void
     *
     * @throws BindingResolutionException
     */
    public function init(ProjectRepository $projectsRepo, UserRepository $userRepo, ClientRepository $clientsRepo): void
    {
        self::dispatch_event('api_key_init', $this);

        $this->projectsRepo = $projectsRepo;
        $this->userRepo = $userRepo;
        $this->clientsRepo = $clientsRepo;
    }

    /**
     * run - display template and edit data
     *
     * @access public
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function run(): Response
    {

        Auth::authOrRedirect([Roles::$owner, Roles::$admin], true);

        // Only admins
        if (isset($_GET['id']) === true) {
            $id = (int)($_GET['id']);
            $row = $this->userRepo->getUser($id);
            $edit = false;

            // Build values array
            $values = array(
                'firstname' => $row['firstname'],
                'lastname' => $row['lastname'],
                'user' => $row['username'],
                'phone' => $row['phone'],
                'status' => $row['status'],
                'role' => $row['role'],
                'hours' => $row['hours'],
                'wage' => $row['wage'],
                'clientId' => $row['clientId'],
                'source' =>  $row['source'],
                'pwReset' => $row['pwReset'],
            );

            if (isset($_POST['save'])) {
                if (isset($_POST[session("formTokenName")]) && $_POST[session("formTokenName")] == session("formTokenValue")) {
                    $values = array(
                        'firstname' => ($_POST['firstname'] ?? $row['firstname']),
                        'lastname' => '',
                        'user' => $row['username'],
                        'phone' => '',
                        'status' => ($_POST['status'] ?? $row['status']),
                        'role' => ($_POST['role'] ?? $row['role']),
                        'hours' => '',
                        'wage' => '',
                        'clientId' => '',
                        'password' => '',
                        'source' =>  'api',
                        'pwReset' => '',
                    );

                    $edit = true;
                } else {
                    $this->tpl->setNotification($this->language->__("notification.form_token_incorrect"), 'error');
                }
            }

            // Was everything okay?
            if ($edit !== false) {
                $this->userRepo->editUser($values, $id);

                if (isset($_POST['projects'])) {
                    if ($_POST['projects'][0] !== '0') {
                        $this->projectsRepo->editUserProjectRelations($id, $_POST['projects']);
                    } else {
                        $this->projectsRepo->deleteAllProjectRelations($id);
                    }
                } else {
                    // If projects are not set, all project assignments have been removed.
                    $this->projectsRepo->deleteAllProjectRelations($id);
                }
                $this->tpl->setNotification($this->language->__("notifications.key_updated"), 'success', 'apikey_updated');
            }

            // Get relations to projects
            $projects = $this->projectsRepo->getUserProjectRelation($id);

            $projectrelation = array();

            foreach ($projects as $projectId) {
                $projectrelation[] = $projectId['projectId'];
            }

            // Assign vars
            $this->tpl->assign('allProjects', $this->projectsRepo->getAll());
            $this->tpl->assign('roles', Roles::getRoles());
            $this->tpl->assign('clients', $this->clientsRepo->getAll());

            // Sensitive Form, generate form tokens
            $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
            session(["formTokenName" => substr(str_shuffle($permitted_chars), 0, 32)]);
            session(["formTokenValue" => substr(str_shuffle($permitted_chars), 0, 32)]);

            $this->tpl->assign('values', $values);
            $this->tpl->assign('relations', $projectrelation);

            $this->tpl->assign('status', $this->userRepo->status);
            $this->tpl->assign('id', $id);

            return $this->tpl->displayPartial('api.apiKey');
        } else {
            return $this->tpl->display('errors.error403');
        }
    }
}
