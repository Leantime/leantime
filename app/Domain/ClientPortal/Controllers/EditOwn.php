<?php

namespace Leantime\Domain\ClientPortal\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller as FrontcontrollerCore;
use Leantime\Core\UI\Theme as ThemeCore;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Setting\Services\Setting as SettingService;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Leantime\Domain\Users\Services\Users as UserService;
use Symfony\Component\HttpFoundation\Response;

/**
 * EditOwn (Client Portal) — Restricted account page for client users.
 * Surfaces only personal data + theme; no notification preferences, no
 * project-level settings, no employee info.
 *
 * GET  /clientportal/editOwn  → render form
 * POST /clientportal/editOwn  → save profile / password / theme / picture upload
 */
class EditOwn extends Controller
{
    private ThemeCore $themeCore;

    private UserRepository $userRepo;

    private SettingService $settingsService;

    private UserService $userService;

    private int $userId;

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function init(
        ThemeCore $themeCore,
        UserRepository $userRepo,
        SettingService $settingsService,
        UserService $userService,
    ): void {
        $role = session('userdata.role');
        if ($role !== Roles::$commenter && ! Auth::userIsAtLeast(Roles::$admin, true)) {
            FrontcontrollerCore::redirect(BASE_URL.'/dashboard/home');
        }

        $this->themeCore = $themeCore;
        $this->userRepo = $userRepo;
        $this->settingsService = $settingsService;
        $this->userService = $userService;
        $this->userId = (int) session('userdata.id');
    }

    /**
     * @throws \Exception
     */
    public function get(): Response
    {
        $row = $this->userRepo->getUser($this->userId);

        $userTheme = $this->settingsService->getSetting('usersettings.'.$this->userId.'.theme') ?: 'default';
        $userColorMode = $this->settingsService->getSetting('usersettings.'.$this->userId.'.colorMode') ?: 'light';
        $themeFont = $this->settingsService->getSetting('usersettings.'.$this->userId.'.themeFont') ?: 'Roboto';

        $availableColorSchemes = $this->themeCore->getAvailableColorSchemes();
        $userColorScheme = $this->settingsService->getSetting('usersettings.'.$this->userId.'.colorScheme');
        if (! $userColorScheme) {
            $userColorScheme = isset($availableColorSchemes['companyColors']) ? 'companyColors' : 'themeDefault';
        }

        $values = [
            'firstname' => $row['firstname'],
            'lastname' => $row['lastname'],
            'user' => $row['username'],
            'phone' => $row['phone'],
            'twoFAEnabled' => $row['twoFAEnabled'],
        ];

        $permitted = '123456789abcdefghijklmnopqrstuvwxyz';
        session(['formTokenName' => substr(str_shuffle($permitted), 0, 32)]);
        session(['formTokenValue' => substr(str_shuffle($permitted), 0, 32)]);

        $this->tpl->assign('profilePic', $this->userRepo->getProfilePicture($this->userId));
        $this->tpl->assign('values', $values);
        $this->tpl->assign('user', $row);
        $this->tpl->assign('userTheme', $userTheme);
        $this->tpl->assign('userColorMode', $userColorMode);
        $this->tpl->assign('userColorScheme', $userColorScheme);
        $this->tpl->assign('themeFont', $themeFont);
        $this->tpl->assign('availableColorSchemes', $availableColorSchemes);
        $this->tpl->assign('availableFonts', $this->themeCore->getAvailableFonts());
        $this->tpl->assign('availableThemes', $this->themeCore->getAll());

        return $this->tpl->display('clientportal.editOwn');
    }

    /**
     * @throws \Exception
     */
    public function post(): Response
    {
        $tab = '';

        if (
            ! isset($_POST[session('formTokenName')])
            || $_POST[session('formTokenName')] !== session('formTokenValue')
        ) {
            $this->tpl->setNotification($this->language->__('notification.form_token_incorrect'), 'error');

            return FrontcontrollerCore::redirect(BASE_URL.'/clientportal/editOwn');
        }

        $row = $this->userRepo->getUser($this->userId);

        // --- Profile info (name, email, phone) ---
        if (isset($_POST['profileInfo'])) {
            $tab = '#myProfile';

            $values = [
                'firstname' => $_POST['firstname'] ?? $row['firstname'],
                'lastname' => $_POST['lastname'] ?? $row['lastname'],
                'user' => $_POST['user'] ?? $row['username'],
                'phone' => $_POST['phone'] ?? $row['phone'],
                'notifications' => $row['notifications'],
                'twoFAEnabled' => $row['twoFAEnabled'],
            ];

            $changedEmail = $row['username'] !== $values['user'] ? 1 : 0;

            if ($values['user'] === '') {
                $this->tpl->setNotification($this->language->__('notification.enter_email'), 'error');
            } elseif (! filter_var($values['user'], FILTER_VALIDATE_EMAIL)) {
                $this->tpl->setNotification($this->language->__('notification.no_valid_email'), 'error');
            } elseif ($changedEmail === 1 && $this->userRepo->usernameExist($values['user'], $this->userId) !== false) {
                $this->tpl->setNotification($this->language->__('notification.user_exists'), 'error');
            } else {
                $this->userService->editOwn($values, $this->userId);
                $this->tpl->setNotification($this->language->__('notifications.profile_edited'), 'success', 'profile_edited');
            }
        }

        // --- Password ---
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

            if (! password_verify($_POST['currentPassword'] ?? '', $values['password'])) {
                $this->tpl->setNotification($this->language->__('notification.previous_password_incorrect'), 'error');
            } elseif (($_POST['newPassword'] ?? '') !== ($_POST['confirmPassword'] ?? '')) {
                $this->tpl->setNotification($this->language->__('notification.passwords_dont_match'), 'error');
            } elseif (! $this->userService->checkPasswordStrength($_POST['newPassword'] ?? '')) {
                $this->tpl->setNotification($this->language->__('notification.password_not_strong_enough'), 'error');
            } else {
                $values['password'] = $_POST['newPassword'];
                $this->userRepo->editOwn($values, $this->userId);
                $this->tpl->setNotification($this->language->__('notifications.password_changed'), 'success', 'password_edited');
            }
        }

        // --- Profile image upload (handled separately by JS uploader) ---
        if (isset($_POST['profileImage'])) {
            $tab = '#myProfile';
        }

        // --- Theme ---
        if (isset($_POST['saveTheme'])) {
            $tab = '#theme';

            $postTheme = htmlentities($_POST['theme'] ?? 'default');
            $postColorMode = htmlentities($_POST['colormode'] ?? 'light');
            $postColorScheme = htmlentities($_POST['colorscheme'] ?? 'themeDefault');
            $themeFont = htmlentities($_POST['themeFont'] ?? 'Roboto');

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

        return FrontcontrollerCore::redirect(BASE_URL.'/clientportal/editOwn'.$tab);
    }
}
