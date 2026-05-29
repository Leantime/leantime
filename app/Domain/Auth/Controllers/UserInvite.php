<?php

namespace Leantime\Domain\Auth\Controllers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller as FrontcontrollerCore;
use Leantime\Core\UI\Theme;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Auth\Services\Onboarding as OnboardingService;
use Symfony\Component\HttpFoundation\Response;

class UserInvite extends Controller
{
    private AuthService $authService;

    private OnboardingService $onboardingService;

    private Theme $themeCore;

    /**
     * init - initializes the objects for the class
     *
     *
     * @param  AuthService  $authService  The AuthService object
     * @param  OnboardingService  $onboardingService  The Onboarding service object
     * @param  Theme  $theme  The Theme object
     *
     * @throws \Exception
     */
    public function init(
        AuthService $authService,
        OnboardingService $onboardingService,
        Theme $theme
    ): void {
        $this->authService = $authService;
        $this->onboardingService = $onboardingService;
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

            $inviteSettings = $this->onboardingService->getInviteSettings($user);

            array_map([$this->tpl, 'assign'], array_keys($inviteSettings), array_values($inviteSettings));

            $this->tpl->assign('user', $user);
            $this->tpl->assign('themeCore', $this->themeCore);
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

            $result = $this->onboardingService->saveAccount(
                $userInvite,
                $_POST['name'] ?? '',
                $_POST['jobTitle'] ?? '',
                $_POST['password'] ?? ''
            );

            if ($result === 'weak') {
                $this->tpl->setNotification(
                    $this->language->__('notification.password_not_strong_enough'),
                    'error'
                );

                return FrontcontrollerCore::redirect(BASE_URL.'/auth/userInvite/'.$invitationId);
            }

            if ($result === 'saved') {
                return FrontcontrollerCore::redirect(BASE_URL.'/auth/userInvite/'.$invitationId.'?step=2');
            } else {
                $this->tpl->setNotification(
                    $this->language->__('notifications.problem_updating_user'),
                    'error'
                );
            }
        }

        if (isset($_POST['step']) && $_POST['step'] == 2) {

            $this->onboardingService->saveThemeChoice($userInvite, $_POST['theme'], $_POST['themeFont']);

            return FrontcontrollerCore::redirect(BASE_URL.'/auth/userInvite/'.$invitationId.'?step=3');
        }

        if (isset($_POST['step']) && $_POST['step'] == 3) {

            $this->onboardingService->saveColorChoice(
                $userInvite,
                $_POST['colormode'],
                $_POST['colorscheme'] ?? 'themeDefault'
            );

            return FrontcontrollerCore::redirect(BASE_URL.'/auth/userInvite/'.$invitationId.'?step=4');
        }

        if (isset($_POST['step']) && $_POST['step'] == 4) {

            $this->onboardingService->saveSchedule(
                $userInvite,
                $_POST['daySchedule-workStart'] ?? '',
                $_POST['daySchedule-lunch'] ?? '',
                $_POST['daySchedule-workEnd'] ?? ''
            );

            return FrontcontrollerCore::redirect(BASE_URL.'/auth/userInvite/'.$invitationId.'?step=5');
        }

        if (isset($_POST['step']) && $_POST['step'] == 5) {

            $this->tpl->setNotification(
                $this->language->__('notifications.you_are_active'),
                'success',
                'user_activated'
            );

            $loggedIn = $this->onboardingService->completeOnboarding($userInvite);

            if ($loggedIn) {
                return FrontcontrollerCore::redirect(BASE_URL.'/dashboard/home');
            } else {
                return FrontcontrollerCore::redirect(BASE_URL.'/auth/login');
            }
        }

        return FrontcontrollerCore::redirect(BASE_URL.'/auth/userInvite/'.$invitationId);
    }
}
