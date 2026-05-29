<?php

namespace Leantime\Domain\TwoFA\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\TwoFA\Services\TwoFA as TwoFAService;
use RobThree\Auth\TwoFactorAuthException;
use Symfony\Component\HttpFoundation\Response;

class Edit extends Controller
{
    private TwoFAService $twoFAService;

    /**
     * Initializes dependencies.
     */
    public function init(TwoFAService $twoFAService): void
    {
        $this->twoFAService = $twoFAService;
    }

    /**
     * Displays the 2FA setup/edit page.
     *
     * @param  array  $params  Request parameters
     *
     * @throws TwoFactorAuthException
     */
    public function get(array $params): Response
    {
        $this->assignSetupData();
        $this->generateFormTokens();

        return $this->tpl->display('twofa.edit');
    }

    /**
     * Handles 2FA enable/disable actions.
     *
     * @param  array  $params  Request parameters
     *
     * @throws TwoFactorAuthException
     */
    public function post(array $params): Response
    {
        $userId = (int) session('userdata.id');

        if (isset($_POST['disable'])) {
            if ($this->isValidFormToken()) {
                $this->twoFAService->disable2FA($userId);
            } else {
                $this->tpl->setNotification($this->language->__('notification.form_token_incorrect'), 'error');
            }
        }

        if (isset($_POST['save'])) {
            if (isset($_POST['secret'])) {
                $this->twoFAService->saveSecret($userId, $_POST['secret']);
            }

            if (isset($_POST['secret'], $_POST['twoFACode'])) {
                if ($this->twoFAService->verifyAndEnable($userId, $_POST['secret'], $_POST['twoFACode'])) {
                    $this->tpl->setNotification($this->language->__('notification.twoFA_enabled_success'), 'success', 'twoFAenabled');
                } else {
                    $this->tpl->setNotification($this->language->__('notification.incorrect_twoFA_code'), 'error');
                }
            }
        }

        $this->assignSetupData();
        $this->generateFormTokens();

        return $this->tpl->display('twofa.edit');
    }

    /**
     * Assigns the current 2FA setup state (secret, QR data, enabled flag) to the template.
     *
     * @throws TwoFactorAuthException
     */
    private function assignSetupData(): void
    {
        $setup = $this->twoFAService->getSetupData((int) session('userdata.id'));

        $this->tpl->assign('secret', $setup['secret']);
        $this->tpl->assign('twoFAEnabled', $setup['twoFAEnabled']);

        if (! $setup['twoFAEnabled']) {
            $this->tpl->assign('qrData', $setup['qrData']);
        }
    }

    /**
     * Validates the submitted CSRF form token against the session.
     */
    private function isValidFormToken(): bool
    {
        return isset($_POST[session('formTokenName')])
            && $_POST[session('formTokenName')] == session('formTokenValue');
    }

    /**
     * Generates CSRF form tokens for sensitive forms.
     */
    private function generateFormTokens(): void
    {
        $permittedChars = '0123456789abcdefghijklmnopqrstuvwxyz';
        session(['formTokenName' => substr(str_shuffle($permittedChars), 0, 32)]);
        session(['formTokenValue' => substr(str_shuffle($permittedChars), 0, 32)]);
    }
}
