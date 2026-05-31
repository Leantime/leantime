<?php

namespace Leantime\Domain\Users\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller as FrontcontrollerCore;
use Leantime\Domain\Users\Services\Users as UserService;
use Symfony\Component\HttpFoundation\Response;

class EditOwn extends Controller
{
    private UserService $userService;

    private int $userId;

    /**
     * init - initialize private variables
     */
    public function init(UserService $userService): void
    {
        $this->userService = $userService;

        $this->userId = session('userdata.id');
    }

    /**
     * @throws \Exception
     */
    public function get(): Response
    {
        $permitted_chars = '123456789abcdefghijklmnopqrstuvwxyz';
        session(['formTokenName' => substr(str_shuffle($permitted_chars), 0, 32)]);
        session(['formTokenValue' => substr(str_shuffle($permitted_chars), 0, 32)]);

        $profileSettings = $this->userService->getOwnProfileSettings($this->userId);
        array_map([$this->tpl, 'assign'], array_keys($profileSettings), array_values($profileSettings));

        $notificationPreferences = $this->userService->getNotificationPreferences($this->userId);
        array_map([$this->tpl, 'assign'], array_keys($notificationPreferences), array_values($notificationPreferences));

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
            // profile Info
            if (isset($_POST['profileInfo'])) {
                $tab = '#myProfile';

                $result = $this->userService->saveOwnProfile($this->userId, $_POST);

                if ($result === 'success') {
                    $this->tpl->setNotification($this->language->__('notifications.profile_edited'), 'success', 'profile_edited');
                } elseif ($result === 'user_exists') {
                    $this->tpl->setNotification($this->language->__('notification.user_exists'), 'error');
                } elseif ($result === 'no_valid_email') {
                    $this->tpl->setNotification($this->language->__('notification.no_valid_email'), 'error');
                } else {
                    $this->tpl->setNotification($this->language->__('notification.enter_email'), 'error');
                }
            }

            // Save Password
            if (isset($_POST['savepw'])) {
                $tab = '#security';

                $result = $this->userService->changeOwnPassword(
                    $this->userId,
                    $_POST['currentPassword'],
                    $_POST['newPassword'],
                    $_POST['confirmPassword']
                );

                if ($result === 'success') {
                    $this->tpl->setNotification($this->language->__('notifications.password_changed'), 'success', 'password_edited');
                } elseif ($result === 'password_not_strong_enough') {
                    $this->tpl->setNotification($this->language->__('notification.password_not_strong_enough'), 'error');
                } elseif ($result === 'passwords_dont_match') {
                    $this->tpl->setNotification($this->language->__('notification.passwords_dont_match'), 'error');
                } else {
                    $this->tpl->setNotification($this->language->__('notification.previous_password_incorrect'), 'error');
                }
            }

            if (isset($_POST['saveTheme'])) {
                $tab = '#theme';

                $this->userService->saveOwnAppearanceSettings($this->userId, $_POST);

                $this->tpl->setNotification($this->language->__('notifications.changed_profile_settings_successfully'), 'success', 'themsettings_updated');
            }

            // Save Look & Feel
            if (isset($_POST['saveSettings'])) {
                $tab = '#settings';

                $this->userService->saveOwnLocaleSettings($this->userId, $_POST);

                $this->tpl->setNotification($this->language->__('notifications.changed_profile_settings_successfully'), 'success', 'profilesettings_updated');
            }

            // Save Profile Image
            if (isset($_POST['profileImage'])) {
                $tab = '#myProfile';
            }

            // Save Notifications
            if (isset($_POST['savenotifications'])) {
                $tab = '#notifications';

                $this->userService->saveOwnNotificationPreferences($this->userId, $_POST);

                $this->tpl->setNotification($this->language->__('notifications.changed_profile_settings_successfully'), 'success', 'profilesettings_updated');
            }
        } else {
            $this->tpl->setNotification($this->language->__('notification.form_token_incorrect'), 'error');
        }

        // Redirect
        return FrontcontrollerCore::redirect(BASE_URL.'/users/editOwn'.$tab);
    }
}
