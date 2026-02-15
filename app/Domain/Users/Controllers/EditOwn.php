<?php

namespace Leantime\Domain\Users\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller as FrontcontrollerCore;
use Leantime\Core\UI\Theme as ThemeCore;
use Leantime\Domain\Notifications\Models\Notification;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Setting\Services\Setting as SettingService;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Leantime\Domain\Users\Services\Users as UserService;
use Symfony\Component\HttpFoundation\Response;

class EditOwn extends Controller
{
    private ThemeCore $themeCore;

    private UserRepository $userRepo;

    private SettingService $settingsService;

    private UserService $userService;

    private ProjectService $projectService;

    private int $userId;

    /**
     * init - initialize private variables
     */
    public function init(
        ThemeCore $themeCore,
        UserRepository $userRepo,
        SettingService $settingsService,
        UserService $userService,
        ProjectService $projectService
    ): void {
        $this->themeCore = $themeCore;
        $this->userRepo = $userRepo;
        $this->settingsService = $settingsService;
        $this->userService = $userService;
        $this->projectService = $projectService;

        $this->userId = session('userdata.id');
    }

    /**
     * @throws \Exception
     */
    public function get(): Response
    {

        $row = $this->userRepo->getUser($this->userId);

        $userLang = $this->settingsService->getSetting('usersettings.'.$this->userId.'.language');

        if (! $userLang) {
            $userLang = $this->language->getCurrentLanguage();
        }

        $userTheme = $this->settingsService->getSetting('usersettings.'.$this->userId.'.theme');
        if (! $userTheme) {
            $userTheme = 'default';
        }

        $userColorMode = $this->settingsService->getSetting('usersettings.'.$this->userId.'.colorMode');
        if (! $userColorMode) {
            $userColorMode = 'light';
        }

        $availableColorSchemes = $this->themeCore->getAvailableColorSchemes();
        $userColorScheme = $this->settingsService->getSetting('usersettings.'.$this->userId.'.colorScheme');
        if (! $userColorScheme) {
            $userColorScheme = isset($availableColorSchemes['companyColors']) ? 'companyColors' : 'themeDefault';
        }

        $themeFont = $this->settingsService->getSetting('usersettings.'.$this->userId.'.themeFont');
        if (! $themeFont) {
            $themeFont = 'Roboto';
        }

        $userDateFormat = $this->settingsService->getSetting('usersettings.'.$this->userId.'.date_format');
        $userTimeFormat = $this->settingsService->getSetting('usersettings.'.$this->userId.'.time_format');

        $timezone = $this->settingsService->getSetting('usersettings.'.$this->userId.'.timezone');

        if (! $timezone) {
            $timezone = date_default_timezone_get();
        }

        $timezonesAvailable = timezone_identifiers_list();

        // Build values array
        $values = [
            'firstname' => $row['firstname'],
            'lastname' => $row['lastname'],
            'user' => $row['username'],
            'phone' => $row['phone'],
            'role' => $row['role'],
            'jobTitle' => $row['jobTitle'],
            'jobLevel' => $row['jobLevel'],
            'department' => $row['department'],
            'notifications' => $row['notifications'],
            'twoFAEnabled' => $row['twoFAEnabled'],
            'messagesfrequency' => $this->settingsService->getSetting('usersettings.'.$row['id'].'.messageFrequency'),
        ];

        if (! $values['messagesfrequency']) {
            $values['messagesfrequency'] = $this->settingsService->getSetting('companysettings.messageFrequency');
        }

        $permitted_chars = '123456789abcdefghijklmnopqrstuvwxyz';
        session(['formTokenName' => substr(str_shuffle($permitted_chars), 0, 32)]);
        session(['formTokenValue' => substr(str_shuffle($permitted_chars), 0, 32)]);

        $this->tpl->assign('profilePic', $this->userRepo->getProfilePicture(session('userdata.id')));
        $this->tpl->assign('values', $values);

        $this->tpl->assign('userLang', $userLang);
        $this->tpl->assign('userTheme', $userTheme);
        $this->tpl->assign('themeFont', $themeFont);
        $this->tpl->assign('userColorMode', $userColorMode);
        $this->tpl->assign('userColorScheme', $userColorScheme);
        $this->tpl->assign('languageList', $this->language->getLanguageList());
        $this->tpl->assign('dateFormat', $userDateFormat);
        $this->tpl->assign('timeFormat', $userTimeFormat);
        $this->tpl->assign('dateTimeValues', $this->getSupportedDateTimeFormats());
        $this->tpl->assign('timezone', $timezone);
        $this->tpl->assign('availableColorSchemes', $availableColorSchemes);
        $this->tpl->assign('availableFonts', $this->themeCore->getAvailableFonts());
        $this->tpl->assign('availableThemes', $this->themeCore->getAll());
        $this->tpl->assign('timezoneOptions', $timezonesAvailable);

        $this->tpl->assign('user', $row);

        // Notification preferences: event-type categories
        $notificationCategories = Notification::NOTIFICATION_CATEGORIES;
        $enabledEventTypes = $this->settingsService->getSetting('usersettings.'.$this->userId.'.notificationEventTypes');
        if (! $enabledEventTypes) {
            $enabledEventTypes = $this->settingsService->getSetting('companysettings.defaultNotificationEventTypes');
        }
        if ($enabledEventTypes) {
            $enabledEventTypes = json_decode($enabledEventTypes, true);
        }
        if (! is_array($enabledEventTypes)) {
            $enabledEventTypes = Notification::getCategoryKeys();
        }

        // Notification preferences: per-project notification levels
        $projectNotificationLevels = $this->loadProjectNotificationLevels();

        // Use the same access-aware project list as the navigation project dropdown
        // This respects direct assignment, org-wide (psettings='all'), and client-scoped access
        // but does NOT include the admin bypass that shows all projects unconditionally
        $projectData = $this->projectService->getProjectHierarchyAvailableToUser($this->userId);
        $userProjects = $projectData['allAvailableProjects'] ?? [];

        $companyDefaultRelevance = $this->settingsService->getSetting('companysettings.defaultNotificationRelevance');
        if (! $companyDefaultRelevance || ! Notification::isValidRelevanceLevel($companyDefaultRelevance)) {
            $companyDefaultRelevance = Notification::RELEVANCE_ALL;
        }

        $this->tpl->assign('notificationCategories', $notificationCategories);
        $this->tpl->assign('enabledEventTypes', $enabledEventTypes);
        $this->tpl->assign('projectNotificationLevels', $projectNotificationLevels);
        $this->tpl->assign('companyDefaultRelevance', $companyDefaultRelevance);
        $this->tpl->assign('relevanceLevels', Notification::RELEVANCE_LEVELS);
        $this->tpl->assign('userProjects', $userProjects);

        return $this->tpl->display('users.editOwn');
    }

    /**
     * @throws \Exception
     */
    public function post(): Response
    {

        // Save Profile Info
        $tab = '';

        if (isset($_POST[session('formTokenName')]) && session()->exists('formTokenName') && $_POST[session('formTokenName')] === session('formTokenValue')) {
            $row = $this->userRepo->getUser($this->userId);

            // profile Info
            if (isset($_POST['profileInfo'])) {
                $tab = '#myProfile';

                $values = [
                    'firstname' => ($_POST['firstname']) ?? $row['firstname'],
                    'lastname' => ($_POST['lastname']) ?? $row['lastname'],
                    'user' => ($_POST['user']) ?? $row['username'],
                    'phone' => ($_POST['phone']) ?? $row['phone'],
                    'notifications' => $row['notifications'],
                    'twoFAEnabled' => $row['twoFAEnabled'],
                ];

                $changedEmail = 0;
                if ($row['username'] !== $values['user']) {
                    $changedEmail = 1;
                }

                // Validation
                if ($values['user'] !== '') {
                    if (filter_var($values['user'], FILTER_VALIDATE_EMAIL)) {
                        if ($changedEmail === 1) {
                            if ($this->userRepo->usernameExist($values['user'], $this->userId) === false) {
                                $this->userService->editOwn($values, $this->userId);
                                $this->tpl->setNotification($this->language->__('notifications.profile_edited'), 'success', 'profile_edited');
                            } else {
                                $this->tpl->setNotification($this->language->__('notification.user_exists'), 'error');
                            }
                        } else {
                            $this->userService->editOwn($values, $this->userId);
                            $this->tpl->setNotification($this->language->__('notifications.profile_edited'), 'success', 'profile_edited');
                        }
                    } else {
                        $this->tpl->setNotification($this->language->__('notification.no_valid_email'), 'error');
                    }
                } else {
                    $this->tpl->setNotification($this->language->__('notification.enter_email'), 'error');
                }
            }

            // Save Password
            if (isset($_POST['savepw'])) {
                $tab = '#security';

                $values = [
                    'firstname' => $row['firstname'],
                    'lastname' => $row['lastname'],
                    'user' => $row['username'],
                    'phone' => $row['phone'],
                    'password' => $row['password'],
                    'notifications' => $row['notifications'],
                    'twoFAEnabled' => $row['twoFAEnabled'],
                ];

                if (password_verify($_POST['currentPassword'], $values['password'])) {

                    if ($_POST['newPassword'] === $_POST['confirmPassword']) {
                        if ($this->userService->checkPasswordStrength($_POST['newPassword'])) {
                            $values['password'] = $_POST['newPassword'];
                            $this->userRepo->editOwn($values, $this->userId);
                            $this->tpl->setNotification(
                                $this->language->__('notifications.password_changed'),
                                'success',
                                'password_edited'
                            );
                        } else {
                            $this->tpl->setNotification(
                                $this->language->__('notification.password_not_strong_enough'),
                                'error'
                            );
                        }
                    } else {
                        $this->tpl->setNotification(
                            $this->language->__('notification.passwords_dont_match'),
                            'error'
                        );
                    }
                } else {
                    $this->tpl->setNotification(
                        $this->language->__('notification.previous_password_incorrect'),
                        'error'
                    );
                }
            }

            if (isset($_POST['saveTheme'])) {
                $tab = '#theme';

                $postTheme = htmlentities($_POST['theme'] ?? 'default');
                $postColorMode = htmlentities($_POST['colormode'] ?? 'light');
                $postColorScheme = htmlentities($_POST['colorscheme'] ?? 'themeDefault');
                $themeFont = htmlentities($_POST['themeFont']);

                $this->settingsService->saveSetting('usersettings.'.$this->userId.'.theme', $postTheme);
                $this->settingsService->saveSetting('usersettings.'.$this->userId.'.colorMode', $postColorMode);
                $this->settingsService->saveSetting('usersettings.'.$this->userId.'.colorScheme', $postColorScheme);
                $this->settingsService->saveSetting('usersettings.'.$this->userId.'.themeFont', $themeFont);
                $this->themeCore::clearCache();
                $this->themeCore->setActive($postTheme);
                $this->themeCore->setColorMode($postColorMode);
                $this->themeCore->setColorScheme($postColorScheme);
                $this->themeCore->setFont($themeFont);

                $this->tpl->setNotification($this->language->__('notifications.changed_profile_settings_successfully'), 'success', 'themsettings_updated');
            }

            // Save Look & Feel
            if (isset($_POST['saveSettings'])) {
                $tab = '#settings';

                $postLang = htmlentities($_POST['language']);

                $dateFormat = htmlentities($_POST['date_format']);
                $timeFormat = htmlentities($_POST['time_format']);
                $tz = htmlentities($_POST['timezone']);

                $this->settingsService->saveSetting('usersettings.'.$this->userId.'.language', $postLang);
                $this->settingsService->saveSetting('usersettings.'.$this->userId.'.date_format', $dateFormat);
                $this->settingsService->saveSetting('usersettings.'.$this->userId.'.time_format', $timeFormat);
                $this->settingsService->saveSetting('usersettings.'.$this->userId.'.timezone', $tz);

                session()->forget('cache.language_resources_'.$this->language->getCurrentLanguage());

                session(['usersettings.date_format' => $dateFormat]);
                session(['usersettings.time_format' => $timeFormat]);
                session(['usersettings.timezone' => $tz]);

                // Clear the localization cache so middleware re-fetches on next request
                session()->forget('localization.cached');

                $this->language->setLanguage($postLang);

                $this->tpl->setNotification($this->language->__('notifications.changed_profile_settings_successfully'), 'success', 'profilesettings_updated');
            }

            // Save Profile Image
            if (isset($_POST['profileImage'])) {
                $tab = '#myProfile';
            }

            // Save Notifications
            if (isset($_POST['savenotifications'])) {
                $tab = '#notifications';

                $values = [
                    'firstname' => $row['firstname'],
                    'lastname' => $row['lastname'],
                    'user' => $row['username'],
                    'phone' => $row['phone'],
                    'notifications' => $row['notifications'],
                    'twoFAEnabled' => $row['twoFAEnabled'],
                ];

                if (isset($_POST['notifications'])) {
                    $values['notifications'] = 1;
                } else {
                    $values['notifications'] = 0;
                }

                $this->userRepo->editOwn($values, $this->userId);

                // Storing option messagefrequency
                $this->settingsService->saveSetting('usersettings.'.$this->userId.'.messageFrequency', (int) ($_POST['messagesfrequency'] ?? 3600));

                // Save event-type preferences
                $enabledEventTypes = $_POST['enabledEventTypes'] ?? [];
                if (! is_array($enabledEventTypes)) {
                    $enabledEventTypes = [];
                }
                $validCategories = array_keys(Notification::NOTIFICATION_CATEGORIES);
                $enabledEventTypes = array_values(array_intersect($enabledEventTypes, $validCategories));
                $this->settingsService->saveSetting(
                    'usersettings.'.$this->userId.'.notificationEventTypes',
                    json_encode($enabledEventTypes)
                );

                // Save per-project notification levels
                $projectLevels = $_POST['projectNotificationLevel'] ?? [];
                if (! is_array($projectLevels)) {
                    $projectLevels = [];
                }
                $validatedLevels = [];
                foreach ($projectLevels as $projectId => $level) {
                    if (Notification::isValidRelevanceLevel($level)) {
                        $validatedLevels[(int) $projectId] = $level;
                    }
                }
                $this->settingsService->saveSetting(
                    'usersettings.'.$this->userId.'.projectNotificationLevels',
                    json_encode($validatedLevels)
                );
                // Clean up old format if it exists
                $oldSetting = $this->settingsService->getSetting('usersettings.'.$this->userId.'.projectMutedNotifications');
                if ($oldSetting !== false && $oldSetting !== null) {
                    $this->settingsService->saveSetting(
                        'usersettings.'.$this->userId.'.projectMutedNotifications',
                        ''
                    );
                }

                $this->tpl->setNotification($this->language->__('notifications.changed_profile_settings_successfully'), 'success', 'profilesettings_updated');
            }
        } else {
            $this->tpl->setNotification($this->language->__('notification.form_token_incorrect'), 'error');
        }

        // Redirect
        return FrontcontrollerCore::redirect(BASE_URL.'/users/editOwn'.$tab);
    }

    /**
     * Returns list of supported varying date-time formats.
     *
     * @link https://www.php.net/manual/en/class.datetimeinterface.php#datetimeinterface.constants.types
     *
     * @return array<string> Format of ID => date-time string
     */
    private function getSupportedDateTimeFormats(): array
    {
        return [
            'dates' => [
                $this->language->__('language.dateformat'),
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
                $this->language->__('language.timeformat'),
                'H:i P',
                'H:i O',
                'H:i T',
                'H:i:s',
                'H:i',
            ],
        ];
    }

    /**
     * Loads per-project notification levels for the current user.
     *
     * Performs lazy migration from the old binary mute format
     * (projectMutedNotifications: JSON array of project IDs)
     * to the new three-level format
     * (projectNotificationLevels: JSON map of projectId -> relevance level).
     *
     * @return array<int, string> Map of project ID to relevance level.
     */
    private function loadProjectNotificationLevels(): array
    {
        $newSetting = $this->settingsService->getSetting('usersettings.'.$this->userId.'.projectNotificationLevels');
        if ($newSetting) {
            $decoded = json_decode($newSetting, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        // Lazy migration: convert old muted-projects array to new format
        $oldSetting = $this->settingsService->getSetting('usersettings.'.$this->userId.'.projectMutedNotifications');
        if ($oldSetting) {
            $mutedIds = json_decode($oldSetting, true);
            if (is_array($mutedIds) && count($mutedIds) > 0) {
                $migrated = [];
                foreach ($mutedIds as $projectId) {
                    $migrated[(int) $projectId] = Notification::RELEVANCE_MUTED;
                }
                // Save in new format
                $this->settingsService->saveSetting(
                    'usersettings.'.$this->userId.'.projectNotificationLevels',
                    json_encode($migrated)
                );
                // Clear old format
                $this->settingsService->saveSetting(
                    'usersettings.'.$this->userId.'.projectMutedNotifications',
                    ''
                );

                return $migrated;
            }
        }

        return [];
    }
}
