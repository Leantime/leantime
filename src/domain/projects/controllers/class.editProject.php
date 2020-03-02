<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;

    class editProject
    {

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            $tpl = new core\template();
            $projectRepo = new repositories\projects();

            //Only admins
            if ($_SESSION['userdata']['role'] == 'admin') {

                if (isset($_GET['id']) === true) {

                    $id = (int)($_GET['id']);

                    $row = $projectRepo->getProject($id);

                    $msgKey = '';

                    $values = array(
                        'name' => $row['name'],
                        'details' => $row['details'],
                        'clientId' => $row['clientId'],
                        'state' => $row['state'],
                        'hourBudget' => $row['hourBudget'],
                        'assignedUsers' => $projectRepo->getProjectUserRelation($id),
                        'dollarBudget' => $row['dollarBudget']
                    );

                    //Edit project
                    if (isset($_POST['save']) === true) {

                        if (isset($_POST['editorId']) && count($_POST['editorId'])) {
                            $assignedUsers = $_POST['editorId'];
                        } else {
                            $assignedUsers = array();
                        }

                        $mailer = new core\mailer();

                        $values = array(
                            'name' => $_POST['name'],
                            'details' => $_POST['details'],
                            'clientId' => $_POST['clientId'],
                            'state' => $_POST['projectState'],
                            'hourBudget' => $_POST['hourBudget'],
                            'assignedUsers' => $assignedUsers,
                            'dollarBudget' => $_POST['dollarBudget']
                        );

                        if ($values['name'] !== '') {

                            if ($projectRepo->hasTickets($id) && $values['state'] == 1) {

                                $tpl->setNotification('PROJECT_HAS_TICKETS', 'error');

                            } else {

                                $projectRepo->editProject($values, $id);

                                if($_SESSION['currentProject'] == $id) {
                                    $_SESSION['currentProjectName'] = $values['name'];
                                }

                                //Take the old value to avoid nl character
                                $values['details'] = $_POST['details'];


                                $tpl->setNotification('PROJECT_EDITED', 'success');

                                $users = $projectRepo->getUsersAssignedToProject($id);

                                $mailer->setSubject("One of your projects was updated");
                                $actual_link = CURRENT_URL;
                                $mailer->setHtml("Project <a href='" . $actual_link . "'>[" . $id . "] - " . $values['name'] . "</a> was updated by " . $_SESSION["userdata"]["name"] . "<br />");

                                $to = array();

                                foreach ($users as $user) {

                                    if ($user["notifications"] != 0) {
                                        $to[] = $user["username"];
                                    }
                                }

                                $mailer->sendMail($to, $_SESSION["userdata"]["name"]);

                            }

                        } else {

                            $tpl->setNotification('NO_PROJECTTNAME', 'error');

                        }

                    }

                    //Add Account
                    if (isset($_POST['accountSubmit']) === true) {

                        $accountValues = array(
                            'name' => $_POST['accountName'],
                            'kind' => $_POST['kind'],
                            'username' => $_POST['username'],
                            'password' => $_POST['password'],
                            'host' => $_POST['host'],
                            'projectId' => $id
                        );

                        if ($accountValues['name'] !== '') {

                            $projectRepo->addProjectAccount($accountValues);

                            $tpl->setNotification('ACCOUNT_ADDED', 'sucess');

                        } else {

                            $tpl->setNotification('NO_ACCOUNT_NAME', 'error');

                        }

                        $tpl->assign('accountValues', $accountValues);

                    }

                    //Upload file
                    if (isset($_POST['upload']) === true) {

                        if ($_FILES['file']['name'] !== '') {

                            $upload = new core\fileupload();

                            $upload->initFile($_FILES['file']);

                            if ($upload->error == '') {

                                //Name on Server is encoded
                                $newname = md5($id . time());

                                $upload->renameFile($newname);

                                if ($upload->upload() === true) {

                                    $fileValues = array(
                                        'encName' => ($upload->file_name),
                                        'realName' => ($upload->real_name),
                                        'date' => date("Y-m-d H:i:s"),
                                        'ticketId' => ($id),
                                        'userId' => ($_SESSION['userdata']['id'])
                                    );

                                    $projectRepo->addFile($fileValues);

                                    $tpl->setNotification('FILE_UPLOADED', 'success');

                                } else {

                                    $tpl->setNotification('ERROR_FILEUPLOAD', 'error');
                                }

                            } else {

                                $tpl->setNotification('ERROR_FILEUPLOAD', 'error');

                            }
                        } else {

                            $tpl->setNotification('NO_FILE', 'error');
                        }

                    }

                    $helper = new core\helper();
                    $clients = new repositories\clients();

                    $user = new repositories\users();
                    $tpl->assign('availableUsers', $user->getAll());

                    $files = new repositories\files();

                    //Assign vars
                    $tpl->assign('info', $msgKey);
                    $tpl->assign('clients', $clients->getAll());
                    $tpl->assign('values', $values);
                    $tpl->assign('files', $files->getFilesByModule("project", $id));
                    $tpl->assign('helper', $helper);
                    $tpl->assign('accounts', $projectRepo->getProjectAccounts($id));

                    $tpl->display('projects.editProject');

                } else {

                    $tpl->display('general.error');

                }

            } else {

                $tpl->display('general.error');

            }

        }

    }

}
