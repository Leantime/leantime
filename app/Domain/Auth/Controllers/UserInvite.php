<?php

namespace Leantime\Domain\Auth\Controllers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Http\Controller\Controller;
use Leantime\Core\Routing\Frontcontroller as FrontcontrollerCore;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\UI\Theme;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Setting\Services\Setting;
use Leantime\Domain\Users\Services\Users as UserService;
use Symfony\Component\HttpFoundation\Response;

class UserInvite extends Controller
{
    use DispatchesEvents;

    private AuthService $authService;

    private UserService $userService;

    private Setting $settingService;

    private Theme $themeCore;

    /**
     * init - initializes the objects for the class
     *
     *
     * @param  AuthService  $authService  The AuthService object
     * @param  UserService  $userService  The UserService object
     * @param  Setting  $settingService  The Setting object
     *
     * @throws \Exception
     */
    public function init(
        AuthService $authService,
        UserService $userService,
        Setting $settingService,
        Theme $theme
    ): void {
        $this->authService = $authService;
        $this->userService = $userService;
        $this->settingService = $settingService;
        $this->themeCore = $theme;
    }

    /**
     * get - handle get requests
     *
     *
     *
     *
     * @throws \Exception
     */
    public function get(array $params): Response
    {
        if (isset($params['id']) === true) {

            $inviteId = htmlspecialchars($params['id']);
            $user = $this->authService->getUserByInviteLink($params['id']);

            if (! $user) {
                return FrontcontrollerCore::redirect(BASE_URL.'/auth/login');
            }

            $userTheme = $this->settingService->getSetting('usersettings.'.$user['id'].'.theme');
            if (! $userTheme) {
                $userTheme = 'default';
            }

            $userColorMode = $this->settingService->getSetting('usersettings.'.$user['id'].'.colorMode');
            if (! $userColorMode) {
                $userColorMode = 'light';
            }

            $userColorScheme = $this->settingService->getSetting('usersettings.'.$user['id'].'.colorScheme');
            if (! $userColorScheme) {
                $userColorScheme = 'companyColors';
            }

            $themeFont = $this->settingService->getSetting('usersettings.'.$user['id'].'.themeFont');
            if (! $themeFont) {
                $themeFont = 'Roboto';
            }

            $userDateFormat = $this->settingService->getSetting('usersettings.'.$user['id'].'.date_format');
            $userTimeFormat = $this->settingService->getSetting('usersettings.'.$user['id'].'.time_format');

            $timezone = $this->settingService->getSetting('usersettings.'.$user['id'].'.timezone');

            if (! $timezone) {
                $timezone = date_default_timezone_get();
            }

            $timezonesAvailable = timezone_identifiers_list();

            $availableColorSchemes = $this->themeCore->getAvailableColorSchemes();

            $workdays = $this->settingService->getSetting('usersettings.'.$user['id'].'.workdays');
            if (! $workdays) {
                $workdays = [
                    1 => [
                        'start' => '09:00',
                        'end' => '17:00',
                    ],
                    2 => [
                        'start' => '09:00',
                        'end' => '17:00',
                    ],
                    3 => [
                        'start' => '09:00',
                        'end' => '17:00',
                    ],
                    4 => [
                        'start' => '09:00',
                        'end' => '17:00',
                    ],
                    5 => [
                        'start' => '09:00',
                        'end' => '17:00',
                    ],
                ];
            } else {
                $workdays = unserialize($workdays);
            }

            $dayHourOptions = [
                0 => ['start' => '0:00', 'end' => '2:00'],
                2 => ['start' => '2:00', 'end' => '4:00'],
                4 => ['start' => '4:00', 'end' => '6:00'],
                6 => ['start' => '6:00', 'end' => '8:00'],
                8 => ['start' => '8:00', 'end' => '10:00'],
                10 => ['start' => '10:00', 'end' => '12:00'],
                12 => ['start' => '12:00', 'end' => '14:00'],
                14 => ['start' => '14:00', 'end' => '16:00'],
                16 => ['start' => '16:00', 'end' => '18:00'],
                18 => ['start' => '18:00', 'end' => '20:00'],
                20 => ['start' => '20:00', 'end' => '22:00'],
                22 => ['start' => '22:00', 'end' => '0:00'],
            ];

            $daySchedule = $this->settingService->getSetting('usersettings.'.$user['id'].'.daySchedule');

            if ($daySchedule) {
                $daySchedule = unserialize($daySchedule);
            } else {
                $daySchedule = [
                    'wakeup' => 6,
                    'workStart' => 8,
                    'lunch' => 12,
                    'workEnd' => 16,
                    'bed' => 22,
                ];
            }

            $this->tpl->assign('user', $user);
            $this->tpl->assign('themeCore', $this->themeCore);
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
            $this->tpl->assign('fontTooltips', $this->themeCore->fontTooltips);
            $this->tpl->assign('availableThemes', $this->themeCore->getAll());
            $this->tpl->assign('timezoneOptions', $timezonesAvailable);
            $this->tpl->assign('workdays', $workdays);
            $this->tpl->assign('daySchedule', $daySchedule);
            $this->tpl->assign('dayHourOptions', $dayHourOptions);
            $this->tpl->assign('inviteId', $inviteId);

            if (isset($_GET['step']) && is_numeric($_GET['step'])) {
                return $this->tpl->display('auth.userInvite'.$_GET['step'], 'entry');
            }

            return $this->tpl->display('auth.userInvite', 'entry');
        }

        return FrontcontrollerCore::redirect(BASE_URL.'/errors/error404');
    }

    /**
     * post - handle post requests
     *
     *
     *
     *
     * @throws BindingResolutionException
     */
    public function post(array $params): Response
    {

        $invitationId = $params['id'] ?? '';

        $userInvite = $this->authService->getUserByInviteLink($invitationId);
        if (! $userInvite) {
            return FrontcontrollerCore::redirect(BASE_URL.'/auth/login');
        }

        // Step 1
        if (isset($_POST['saveAccount']) && isset($_POST['step'])) {

            if (! $this->userService->checkPasswordStrength($_POST['password'] ?? '')) {
                $this->tpl->setNotification(
                    $this->language->__('notification.password_not_strong_enough'),
                    'error'
                );

                return FrontcontrollerCore::redirect(BASE_URL.'/auth/userInvite/'.$invitationId);
            }

            $name = explode(' ', $_POST['name']);
            $userInvite['firstname'] = $name[0];
            $userInvite['lastname'] = $name[1] ?? '';
            $userInvite['jobTitle'] = $_POST['jobTitle'] ?? '';
            $userInvite['status'] = 'I';
            $userInvite['user'] = $userInvite['username'];
            $userInvite['password'] = $_POST['password'];
            session(['tempPassword' => $_POST['password']]);

            $editUser = $this->userService->editUser($userInvite, $userInvite['id']);

            if ($editUser) {
                return FrontcontrollerCore::redirect(BASE_URL.'/auth/userInvite/'.$invitationId.'?step=2');
            } else {
                $this->tpl->setNotification(
                    $this->language->__('notifications.problem_updating_user'),
                    'error'
                );
            }
        }

        if (isset($_POST['step']) && $_POST['step'] == 2) {

            $postTheme = htmlentities($_POST['theme']);
            $themeFont = htmlentities($_POST['themeFont']);

            $this->settingService->saveSetting('usersettings.'.$userInvite['id'].'.theme', $postTheme);
            $this->settingService->saveSetting('usersettings.'.$userInvite['id'].'.themeFont', $themeFont);
            $this->themeCore->clearCache();
            $this->themeCore->setActive($postTheme);
            $this->themeCore->setFont($themeFont);
            $this->themeCore->clearCache();

            self::dispatchEvent('onboarding_themechoice_'.$postTheme);
            self::dispatchEvent('onboarding_themechoice_'.$themeFont);

            return FrontcontrollerCore::redirect(BASE_URL.'/auth/userInvite/'.$invitationId.'?step=3');
        }

        if (isset($_POST['step']) && $_POST['step'] == 3) {

            $postColorMode = htmlentities($_POST['colormode']);
            $postColorScheme = htmlentities($_POST['colorscheme'] ?? 'themeDefault');

            $this->settingService->saveSetting('usersettings.'.$userInvite['id'].'.colorMode', $postColorMode);
            $this->settingService->saveSetting('usersettings.'.$userInvite['id'].'.colorScheme', $postColorScheme);

            self::dispatchEvent('onboarding_colorchoice_'.$postColorMode);
            self::dispatchEvent('onboarding_colorchoice_'.$postColorScheme);

            $this->themeCore->clearCache();
            $this->themeCore->setColorMode($postColorMode);
            $this->themeCore->setColorScheme($postColorScheme);
            $this->themeCore->clearCache();

            return FrontcontrollerCore::redirect(BASE_URL.'/auth/userInvite/'.$invitationId.'?step=4');
        }

        if (isset($_POST['step']) && $_POST['step'] == 4) {

            $workdays = [];

            $daySchedule = [
                'wakeup' => '',
                'workStart' => $_POST['daySchedule-workStart'] ?? '',
                'lunch' => $_POST['daySchedule-lunch'] ?? '',
                'workEnd' => $_POST['daySchedule-workEnd'] ?? '',
                'bed' => '',
            ];

            /*
            for ($i = 1; $i <= 7; $i++) {
                if (isset($_POST['dayOfWeek-'.$i])) {

                    $start = strtotime($_POST['dayOfWeek-'.$i.'-start']) ? $_POST['dayOfWeek-'.$i.'-start'] : '09:00';
                    $end = strtotime($_POST['dayOfWeek-'.$i.'-end']) ? $_POST['dayOfWeek-'.$i.'-end'] : '17:00';
                    self::dispatchEvent("onboarding_schedule_");
                    $workdays[$i] = [
                        'start' => $start,
                        'end' => $end,
                    ];
                }
            }*/
            self::dispatchEvent('onboarding_schedule_start_'.$daySchedule['workStart']);
            self::dispatchEvent('onboarding_schedule_lunch_'.$daySchedule['lunch']);
            self::dispatchEvent('onboarding_schedule_end_'.$daySchedule['workEnd']);

            $this->settingService->saveSetting('usersettings.'.$userInvite['id'].'.daySchedule', serialize($daySchedule));

            return FrontcontrollerCore::redirect(BASE_URL.'/auth/userInvite/'.$invitationId.'?step=5');
        }

        if (isset($_POST['step']) && $_POST['step'] == 5) {

            $userInvite['status'] = 'A';
            $userInvite['password'] = '';
            $userInvite['user'] = $userInvite['username'];

            $result = $this->userService->editUser($userInvite, $userInvite['id']);

            self::dispatchEvent('onboarding_finished');

            $this->tpl->setNotification(
                $this->language->__('notifications.you_are_active'),
                'success',
                'user_activated'
            );
            $loggedIn = $this->authService->login($userInvite['username'], session('tempPassword'));

            session()->forget('tempPassword');

            self::dispatch_event('userSignUpSuccess', ['user' => $userInvite]);

            if ($loggedIn) {
                return FrontcontrollerCore::redirect(BASE_URL.'/dashboard/home');
            } else {
                return FrontcontrollerCore::redirect(BASE_URL.'/auth/login');
            }
        }

        return FrontcontrollerCore::redirect(BASE_URL.'/auth/userInvite/'.$invitationId);
    }

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
}
