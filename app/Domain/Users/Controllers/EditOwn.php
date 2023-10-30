<?php

namespace Leantime\Domain\Users\Controllers {

    use Leantime\Domain\Users\Repositories\Users as UserRepository;
    use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
    use Leantime\Domain\Setting\Services\Setting as SettingService;
    use Leantime\Domain\Users\Services\Users as UserService;
    use Leantime\Core\Language as LanguageCore;
    use Leantime\Core\Theme as ThemeCore;
    use Leantime\Core\Frontcontroller as FrontcontrollerCore;
    use Leantime\Core\Controller;
    use Leantime\Domain\Auth\Services\Auth;

    /**
     *
     */
    class EditOwn extends Controller
    {
        protected LanguageCore $language;
        private ThemeCore $themeCore;
        private UserRepository $userRepo;
        private SettingRepository $settingsRepo;
        private SettingService $settingsService;
        private UserService $userService;
        private int $userId;

        /**
         * init - initialize private variables
         *
         * @access public
         */
        public function init(
            LanguageCore $language,
            ThemeCore $themeCore,
            UserRepository $userRepo,
            SettingRepository $settingsRepo,
            SettingService $settingsService,
            UserService $userService
        ) {
            $this->language = $language;
            $this->themeCore = $themeCore;
            $this->userRepo = $userRepo;
            $this->settingsRepo = $settingsRepo;
            $this->settingsService = $settingsService;
            $this->userService = $userService;

            $this->userId = $_SESSION['userdata']['id'];
        }


        /**
         * @return void
         * @throws \Exception
         */
        public function get(): void
        {

            $row = $this->userRepo->getUser($this->userId);

            $userLang = $this->settingsService->getSetting("usersettings." . $this->userId . ".language");

            if (!$userLang) {
                $userLang = $this->language->getCurrentLanguage();
            }

            $userTheme = $this->settingsService->getSetting("usersettings." . $this->userId . ".theme");

            //Build values array
            $values = array(
                'firstname' => $row['firstname'],
                'lastname' => $row['lastname'],
                'user' => $row['username'],
                'phone' => $row['phone'],
                'role' => $row['role'],
                'notifications' => $row['notifications'],
                'twoFAEnabled' => $row['twoFAEnabled'],
                'messagesfrequency' => $this->settingsService->getSetting("usersettings." . $row['id'] . ".messageFrequency"),
            );

            if (!$values['messagesfrequency']) {
                $values['messagesfrequency'] = $this->settingsService->getSetting("companysettings.messageFrequency");
            }

            $permitted_chars = '123456789abcdefghijklmnopqrstuvwxyz';
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

        /**
         * @return void
         * @throws \Exception
         */
        public function post(): void
        {

            //Save Profile Info
            $tab = '';

            if (isset($_SESSION['formTokenName']) && isset($_POST[$_SESSION['formTokenName']]) && $_POST[$_SESSION['formTokenName']] == $_SESSION['formTokenValue']) {
                $row = $this->userRepo->getUser($this->userId);

                //profile Info
                if (isset($_POST['profileInfo'])) {
                    $tab = '#myProfile';

                    $values = array(
                        'firstname' => ($_POST['firstname']) ?? $row['firstname'],
                        'lastname' => ($_POST['lastname']) ?? $row['lastname'],
                        'user' => ($_POST['user']) ?? $row['username'],
                        'phone' => ($_POST['phone']) ?? $row['phone'],
                        'notifications' => $row['notifications'],
                        'twoFAEnabled' => $row['twoFAEnabled'],
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
                                    $this->userService->editOwn($values, $this->userId);
                                    $this->tpl->setNotification($this->language->__("notifications.profile_edited"), 'success', "profile_edited");
                                } else {
                                    $this->tpl->setNotification($this->language->__("notification.user_exists"), 'error');
                                }
                            } else {
                                $this->userService->editOwn($values, $this->userId);
                                $this->tpl->setNotification($this->language->__("notifications.profile_edited"), 'success', "profile_edited");
                            }
                        } else {
                            $this->tpl->setNotification($this->language->__("notification.no_valid_email"), 'error');
                        }
                    } else {
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
                        'twoFAEnabled' => $row['twoFAEnabled'],
                    );

                    if (password_verify($_POST['currentPassword'], $values['password'])) {
                        if ($_POST['newPassword'] == $_POST['confirmPassword']) {
                            if ($this->userService->checkPasswordStrength($_POST['newPassword'])) {
                                $values['password'] = $_POST['newPassword'];
                                $this->userRepo->editOwn($values, $this->userId);
                                $this->tpl->setNotification(
                                    $this->language->__("notifications.password_changed"),
                                    'success',
                                    "password_edited"
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
                    } else {
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

                    $this->settingsService->saveSetting("usersettings." . $this->userId . ".theme", $postTheme);
                    $this->settingsService->saveSetting("usersettings." . $this->userId . ".language", $postLang);

                    unset($_SESSION["companysettings.logoPath"]);
                    unset($_SESSION['cache.language_resources_' . $this->language->getCurrentLanguage() . '_' . $postTheme]);

                    $this->themeCore->setActive($postTheme);
                    $this->language->setLanguage($postLang);

                    $this->tpl->setNotification($this->language->__("notifications.changed_profile_settings_successfully"), 'success', "profilesettings_updated");
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
                        'twoFAEnabled' => $row['twoFAEnabled'],
                    );

                    if (isset($_POST['notifications'])) {
                        $values["notifications"] = 1;
                    } else {
                        $values["notifications"] = 0;
                    }

                    $this->userRepo->editOwn($values, $this->userId);

                    // Storing option messagefrequency
                    $this->settingsService->saveSetting("usersettings." . $this->userId . ".messageFrequency", (int) $_POST['messagesfrequency']);

                    $this->tpl->setNotification($this->language->__("notifications.changed_profile_settings_successfully"), 'success', "profilesettings_updated");
                }
            } else {
                $this->tpl->setNotification($this->language->__("notification.form_token_incorrect"), 'error');
            }

            //Redirect
            FrontcontrollerCore::redirect(BASE_URL . "/users/editOwn" . $tab);
        }
    }
}
