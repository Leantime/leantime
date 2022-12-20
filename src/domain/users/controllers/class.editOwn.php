<?php

namespace leantime\domain\controllers {

    use leantime\domain\repositories;
    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\services\auth;

    class editOwn extends controller
    {

        private $userId;
        /**
         * init - initialize private variables
         *
         * @access public
         */
        public function init()
        {

            $this->language = core\language::getInstance();
            $this->settingsService = new \leantime\domain\services\setting();
            $this->userRepo = new repositories\users();
            $this->settingsRepo = new \leantime\domain\repositories\setting();
            $this->themeCore = new \leantime\core\theme();
            $this->userService = new \leantime\domain\services\users();

            $this->userId = $_SESSION['userdata']['id'];

        }


        public function get() {

            $row = $this->userRepo->getUser($this->userId);

            $userLang = $this->settingsService->settingsRepo->getSetting("usersettings.".$this->userId.".language");

            if($userLang == false){
                $userLang = $this->language->getCurrentLanguage();
            }

            $userTheme = $this->settingsService->settingsRepo->getSetting("usersettings.".$this->userId.".theme");

            //Build values array
            $values = array(
                'firstname' => $row['firstname'],
                'lastname' => $row['lastname'],
                'user' => $row['username'],
                'phone' => $row['phone'],
                'role' => $row['role'],
                'notifications' => $row['notifications'],
                'twoFAEnabled' => $row['twoFAEnabled'],
                'messagesfrequency' => $this->settingsRepo->getSetting("usersettings.".$row['id'].".messageFrequency"),
            );

            if($values['messagesfrequency'] == false)
            {
                $values['messagesfrequency'] = $this->settingsRepo->getSetting("companysettings.messageFrequency");
            }

            $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
            $_SESSION['formTokenName'] = substr(str_shuffle($permitted_chars), 0, 32);
            $_SESSION['formTokenValue'] = substr(str_shuffle($permitted_chars), 0, 32);

            $this->tpl->assign('profilePic', $this->userRepo->getProfilePicture($_SESSION['userdata']['id']));
            $this->tpl->assign('values', $values);

            $this->tpl->assign('userLang', $userLang);
            $this->tpl->assign('userTheme', $userTheme);
            $this->tpl->assign("languageList", $this->language->getLanguageList());

            $this->tpl->assign('user', $row);

            $this->tpl->display('users.editOwn');


        }

        public function post() {

            //Save Profile Info
            $tab = '';

            if(isset($_POST[$_SESSION['formTokenName']]) && $_POST[$_SESSION['formTokenName']] == $_SESSION['formTokenValue']) {

                $row = $this->userRepo->getUser($this->userId);

                //profile Info
                if (isset($_POST['profileInfo'])) {

                    $tab = '#myProfile';

                    $values = array(
                        'firstname' => ($_POST['firstname']) ?? $row['firstname'],
                        'lastname' => ($_POST['lastname']) ?? $row['lastname'],
                        'user' => ($_POST['user']) ?? $row['username'],
                        'phone' => ($_POST['phone']) ?? $row['phone'],
                        'password' => $row['password'],
                        'notifications' => $row['notifications'],
                        'twoFAEnabled' => $row['twoFAEnabled']
                    );

                    $changedEmail = 0;
                    if ($row['username'] != $values['user']) {
                        $changedEmail = 1;
                    }

                    //Validation
                    if ($values['user'] !== '') {
                        if (filter_var($values['user'], FILTER_VALIDATE_EMAIL)) {
                            if ($changedEmail == 1) {
                                if ($this->userRepo->usernameExist($values['user'], $this->userId) === false) {

                                    $this->userRepo->editOwn($values, $this->userId);
                                    $this->tpl->setNotification($this->language->__("notifications.profile_edited"), 'success');

                                }else{
                                    $this->tpl->setNotification($this->language->__("notification.user_exists"), 'error');
                                }
                            }else{
                                $this->userRepo->editOwn($values, $this->userId);
                                $this->tpl->setNotification($this->language->__("notifications.profile_edited"), 'success');
                            }
                        }else{
                            $this->tpl->setNotification($this->language->__("notification.no_valid_email"), 'error');
                        }
                    }else{
                        $this->tpl->setNotification($this->language->__("notification.enter_email"), 'error');
                    }

                }

                //Save Password
                if (isset($_POST['savepw'])) {

                    $tab = '#security';

                    $values = array(
                        'firstname' => $row['firstname'],
                        'lastname' => $row['lastname'],
                        'user' => $row['username'],
                        'phone' => $row['phone'],
                        'password' => $row['password'],
                        'notifications' => $row['notifications'],
                        'twoFAEnabled' => $row['twoFAEnabled']
                    );

                    if(password_verify($_POST['currentPassword'], $values['password'])) {

                        if ($_POST['newPassword'] == $_POST['confirmPassword']) {
                            if ($this->userService->checkPasswordStrength($_POST['newPassword'])) {
                                $values['password'] = password_hash($_POST['newPassword'], PASSWORD_DEFAULT);
                                $this->userRepo->editOwn($values, $this->userId);
                                $this->tpl->setNotification(
                                    $this->language->__("notifications.password_changed"),
                                    'success'
                                );
                            } else {
                                $this->tpl->setNotification(
                                    $this->language->__("notification.password_not_strong_enough"),
                                    'error'
                                );
                            }
                        } else {
                            $this->tpl->setNotification(
                                $this->language->__("notification.passwords_dont_match"),
                                'error'
                            );
                        }

                    }else{
                        $this->tpl->setNotification(
                            $this->language->__("notification.previous_password_incorrect"),
                            'error'
                        );
                    }
                }


                //Save Look & Feel
                if (isset($_POST['saveLook'])) {

                    $tab = '#look';

                    $postLang = htmlentities($_POST['language']);
                    $postTheme = htmlentities($_POST['theme']);

                    $this->settingsService->settingsRepo->saveSetting("usersettings.".$this->userId.".theme", $postTheme);
                    $this->settingsService->settingsRepo->saveSetting("usersettings.".$this->userId.".language", $postLang);

                    $this->themeCore->setActive($postTheme);
                    $this->language->setLanguage($postLang);

                    $this->tpl->setNotification($this->language->__("notifications.changed_profile_settings_successfully"), 'success');

                }


                //Save Profile Image
                if (isset($_POST['profileImage'])) {
                    $tab = '#myProfile';
                }


                //Save Notifications
                if (isset($_POST['savenotifications'])) {

                    $tab = '#notifications';

                    $values = array(
                        'firstname' => $row['firstname'],
                        'lastname' => $row['lastname'],
                        'user' => $row['username'],
                        'phone' => $row['phone'],
                        'password' => $row['password'],
                        'notifications' => $row['notifications'],
                        'twoFAEnabled' => $row['twoFAEnabled']
                    );

                    if (isset($_POST['notifications']) == true) {
                        $values["notifications"] = 1;
                    } else {
                        $values["notifications"] = 0;
                    }

                    $this->userRepo->editOwn($values, $this->userId);

                    // Storing option messagefrequency
                    $this->settingsRepo->saveSetting("usersettings.".$this->userId.".messageFrequency", (int) $_POST['messagesfrequency']);

                    $this->tpl->setNotification($this->language->__("notifications.changed_profile_settings_successfully"), 'success');


                }

            }else{

                $this->tpl->setNotification($this->language->__("notification.form_token_incorrect"), 'error');

            }

            //Redirect
            core\frontcontroller::redirect(BASE_URL."/users/editOwn".$tab);
        }

        /**
         * run - display template and edit data
         *
         * @access public

        public function run()
        {





            $infoKey = '';



            //Save form
            if (isset($_POST['save'])) {

                if(isset($_POST[$_SESSION['formTokenName']]) && $_POST[$_SESSION['formTokenName']] == $_SESSION['formTokenValue']) {

                    $values = array(
                        'firstname' => ($_POST['firstname']) ?? $row['firstname'],
                        'lastname' => ($_POST['lastname']) ?? $row['lastname'],
                        'user' => ($_POST['user']) ?? $row['username'],
                        'phone' => ($_POST['phone']) ?? $row['phone'],
                        'password' => (password_hash($_POST['newPassword'], PASSWORD_DEFAULT)),
                        'notifications' => $row['notifications'],
                        'twoFAEnabled' => $row['twoFAEnabled'],
                        'messagesfrequency' => $_POST['messagesfrequency'],
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

                        if (filter_var($values['user'], FILTER_VALIDATE_EMAIL)) {

                            if ($_POST['newPassword'] == $_POST['confirmPassword']) {

                                if ($_POST['newPassword'] == '') {

                                    $values['password'] = '';

                                } else {

                                    $this->userRepo->editOwn($values, $userId);

                                }

                                if ($changedEmail == 1) {

                                    if ($this->userRepo->usernameExist($values['user'], $userId) === false) {

                                        $this->userRepo->editOwn($values, $userId);

                                       // Storing option messagefrequency
                                       $this->settingsRepo->saveSetting("usersettings.".$userId.".messageFrequency", $values['messagesfrequency']);

                                        $postLang = htmlentities($_POST['language']);
                                        $postTheme = htmlentities($_POST['theme']);

                                        $this->settingsService->settingsRepo->saveSetting("usersettings.".$userId.".theme", $postTheme);
                                        $this->settingsService->settingsRepo->saveSetting("usersettings.".$userId.".language", $postLang);

                                        $this->themeCore->setActive($postTheme);
                                        $this->language->setLanguage($postLang);

                                        $this->tpl->setNotification($this->language->__("notifications.profile_edited"), 'success');

                                    } else {



                                    }

                                } else {

                                    $postLang = htmlentities($_POST['language']);
                                    $postTheme = htmlentities($_POST['theme']);

                                    $this->settingsService->settingsRepo->saveSetting("usersettings.".$userId.".theme", $postTheme);
                                    $this->settingsService->settingsRepo->saveSetting("usersettings.".$userId.".language", $postLang);

                                    $this->themeCore->setActive($postTheme);
                                    $this->language->setLanguage($postLang);

                                    $this->userRepo->editOwn($values, $userId);

                                    // Storing option messagefrequency
                                    $this->settingsRepo->saveSetting("usersettings.".$userId.".messageFrequency", $values['messagesfrequency']);



                                }


                            } else {



                            }

                        } else {



                        }

                    } else {



                    }

                }else{

                    $this->tpl->setNotification($this->language->__("notification.form_token_incorrect"), 'error');

                }
            }

            //Assign vars


            //Sensitive Form, generate form tokens


            $this->tpl->assign('profilePic', $users->getProfilePicture($_SESSION['userdata']['id']));
            $this->tpl->assign('info', $infoKey);
            $this->tpl->assign('values', $values);

            $this->tpl->assign('userLang', $userLang);
            $this->tpl->assign('userTheme', $userTheme);
            $this->tpl->assign("languageList", $this->language->getLanguageList());

            $this->tpl->assign('user', $row);

            $this->tpl->display('users.editOwn');

        }
         * */

    }
}

