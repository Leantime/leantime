<?php

namespace Leantime\Domain\Projects\Controllers {

    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Controller\Frontcontroller;
    use Leantime\Core\Mailer as MailerCore;
    use Leantime\Core\Support\FromFormat;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Auth\Services\Auth;
    use Leantime\Domain\Clients\Repositories\Clients as ClientRepository;
    use Leantime\Domain\Menu\Repositories\Menu as MenuRepository;
    use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;
    use Leantime\Domain\Queue\Repositories\Queue as QueueRepository;
    use Leantime\Domain\Users\Repositories\Users as UserRepository;

    /**
     *
     */
    class NewProject extends Controller
    {
        private ProjectRepository $projectRepo;
        private MenuRepository $menuRepo;
        private UserRepository $userRepo;
        private ClientRepository $clientsRepo;
        private QueueRepository $queueRepo;
        private ProjectService $projectService;

        /**
         * init - initialize private variables
         *
         * @access public
         */
        public function init(
            ProjectRepository $projectRepo,
            MenuRepository $menuRepo,
            UserRepository $userRepo,
            ClientRepository $clientsRepo,
            QueueRepository $queueRepo,
            ProjectService $projectService
        ) {
            $this->projectRepo = $projectRepo;
            $this->menuRepo = $menuRepo;
            $this->userRepo = $userRepo;
            $this->clientsRepo = $clientsRepo;
            $this->queueRepo = $queueRepo;
            $this->projectService = $projectService;
        }

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {
            Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager], true);

            if (!session()->exists("lastPage")) {
                session(["lastPage" => BASE_URL . "/projects/showAll"]);
            }

            $msgKey = '';
            $values = array(
                'id' => '',
                'name' => '',
                'details' => '',
                'clientId' => '',
                'hourBudget' => '',
                'assignedUsers' => array(session("userdata.id")),
                'dollarBudget' => '',
                'state' => '',
                'menuType' => MenuRepository::DEFAULT_MENU,
                'type' => 'project',
                'parent' => $_GET['parent'] ?? '',
                'psettings' => '',
                'start' => '',
                'end' => '',
            );

            if (isset($_POST['save']) === true) {

                if (!isset($_POST['hourBudget']) || $_POST['hourBudget'] == '' || $_POST['hourBudget'] == null) {
                    $hourBudget = '0';
                } else {
                    $hourBudget = $_POST['hourBudget'];
                }


                if (isset($_POST['editorId']) && count($_POST['editorId'])) {
                    $assignedUsers = $_POST['editorId'];
                } else {
                    $assignedUsers = array();
                }


                $mailer = app()->make(MailerCore::class);

                $values = array(
                    'name' => $_POST['name'] ?? "",
                    'details' => $_POST['details'] ?? "",
                    'clientId' => $_POST['clientId'] ?? 0,
                    'hourBudget' => $hourBudget,
                    'assignedUsers' => $assignedUsers,
                    'dollarBudget' => $_POST['dollarBudget'] ?? 0,
                    'state' => $_POST['projectState'],
                    'psettings' => $_POST['globalProjectUserAccess'],
                    'menuType' => $_POST['menuType'] ?? 'default',
                    'type' => $_POST['type']  ?? 'project',
                    'parent' => $_POST['parent'] ?? '',
                    'start' => format(value: $_POST['start'], fromFormat: FromFormat::UserDateStartOfDay)->isoDateTime(),
                    'end' => $_POST['end'] ? format(value: $_POST['end'], fromFormat: FromFormat::UserDateEndOfDay)->isoDateTime() : '',
                );

                if ($values['name'] === '') {
                    $this->tpl->setNotification($this->language->__("notification.no_project_name"), 'error');
                } elseif ($values['clientId'] === '') {
                    $this->tpl->setNotification($this->language->__("notification.no_client"), 'error');
                } else {
                    $projectName = $values['name'];
                    $id = $this->projectRepo->addProject($values);
                    $this->projectService->changeCurrentSessionProject($id);

                    $users = $this->projectRepo->getUsersAssignedToProject($id);

                    $mailer->setContext('project_created');
                    $mailer->setSubject($this->language->__('email_notifications.project_created_subject'));
                    $actual_link = BASE_URL . "/projects/showProject/" . $id . "";
                    $message = sprintf($this->language->__('email_notifications.project_created_message'), $actual_link, $id, $projectName, session("userdata.name"));
                    $mailer->setHtml($message);

                    $to = array();

                    foreach ($users as $user) {
                        if ($user["notifications"] != 0) {
                            $to[] = $user["username"];
                        }
                    }

                    //$mailer->sendMail($to, session("userdata.name"));
                    // NEW Queuing messaging system
                    $this->queueRepo->queueMessageToUsers($to, $message, $this->language->__('email_notifications.project_created_subject'), $id);


                    //Take the old value to avoid nl character
                    $values['details'] = $_POST['details'];

                    $this->tpl->sendConfetti();
                    $this->tpl->setNotification(sprintf($this->language->__('notifications.project_created_successfully'), BASE_URL . '/leancanvas/simpleCanvas/'), 'success', "project_created");

                    return Frontcontroller::redirect(BASE_URL . "/projects/showProject/" . $id);
                }


                $this->tpl->assign('project', $values);
            }

            $this->tpl->assign('menuTypes', $this->menuRepo->getMenuTypes());
            $this->tpl->assign('project', $values);
            $this->tpl->assign('availableUsers', $this->userRepo->getAll());
            $this->tpl->assign('clients', $this->clientsRepo->getAll());
            $this->tpl->assign('projectTypes', $this->projectService->getProjectTypes());

            $this->tpl->assign('info', $msgKey);

            return $this->tpl->display('projects.newProject');
        }
    }

}
