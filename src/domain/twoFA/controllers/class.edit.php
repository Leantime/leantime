<?php

namespace leantime\domain\controllers {

    use Endroid\QrCode\QrCode;
    use leantime\domain\repositories;
    use leantime\core;
    use leantime\core\controller;
    use RobThree\Auth\Providers\Qr\IQRCodeProvider;
    use RobThree\Auth\TwoFactorAuth;

    class edit extends controller
    {

        private $userRepo;

        public function init()
        {

            $this->userRepo = new repositories\users();

        }

        public function run()
        {

            $userId = $_SESSION['userdata']['id'];

            $user = $this->userRepo->getUser($userId);

            $mp = new TwoFAQRCode();
            $tfa = new TwoFactorAuth('Leantime',6, 30, 'sha1', $mp);
            $secret = $user['twoFASecret'];

            if (isset($_POST['disable'])) {
                if(isset($_POST[$_SESSION['formTokenName']]) && $_POST[$_SESSION['formTokenName']] == $_SESSION['formTokenValue']) {

                    $this->userRepo->patchUser($userId, [
                        "twoFAEnabled" => 0,
                        "twoFASecret" => null
                    ]);

                    $user['twoFASecret'] = null;
                    $user['twoFAEnabled'] = 0;
                    $secret = null;

                    $this->tpl->assign("twoFAEnabled", false);

                }else{
                    $this->tpl->setNotification($this->language->__("notification.form_token_incorrect"), 'error');
                }
            }

            if (empty($secret)) {
                $secret = $tfa->createSecret(160);
            }

            $this->tpl->assign("secret", $secret);

            if (isset($_POST['save'])) {
                if (isset($_POST['secret'])) {
                    $secret = $_POST['secret'];
                    $this->userRepo->patchUser($userId, [
                        "twoFASecret" => $secret
                    ]);

                    $user['twoFASecret'] = $secret;
                    $this->tpl->assign("secret", $secret);
                }

                if (isset($_POST['twoFACode']) && isset($secret)) {
                    $verified = $tfa->verifyCode($secret, $_POST['twoFACode']);
                    if ($verified) {
                        $this->userRepo->patchUser($userId, [
                            "twoFAEnabled" => 1,
                            "twoFASecret" => $secret
                        ]);
                        $user['twoFAEnabled'] = 1;
                        $this->tpl->setNotification($this->language->__("notification.twoFA_enabled_success"), 'success');
                        $this->tpl->assign("twoFAEnabled", true);
                    } else {
                        $this->tpl->setNotification($this->language->__("notification.incorrect_twoFA_code"), 'error');
                    }
                }
            }

            if ($user['twoFAEnabled']) {
                $this->tpl->assign("twoFAEnabled", true);
            } else {
                $qrData = $tfa->getQRCodeImageAsDataUri($user['username'], $secret);
                $this->tpl->assign("qrData", $qrData);
                $this->tpl->assign("twoFAEnabled", false);
            }

            //Sensitive Form, generate form tokens
            $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
            $_SESSION['formTokenName'] = substr(str_shuffle($permitted_chars), 0, 32);
            $_SESSION['formTokenValue'] = substr(str_shuffle($permitted_chars), 0, 32);

            $this->tpl->display('twoFA.edit');
        }
    }

    // TODO: lets find a place for this
    class TwoFAQRCode implements IQRCodeProvider {
        public function getMimeType() {
            return 'image/png';
        }

        public function getQRCodeImage($qrtext, $size) {
            $qrCode = new QrCode($qrtext);
            $qrCode->setSize($size);
            return $qrCode->writeString();
        }
    }
}

