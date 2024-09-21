<?php

namespace Leantime\Domain\Users\Controllers {

    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Controller\Frontcontroller as FrontcontrollerCore;
    use Leantime\Core\Language as LanguageCore;
    use Leantime\Core\Theme as ThemeCore;
    use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
    use Leantime\Domain\Setting\Services\Setting as SettingService;
    use Leantime\Domain\Users\Repositories\Users as UserRepository;
    use Leantime\Domain\Users\Services\Users as UserService;
    use Symfony\Component\HttpFoundation\Response;

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

            $this->userId = session("userdata.id");
        }


        /**
         * @return Response
         * @throws \Exception
         */
        public function get(): Response
        {

            $row = $this->userRepo->getUser($this->userId);

            $userLang = $this->settingsService->getSetting("usersettings." . $this->userId . ".language");

            if (!$userLang) {
                $userLang = $this->language->getCurrentLanguage();
            }

            $userTheme = $this->settingsService->getSetting("usersettings." . $this->userId . ".theme");
            $userColorMode = $this->settingsService->getSetting("usersettings." . $this->userId . ".colorMode");
            if (!$userColorMode) {
                $userColorMode = "light";
            }

            $userColorScheme = $this->settingsService->getSetting("usersettings." . $this->userId . ".colorScheme");
            if (!$userColorScheme) {
                $userColorScheme = "companyColors";
            }

            $themeFont = $this->settingsService->getSetting("usersettings." . $this->userId . ".themeFont");
            if (!$themeFont) {
                $themeFont = "Roboto";
            }

            $userDateFormat = $this->settingsService->getSetting("usersettings." . $this->userId . ".date_format");
            $userTimeFormat = $this->settingsService->getSetting("usersettings." . $this->userId . ".time_format");

            $timezone = $this->settingsService->getSetting("usersettings." . $this->userId . ".timezone");

            if (!$timezone) {
                $timezone = date_default_timezone_get();
            }

            $timezonesAvailable = timezone_identifiers_list();

            $availableColorSchemes = $this->themeCore->getAvailableColorSchemes();

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
            session(["formTokenName" => substr(str_shuffle($permitted_chars), 0, 32)]);
            session(["formTokenValue" => substr(str_shuffle($permitted_chars), 0, 32)]);

            $this->tpl->assign('profilePic', $this->userRepo->getProfilePicture(session("userdata.id")));
            $this->tpl->assign('values', $values);

            $this->tpl->assign('userLang', $userLang);
            $this->tpl->assign('userTheme', $userTheme);
            $this->tpl->assign('themeFont', $themeFont);
            $this->tpl->assign('userColorMode', $userColorMode);
            $this->tpl->assign('userColorScheme', $userColorScheme);
            $this->tpl->assign("languageList", $this->language->getLanguageList());
            $this->tpl->assign('dateFormat', $userDateFormat);
            $this->tpl->assign('timeFormat', $userTimeFormat);
            $this->tpl->assign('dateTimeValues', $this->getSupportedDateTimeFormats());
            $this->tpl->assign('timezone', $timezone);
            $this->tpl->assign('availableColorSchemes', $availableColorSchemes);
            $this->tpl->assign('availableFonts', $this->themeCore->getAvailableFonts());
            $this->tpl->assign('availableThemes', $this->themeCore->getAll());
            $this->tpl->assign('timezoneOptions', $timezonesAvailable);

            $this->tpl->assign('user', $row);

            return $this->tpl->display('users.editOwn');
        }

        /**
         * @return Response
         * @throws \Exception
         */
        public function post(): Response
        {

            //Save Profile Info
            $tab = '';

            if (session()->exists("formTokenName") && isset($_POST[session("formTokenName")]) && $_POST[session("formTokenName")] == session("formTokenValue")) {
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

                if (isset($_POST['saveTheme'])) {
                    $tab = '#theme';

                    $postTheme = htmlentities($_POST['theme']);
                    $postColorMode = htmlentities($_POST['colormode']);
                    $postColorScheme = htmlentities($_POST['colorscheme'] ?? "themeDefault");
                    $themeFont = htmlentities($_POST['themeFont']);

                    $this->settingsService->saveSetting("usersettings." . $this->userId . ".theme", $postTheme);
                    $this->settingsService->saveSetting("usersettings." . $this->userId . ".colorMode", $postColorMode);
                    $this->settingsService->saveSetting("usersettings." . $this->userId . ".colorScheme", $postColorScheme);
                    $this->settingsService->saveSetting("usersettings." . $this->userId . ".themeFont", $themeFont);
                    $this->themeCore->clearCache();
                    $this->themeCore->setActive($postTheme);
                    $this->themeCore->clearCache();
                    $this->themeCore->setColorMode($postColorMode);
                    $this->themeCore->clearCache();
                    $this->themeCore->setColorScheme($postColorScheme);
                    $this->themeCore->setFont($themeFont);


                    $this->tpl->setNotification($this->language->__("notifications.changed_profile_settings_successfully"), 'success', "themsettings_updated");
                }

                //Save Look & Feel
                if (isset($_POST['saveSettings'])) {
                    $tab = '#settings';

                    $postLang = htmlentities($_POST['language']);

                    $dateFormat = htmlentities($_POST['date_format']);
                    $timeFormat = htmlentities($_POST['time_format']);
                    $tz = htmlentities($_POST['timezone']);

                    $this->settingsService->saveSetting("usersettings." . $this->userId . ".language", $postLang);
                    $this->settingsService->saveSetting("usersettings." . $this->userId . ".date_format", $dateFormat);
                    $this->settingsService->saveSetting("usersettings." . $this->userId . ".time_format", $timeFormat);
                    $this->settingsService->saveSetting("usersettings." . $this->userId . ".timezone", $tz);

                    session()->forget("cache.language_resources_" . $this->language->getCurrentLanguage());

                    session(["usersettings.date_format" => $dateFormat]);
                    session(["usersettings.time_format" => $timeFormat]);
                    session(["usersettings.timezone" => $tz]);

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
            return FrontcontrollerCore::redirect(BASE_URL . "/users/editOwn" . $tab);
        }

        /**
         * Returns list of supported varying date-time formats.
         * @link https://www.php.net/manual/en/class.datetimeinterface.php#datetimeinterface.constants.types
         *
         * @return array<string> Format of ID => date-time string
         */
        private function getSupportedDateTimeFormats(): array
        {
            return [
                'dates' => [
                    $this->language->__("language.dateformat"),
                    'Y-m-d',
                    'D, d M y',
                    'l, d-M-y',
                    'd.m.Y',
                    'd/m/Y',
                    'd. F Y',
                    'm-d-Y',
                    'dmY',
                    'F d, Y',
                    'd F Y',
                ],
                'times' => [
                    $this->language->__("language.timeformat"),
                    'H:i P',
                    'H:i O',
                    'H:i T',
                    'H:i:s',
                    'H:i',
                ],
            ];
        }
    }
}
