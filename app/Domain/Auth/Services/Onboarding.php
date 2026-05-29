<?php

namespace Leantime\Domain\Auth\Services;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\UI\Theme;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Setting\Services\Setting as SettingService;
use Leantime\Domain\Users\Services\Users as UserService;

/**
 * Onboarding service - encapsulates the multi-step user invite / onboarding
 * flow that was previously orchestrated inside the UserInvite controller.
 */
class Onboarding
{
    use DispatchesEvents;

    /**
     * The event context the onboarding controller used to dispatch events under.
     *
     * Onboarding events are dispatched from this service, but the original
     * (controller-based) event names must be preserved so registered listeners
     * (including plugins) keep matching. Passing this fully-qualified context to
     * the dispatch helpers keeps the emitted event names byte-identical.
     */
    private const EVENT_CONTEXT = 'leantime.domain.auth.controllers.userinvite.post';

    /**
     * init - initializes the service dependencies.
     */
    public function __construct(
        private AuthService $authService,
        private UserService $userService,
        private SettingService $settingService,
        private Theme $themeCore,
        private LanguageCore $language
    ) {}

    /**
     * getInviteSettings - builds the defaulted settings payload used to render the
     * onboarding screens for a given invited user.
     *
     * @param  array  $user  the invited user record
     * @return array the resolved settings (theme, colorMode, colorScheme, themeFont,
     *               date/time formats, timezone, workdays and daySchedule plus the
     *               available option catalogs)
     *
     * @api
     */
    public function getInviteSettings(array $user): array
    {
        $userId = $user['id'];

        $userTheme = $this->settingService->getSetting('usersettings.'.$userId.'.theme');
        if (! $userTheme) {
            $userTheme = 'default';
        }

        $userColorMode = $this->settingService->getSetting('usersettings.'.$userId.'.colorMode');
        if (! $userColorMode) {
            $userColorMode = 'light';
        }

        $userColorScheme = $this->settingService->getSetting('usersettings.'.$userId.'.colorScheme');
        if (! $userColorScheme) {
            $userColorScheme = 'companyColors';
        }

        $themeFont = $this->settingService->getSetting('usersettings.'.$userId.'.themeFont');
        if (! $themeFont) {
            $themeFont = 'Roboto';
        }

        $userDateFormat = $this->settingService->getSetting('usersettings.'.$userId.'.date_format');
        $userTimeFormat = $this->settingService->getSetting('usersettings.'.$userId.'.time_format');

        $timezone = $this->settingService->getSetting('usersettings.'.$userId.'.timezone');
        if (! $timezone) {
            $timezone = date_default_timezone_get();
        }

        $workdays = $this->settingService->getSetting('usersettings.'.$userId.'.workdays');
        if (! $workdays) {
            $workdays = $this->getDefaultWorkdays();
        } else {
            $workdays = safe_unserialize($workdays, []);
        }

        $daySchedule = $this->settingService->getSetting('usersettings.'.$userId.'.daySchedule');
        if ($daySchedule) {
            $daySchedule = safe_unserialize($daySchedule, []);
        } else {
            $daySchedule = $this->getDefaultDaySchedule();
        }

        return [
            'userTheme' => $userTheme,
            'userColorMode' => $userColorMode,
            'userColorScheme' => $userColorScheme,
            'themeFont' => $themeFont,
            'dateFormat' => $userDateFormat,
            'timeFormat' => $userTimeFormat,
            'dateTimeValues' => $this->getSupportedDateTimeFormats(),
            'timezone' => $timezone,
            'timezoneOptions' => timezone_identifiers_list(),
            'availableColorSchemes' => $this->themeCore->getAvailableColorSchemes(),
            'availableFonts' => $this->themeCore->getAvailableFonts(),
            'fontTooltips' => $this->themeCore->fontTooltips,
            'availableThemes' => $this->themeCore->getAll(),
            'languageList' => $this->language->getLanguageList(),
            'workdays' => $workdays,
            'daySchedule' => $daySchedule,
            'dayHourOptions' => $this->getDayHourOptions(),
        ];
    }

    /**
     * getDefaultWorkdays - returns the default weekly working hours used when a
     * user has not yet configured their schedule.
     *
     * @return array<int, array{start: string, end: string}>
     */
    public function getDefaultWorkdays(): array
    {
        return [
            1 => ['start' => '09:00', 'end' => '17:00'],
            2 => ['start' => '09:00', 'end' => '17:00'],
            3 => ['start' => '09:00', 'end' => '17:00'],
            4 => ['start' => '09:00', 'end' => '17:00'],
            5 => ['start' => '09:00', 'end' => '17:00'],
        ];
    }

    /**
     * getDefaultDaySchedule - returns the default daily schedule used when a user
     * has not yet configured one.
     *
     * @return array<string, int>
     */
    public function getDefaultDaySchedule(): array
    {
        return [
            'wakeup' => 6,
            'workStart' => 8,
            'lunch' => 12,
            'workEnd' => 16,
            'bed' => 22,
        ];
    }

    /**
     * getDayHourOptions - returns the catalog of selectable two-hour blocks used
     * when configuring the daily schedule.
     *
     * @return array<int, array{start: string, end: string}>
     */
    public function getDayHourOptions(): array
    {
        return [
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
    }

    /**
     * getSupportedDateTimeFormats - returns the catalog of supported date and time
     * format options shown during onboarding.
     *
     * @return array{dates: array<int, string>, times: array<int, string>}
     */
    public function getSupportedDateTimeFormats(): array
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
     * saveAccount - first onboarding step: validates the chosen password, assembles
     * the user record from the submitted profile fields and persists it.
     *
     * The plaintext password is stored in the session (tempPassword) so the user can
     * be logged in automatically once onboarding completes, mirroring the original flow.
     *
     * @param  array  $userInvite  the invited user record (resolved from the invite link)
     * @param  string  $name  the full name as submitted (split into first/last name)
     * @param  string  $jobTitle  the submitted job title
     * @param  string  $password  the chosen password
     * @return string 'weak' if the password is not strong enough, 'saved' if the user was
     *                persisted, 'error' if persistence failed
     *
     * @api
     */
    public function saveAccount(array $userInvite, string $name, string $jobTitle, string $password): string
    {
        if (! $this->userService->checkPasswordStrength($password)) {
            return 'weak';
        }

        $nameParts = explode(' ', $name);
        $userInvite['firstname'] = $nameParts[0];
        $userInvite['lastname'] = $nameParts[1] ?? '';
        $userInvite['jobTitle'] = $jobTitle;
        $userInvite['status'] = 'i';
        $userInvite['user'] = $userInvite['username'];
        $userInvite['password'] = $password;

        session(['tempPassword' => $password]);

        if ($this->userService->editUser($userInvite, $userInvite['id'])) {
            return 'saved';
        }

        return 'error';
    }

    /**
     * saveThemeChoice - second onboarding step: persists the chosen theme and font,
     * activates them and dispatches the related onboarding events.
     *
     * @param  array  $userInvite  the invited user record
     * @param  string  $theme  the chosen theme
     * @param  string  $themeFont  the chosen font
     *
     * @throws BindingResolutionException
     *
     * @api
     */
    public function saveThemeChoice(array $userInvite, string $theme, string $themeFont): void
    {
        $postTheme = htmlentities($theme);
        $font = htmlentities($themeFont);

        $this->settingService->saveSetting('usersettings.'.$userInvite['id'].'.theme', $postTheme);
        $this->settingService->saveSetting('usersettings.'.$userInvite['id'].'.themeFont', $font);

        $this->themeCore->clearCache();
        $this->themeCore->setActive($postTheme);
        $this->themeCore->setFont($font);
        $this->themeCore->clearCache();

        self::dispatchEvent('onboarding_themechoice_'.$postTheme, [], self::EVENT_CONTEXT);
        self::dispatchEvent('onboarding_themechoice_'.$font, [], self::EVENT_CONTEXT);
    }

    /**
     * saveColorChoice - third onboarding step: persists the chosen color mode and
     * scheme, activates them and dispatches the related onboarding events.
     *
     * @param  array  $userInvite  the invited user record
     * @param  string  $colorMode  the chosen color mode
     * @param  string  $colorScheme  the chosen color scheme
     *
     * @throws BindingResolutionException
     *
     * @api
     */
    public function saveColorChoice(array $userInvite, string $colorMode, string $colorScheme): void
    {
        $postColorMode = htmlentities($colorMode);
        $postColorScheme = htmlentities($colorScheme);

        $this->settingService->saveSetting('usersettings.'.$userInvite['id'].'.colorMode', $postColorMode);
        $this->settingService->saveSetting('usersettings.'.$userInvite['id'].'.colorScheme', $postColorScheme);

        self::dispatchEvent('onboarding_colorchoice_'.$postColorMode, [], self::EVENT_CONTEXT);
        self::dispatchEvent('onboarding_colorchoice_'.$postColorScheme, [], self::EVENT_CONTEXT);

        $this->themeCore->clearCache();
        $this->themeCore->setColorMode($postColorMode);
        $this->themeCore->setColorScheme($postColorScheme);
        $this->themeCore->clearCache();
    }

    /**
     * saveSchedule - fourth onboarding step: assembles the day schedule from the
     * submitted values, dispatches the related onboarding events and persists it.
     *
     * @param  array  $userInvite  the invited user record
     * @param  string  $workStart  the submitted work start block
     * @param  string  $lunch  the submitted lunch block
     * @param  string  $workEnd  the submitted work end block
     *
     * @throws BindingResolutionException
     *
     * @api
     */
    public function saveSchedule(array $userInvite, string $workStart, string $lunch, string $workEnd): void
    {
        $daySchedule = [
            'wakeup' => '',
            'workStart' => $workStart,
            'lunch' => $lunch,
            'workEnd' => $workEnd,
            'bed' => '',
        ];

        self::dispatchEvent('onboarding_schedule_start_'.$daySchedule['workStart'], [], self::EVENT_CONTEXT);
        self::dispatchEvent('onboarding_schedule_lunch_'.$daySchedule['lunch'], [], self::EVENT_CONTEXT);
        self::dispatchEvent('onboarding_schedule_end_'.$daySchedule['workEnd'], [], self::EVENT_CONTEXT);

        $this->settingService->saveSetting('usersettings.'.$userInvite['id'].'.daySchedule', serialize($daySchedule));
    }

    /**
     * completeOnboarding - final onboarding step: activates the user, dispatches the
     * onboarding-finished and signup-success events, then logs the user in using the
     * temporary password captured during account setup.
     *
     * @param  array  $userInvite  the invited user record
     * @return bool true if the user was successfully logged in, false otherwise
     *
     * @throws BindingResolutionException
     *
     * @api
     */
    public function completeOnboarding(array $userInvite): bool
    {
        $userInvite['status'] = 'A';
        $userInvite['password'] = '';
        $userInvite['user'] = $userInvite['username'];

        $this->userService->editUser($userInvite, $userInvite['id']);

        self::dispatchEvent('onboarding_finished', [], self::EVENT_CONTEXT);

        $loggedIn = $this->authService->login($userInvite['username'], session('tempPassword'));

        session()->forget('tempPassword');

        self::dispatch_event('userSignUpSuccess', ['user' => $userInvite], self::EVENT_CONTEXT);

        return $loggedIn;
    }
}
