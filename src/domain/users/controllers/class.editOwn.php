<?php

namespace leantime\domain\controllers {

    use leantime\domain\repositories;
    use leantime\core;

    class editOwn
    {

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            $tpl = new core\template();

            $userId = $_SESSION['userdata']['id'];
            $userRepo = new repositories\users();

            $row = $userRepo->getUser($userId);

            $infoKey = '';

            //Build values array
            $values = array(
                'firstname' => $row['firstname'],
                'lastname' => $row['lastname'],
                'user' => $row['username'],
                'phone' => $row['phone'],
                'role' => $row['role'],
                'notifications' => $row['notifications'],
            );

            //Save form
            if (isset($_POST['save'])) {

                $values = array(
                    'firstname' => ($_POST['firstname']),
                    'lastname' => ($_POST['lastname']),
                    'user' => ($_POST['user']),
                    'phone' => ($_POST['phone']),
                    'password' => (password_hash($_POST['newPassword'], PASSWORD_DEFAULT)),
                    'notifications' => $row['notifications']
                );

                if (isset($_POST['notifications']) == true) {
                    $values["notifications"] = 1;
                } else {
                    $values["notifications"] = 0;
                }

                $changedEmail = 0;

                if ($row['username'] != $values['user']) {

                    $changedEmail = 1;

                }

                //Validation
                if ($values['user'] !== '') {

                    $helper = new core\helper();

                    if (filter_var($values['user'], FILTER_VALIDATE_EMAIL)) {

                        if ($_POST['newPassword'] == $_POST['confirmPassword']) {

                            if ($_POST['newPassword'] == '') {

                                $values['password'] = '';

                            } else {

                                $userRepo->editOwn($values, $userId);

                            }

                            if ($changedEmail == 1) {

                                if ($userRepo->usernameExist($values['user'], $userId) === false) {

                                    $userRepo->editOwn($values, $userId);

                                    $tpl->setNotification('EDIT_SUCCESS', 'success');

                                } else {

                                    $tpl->setNotification('USERNAME_EXISTS', 'error');

                                }

                            } else {

                                $userRepo->editOwn($values, $userId);

                                $tpl->setNotification('EDIT_SUCCESS', 'success');

                            }

                        } else {

                            $tpl->setNotification('PASSWORDS_DONT_MATCH', 'ERROR');

                        }

                    } else {

                        $tpl->setNotification('NO_VALID_EMAIL', 'error');

                    }

                } else {

                    $tpl->setNotification('NO_USERNAME', 'error');

                }

            }

            //Assign vars
            $users = new repositories\users();

            $tpl->assign('profilePic', $users->getProfilePicture($_SESSION['userdata']['id']));
            $tpl->assign('info', $infoKey);
            $tpl->assign('values', $values);

            $tpl->assign('user', $row);

            $tpl->display('users.editOwn');

        }

    }
}

