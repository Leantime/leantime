<?php

namespace Leantime\Domain\Projects\Controllers {

    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Controller\Frontcontroller;
    use Leantime\Core\Controller\Frontcontroller as FrontcontrollerCore;
    use Leantime\Core\Support\FromFormat;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Auth\Services\Auth;
    use Leantime\Domain\Clients\Repositories\Clients as ClientRepository;
    use Leantime\Domain\Comments\Repositories\Comments as CommentRepository;
    use Leantime\Domain\Comments\Services\Comments as CommentService;
    use Leantime\Domain\Files\Repositories\Files as FileRepository;
    use Leantime\Domain\Files\Services\Files as FileService;
    use Leantime\Domain\Menu\Repositories\Menu as MenuRepository;
    use Leantime\Domain\Notifications\Models\Notification;
    use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;
    use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
    use Leantime\Domain\Tickets\Services\Tickets as TicketService;
    use Leantime\Domain\Users\Repositories\Users as UserRepository;

    /**
     *
     */
    class ShowProject extends Controller
    {
        //services
        private ProjectService $projectService;
        private CommentService $commentService;
        private FileService $fileService;
        private TicketService $ticketService;

        // repositories
        private SettingRepository $settingsRepo;
        private ProjectRepository $projectRepo;
        private UserRepository $userRepo;
        private ClientRepository $clientsRepo;
        private FileRepository $fileRepo;
        private CommentRepository $commentsRepo;
        private MenuRepository $menuRepo;

        /**
         * init - initialize private variables
         *
         * @access public
         */
        public function init(
            ProjectService $projectService,
            CommentService $commentService,
            FileService $fileService,
            TicketService $ticketService,
            SettingRepository $settingsRepo,
            ProjectRepository $projectRepo,
            UserRepository $userRepo,
            ClientRepository $clientsRepo,
            FileRepository $fileRepo,
            CommentRepository $commentsRepo,
            MenuRepository $menuRepo
        ) {

            Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager]);

            //services
            $this->projectService = $projectService;
            $this->commentService = $commentService;
            $this->fileService = $fileService;
            $this->ticketService = $ticketService;

            // repositories
            $this->settingsRepo = $settingsRepo;
            $this->projectRepo = $projectRepo;
            $this->userRepo = $userRepo;
            $this->clientsRepo = $clientsRepo;
            $this->fileRepo = $fileRepo;
            $this->commentsRepo = $commentsRepo;
            $this->menuRepo = $menuRepo;

            if (!session()->exists("lastPage")) {
                session(["lastPage" => CURRENT_URL]);
            }
        }


        /**
         * One Method to rule them all...
         */
        public function run()
        {

            if (isset($_GET['id']) === true) {
                $projectTypes = $this->projectService->getProjectTypes();


                $id = (int)($_GET['id']);

                $project = $this->projectRepo->getProject($id);

                if (isset($project['id']) === false) {
                    return FrontcontrollerCore::redirect(BASE_URL . "/error/error404");
                }

                if(session("currentProject") != $project['id'] ){
                    $this->projectService->changeCurrentSessionProject($project['id']);
                }

                //Mattermost integration
                if (isset($_POST['mattermostSave'])) {
                    $webhook = strip_tags($_POST['mattermostWebhookURL']);
                    $this->settingsRepo->saveSetting("projectsettings." . $id . ".mattermostWebhookURL", $webhook);
                    $this->tpl->setNotification($this->language->__("notification.saved_mattermost_webhook"), 'success');
                }

                //Slack integration
                if (isset($_POST['slackSave'])) {
                    $webhook = strip_tags($_POST['slackWebhookURL']);
                    $this->settingsRepo->saveSetting("projectsettings." . $id . ".slackWebhookURL", $webhook);
                    $this->tpl->setNotification($this->language->__("notification.saved_slack_webhook"), 'success');
                }

                //Zulip
                $zulipWebhook = $this->settingsRepo->getSetting("projectsettings." . $id . ".zulipHook");

                if ($zulipWebhook == "") {
                    $zulipHook = array(
                        'zulipURL' => '',
                        'zulipEmail' => '',
                        'zulipBotKey' => '',
                        'zulipStream' => '',
                        'zulipTopic' => '',
                    );
                    $this->tpl->assign('zulipHook', $zulipHook);
                } else {
                    $this->tpl->assign('zulipHook', unserialize($zulipWebhook));
                }


                if (isset($_POST['zulipSave'])) {
                    $zulipHook = array(
                        'zulipURL' => strip_tags($_POST['zulipURL']),
                        'zulipEmail' => strip_tags($_POST['zulipEmail']),
                        'zulipBotKey' => strip_tags($_POST['zulipBotKey']),
                        'zulipStream' => strip_tags($_POST['zulipStream']),
                        'zulipTopic' => strip_tags($_POST['zulipTopic']),
                    );

                    if (
                        $zulipHook['zulipURL'] == "" ||
                        $zulipHook['zulipEmail'] == "" ||
                        $zulipHook['zulipBotKey'] == "" ||
                        $zulipHook['zulipStream'] == "" ||
                        $zulipHook['zulipTopic'] == ""
                    ) {
                        $this->tpl->setNotification($this->language->__("notification.error_zulip_webhook_fill_out_fields"), 'error');
                    } else {
                        $this->settingsRepo->saveSetting("projectsettings." . $id . ".zulipHook", serialize($zulipHook));
                        $this->tpl->setNotification($this->language->__("notification.saved_zulip_webhook"), 'success');
                    }

                    $this->tpl->assign('zulipHook', $zulipHook);
                }

                //Discord integration; provide three possible webhooks per project
                if (isset($_POST['discordSave'])) {
                    for ($i = 1; 3 >= $i; $i++) {
                        $webhook = trim(strip_tags($_POST['discordWebhookURL' . $i]));
                        $this->settingsRepo->saveSetting('projectsettings.' . $id . '.discordWebhookURL' . $i, $webhook);
                    }
                    $this->tpl->setNotification($this->language->__('notification.saved_discord_webhook'), 'success');
                }

                $mattermostWebhook = $this->settingsRepo->getSetting("projectsettings." . $id . ".mattermostWebhookURL");
                $this->tpl->assign('mattermostWebhookURL', $mattermostWebhook);

                $slackWebhook = $this->settingsRepo->getSetting("projectsettings." . $id . ".slackWebhookURL");
                $this->tpl->assign('slackWebhookURL', $slackWebhook);

                for ($i = 1; 3 >= $i; $i++) {
                    $discordWebhook = $this->settingsRepo->getSetting('projectsettings.' . $id . '.discordWebhookURL' . $i);
                    $this->tpl->assign('discordWebhookURL' . $i, $discordWebhook);
                }

                session(["lastPage" => BASE_URL . "/projects/showProject/" . $id]);


                $project['assignedUsers'] = $this->projectRepo->getProjectUserRelation($id);

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


                if (isset($_POST['saveUsers']) === true) {
                    if (isset($_POST['editorId']) && count($_POST['editorId'])) {
                        $assignedUsers = $_POST['editorId'];
                    } else {
                        $assignedUsers = array();
                    }

                    $values = array(
                        "assignedUsers" => $assignedUsers,
                        "projectRoles" => $_POST,
                    );

                    $this->projectRepo->editProjectRelations($values, $id);

                    $project['assignedUsers'] = $this->projectRepo->getProjectUserRelation($id);

                    $this->tpl->setNotification($this->language->__("notifications.user_was_added_to_project"), "success");
                }

                //save changed project data
                if (isset($_POST['save']) === true) {
                    //bind Post Data into one array
                    $values = array(
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
                        'start' => format(value: $_POST['start'], fromFormat: FromFormat::UserDateStartOfDay)->isoDateTime(),
                        'end' => $_POST['end'] ? format(value: $_POST['end'], fromFormat: FromFormat::UserDateStartOfDay)->isoDateTime() : ''
                    );

                    if ($values['name'] !== '') {
                        if ($this->projectRepo->hasTickets($id) && $values['state'] == 1) {
                            $this->tpl->setNotification($this->language->__("notification.project_has_tickets"), 'error');
                        } else {
                            $this->projectRepo->editProject($values, $id);

                            $project['assignedUsers'] = $this->projectRepo->getProjectUserRelation($id);

                            $this->tpl->setNotification($this->language->__("notification.project_saved"), 'success');

                            $subject = sprintf($this->language->__("email_notifications.project_update_subject"), $id, $values['name']);
                            $message = sprintf(
                                $this->language->__("email_notifications.project_update_message"),
                                session("userdata.name"),
                                $values['name']
                            );

                            $linkLabel = $this->language->__("email_notifications.project_update_cta");

                            $actual_link = CURRENT_URL;

                            $notification = app()->make(Notification::class);
                            $notification->url = array(
                                "url" => $actual_link,
                                "text" => $linkLabel,
                            );
                            $notification->entity = $project;
                            $notification->module = "projects";
                            $notification->projectId = session("currentProject");
                            $notification->subject = $subject;
                            $notification->authorId = session("userdata.id");
                            $notification->message = $message;

                            $this->projectService->notifyProjectUsers($notification);

                            //Get updated project
                            return Frontcontroller::redirect(BASE_URL."/projects/showProject/".$id);
                        }
                    } else {
                        $this->tpl->setNotification($this->language->__("notification.no_project_name"), 'error');
                    }
                }



                $employees = $this->userRepo->getEmployees();

                //Assign vars
                $this->tpl->assign('availableUsers', $this->userRepo->getAll());
                $this->tpl->assign('clients', $this->clientsRepo->getAll());

                $this->tpl->assign("todoStatus", $this->ticketService->getStatusLabels());

                $this->tpl->assign('employees', $employees);

                $this->tpl->assign('project', $project);

                $this->tpl->assign('menuTypes', $this->menuRepo->getMenuTypes());
                $this->tpl->assign('projectTypes', $projectTypes);

                $this->tpl->assign('state', $this->projectRepo->state);
                $this->tpl->assign('role', session("userdata.role"));

                return $this->tpl->display('projects.showProject');
            } else {
                return $this->tpl->display('errors.error403', responseCode: 403);
            }
        }
    }
}
