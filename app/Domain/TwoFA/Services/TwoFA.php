<?php

namespace Leantime\Domain\TwoFA\Services;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use RobThree\Auth\Providers\Qr\IQRCodeProvider;
use RobThree\Auth\TwoFactorAuth;
use RobThree\Auth\TwoFactorAuthException;

/**
 * Two-factor authentication domain service.
 *
 * Owns TOTP secret generation, QR-code construction, code verification, and
 * persistence of the user's 2FA state so controllers stay thin.
 *
 * @api
 */
class TwoFA
{
    public function __construct(
        private UserRepository $userRepo
    ) {}

    /**
     * Builds the data needed to render the 2FA setup page for a user.
     *
     * Generates a fresh TOTP secret (and matching QR code) when the user does
     * not already have one. The QR code is omitted when 2FA is already enabled.
     *
     * @param  int  $userId  The user to build setup data for
     * @return array{secret: string, qrData: string|null, twoFAEnabled: bool}
     *
     * @throws TwoFactorAuthException
     *
     * @api
     */
    public function getSetupData(int $userId): array
    {
        $user = $this->userRepo->getUser($userId);
        $tfa = $this->createTwoFactorAuth();

        $secret = $user['twoFASecret'] ?? '';
        if (empty($secret)) {
            $secret = $tfa->createSecret(160);
        }

        $enabled = (bool) ($user['twoFAEnabled'] ?? false);

        return [
            'secret' => $secret,
            'qrData' => $enabled ? null : $tfa->getQRCodeImageAsDataUri($user['username'], $secret),
            'twoFAEnabled' => $enabled,
        ];
    }

    /**
     * Persists a TOTP secret for the user without enabling 2FA.
     *
     * Stored ahead of verification so a failed code entry does not discard the
     * secret the user is mid-way through enrolling.
     *
     * @param  int  $userId  The user to store the secret for
     * @param  string  $secret  The TOTP secret
     *
     * @api
     */
    public function saveSecret(int $userId, string $secret): void
    {
        $this->userRepo->patchUser($userId, ['twoFASecret' => $secret]);
    }

    /**
     * Verifies a submitted TOTP code against the secret and, when valid, enables 2FA.
     *
     * @param  int  $userId  The user enabling 2FA
     * @param  string  $secret  The TOTP secret
     * @param  string  $code  The code submitted by the user
     * @return bool True when the code verified and 2FA was enabled
     *
     * @throws TwoFactorAuthException
     *
     * @api
     */
    public function verifyAndEnable(int $userId, string $secret, string $code): bool
    {
        $verified = $this->createTwoFactorAuth()->verifyCode($secret, $code);

        if (! $verified) {
            return false;
        }

        $this->userRepo->patchUser($userId, [
            'twoFAEnabled' => 1,
            'twoFASecret' => $secret,
        ]);

        return true;
    }

    /**
     * Disables 2FA for the user and clears their stored secret.
     *
     * @param  int  $userId  The user to disable 2FA for
     *
     * @api
     */
    public function disable2FA(int $userId): void
    {
        $this->userRepo->patchUser($userId, [
            'twoFAEnabled' => 0,
            'twoFASecret' => null,
        ]);
    }

    /**
     * Creates a TwoFactorAuth instance backed by a PNG QR-code provider.
     *
     * @throws TwoFactorAuthException
     */
    private function createTwoFactorAuth(): TwoFactorAuth
    {
        return new TwoFactorAuth('Leantime', 6, 30, 'sha1', new class implements IQRCodeProvider
        {
            public function getMimeType(): string
            {
                return 'image/png';
            }

            public function getQRCodeImage($qrtext, $size): string
            {
                $writer = new PngWriter;

                $qrCode = new QrCode(data: $qrtext, size: $size, backgroundColor: new Color(255, 255, 255, 127));

                $label = new Label(
                    text: 'Label',
                    textColor: new Color(255, 0, 0)
                );

                return $writer->write($qrCode, null, null)->getString();
            }
        });
    }
}
