<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;
    use leantime\domain\services;

    class newUser
    {

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            $tpl = new core\template();
            $userRepo =  new repositories\users();
            $project = new repositories\projects();

            $values = array(
                'firstname' =>"",
                'lastname' => "",
                'user' => "",
                'phone' => "",
                'role' => "",
                'password' => "",
                'clientId' => ""
            );

            //only Admins
            if ($_SESSION['userdata']['role'] == 'admin') {

                    $projectrelation = array();

                    if (isset($_POST['save'])) {

                        $tempPasswordVar = $_POST['password'];
                        $values = array(
                            'firstname' => ($_POST['firstname']),
                            'lastname' => ($_POST['lastname']),
                            'user' => ($_POST['user']),
                            'phone' => ($_POST['phone']),
                            'role' => ($_POST['role']),
                            'password' => (password_hash($_POST['password'], PASSWORD_DEFAULT )),
                            'clientId' => ($_POST['client'])
                        );

                        //Validation
                        if ($values['user'] !== '') {

                            if (filter_var($values['user'], FILTER_VALIDATE_EMAIL)) {
                                if (password_verify($_POST['password'], $values['password']) && $_POST['password'] != '') {
                                    if ($userRepo->usernameExist($values['user']) === false) {

                                        $userId = $userRepo->addUser($values);

                                        //Update Project Relationships
                                        if (isset($_POST['projects'])) {
                                            if ($_POST['projects'][0] !== '0') {
                                                $project->editUserProjectRelations($userId, $_POST['projects']);
                                            } else {
                                                $project->deleteAllProjectRelations($userId);
                                            }
                                        }

                                        $mailer = new core\mailer();

                                        $mailer->setSubject("Your Leantime Account is Ready");
                                        $actual_link = BASE_URL;
                                        $mailer->setHtml($_SESSION["userdata"]["name"] . " created a new user account for you. You can access your account at: <a href='" . $actual_link . "'>" . $actual_link . "</a><br/><br/>Your username is: " . $values["user"] . "<br/>And your password is: " . $tempPasswordVar . "<br /><br />Please make sure to update your password once you login.<br />Have fun!<br />");

                                        $to = array($values["user"]);

                                        $mailer->sendMail($to, $_SESSION["userdata"]["name"]);

                                        $tpl->setNotification('USER_ADDED', 'success');

                                        $tpl->redirect(BASE_URL."/users/showAll");

                                    } else {

                                        $tpl->setNotification('USERNAME_EXISTS', 'error');

                                    }
                                } else {

                                    $tpl->setNotification('PASSWORDS_DONT_MATCH', 'error');
                                }
                            } else {

                                $tpl->setNotification('NO_VALID_EMAIL', 'error');
                            }
                        } else {

                            $tpl->setNotification('NO_USERNAME', 'error');
                        }

                    }

                    $tpl->assign('values', $values);
                    $clients = new repositories\clients();
                    $tpl->assign('clients', $clients->getAll());
                    $tpl->assign('allProjects', $project->getAll());
                    $tpl->assign('relations', $projectrelation);
                    $tpl->assign('roles', $userRepo->getRoles());

                    $tpl->display('users.newUser');

            } else {

                $tpl->display('general.error');

            }

        }

    }

}
