<?php

namespace leantime\domain\controllers {

    use Endroid\QrCode\QrCode;
    use leantime\domain\repositories;
    use leantime\core;
    use RobThree\Auth\Providers\Qr\IQRCodeProvider;
    use RobThree\Auth\TwoFactorAuth;

    class edit
    {
        public function run()
        {
            $tpl = new core\template();

            $userId = $_SESSION['userdata']['id'];
            $userRepo = new repositories\users();
            $language = new core\language();

            $user = $userRepo->getUser($userId);

            $mp = new TwoFAQRCode();
            $tfa = new TwoFactorAuth('Leantime',6, 30, 'sha1', $mp);
            $secret = $user['twoFASecret'];

            if (isset($_POST['disable'])) {
                $userRepo->patchUser($userId, [
                    "twoFAEnabled" => 0,
                    "twoFASecret" => null
                ]);

                $user['twoFASecret'] = null;
                $user['twoFAEnabled'] = 0;
                $secret = null;

                $tpl->assign("twoFAEnabled", false);
            }

            if (empty($secret)) {
                $secret = $tfa->createSecret(160);
            }

            $tpl->assign("secret", $secret);

            if (isset($_POST['save'])) {
                if (isset($_POST['secret'])) {
                    $secret = $_POST['secret'];
                    $userRepo->patchUser($userId, [
                        "twoFASecret" => $secret
                    ]);

                    $user['twoFASecret'] = $secret;
                    $tpl->assign("secret", $secret);
                }

                if (isset($_POST['twoFACode']) && isset($secret)) {
                    $verified = $tfa->verifyCode($secret, $_POST['twoFACode']);
                    if ($verified) {
                        $userRepo->patchUser($userId, [
                            "twoFAEnabled" => 1,
                            "twoFASecret" => $secret
                        ]);
                        $user['twoFAEnabled'] = 1;
                        $tpl->setNotification($language->__("notification.twoFA_enabled_success"), 'success');
                        $tpl->assign("twoFAEnabled", true);
                    } else {
                        $tpl->setNotification($language->__("notification.incorrect_twoFA_code"), 'error');
                    }
                }
            }

            if ($user['twoFAEnabled']) {
                $tpl->assign("twoFAEnabled", true);
            } else {
                $qrData = $tfa->getQRCodeImageAsDataUri($user['username'], $secret);
                $tpl->assign("qrData", $qrData);
                $tpl->assign("twoFAEnabled", false);
            }

            $tpl->display('twoFA.edit');
        }
    }

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

