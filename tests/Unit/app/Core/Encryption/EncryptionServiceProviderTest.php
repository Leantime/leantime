<?php

namespace Unit\app\Core\Encryption;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Encryption\Encrypter;
use Leantime\Core\Encryption\EncryptionServiceProvider;
use Unit\TestCase;

/**
 * The encrypter follows the session password, and keeps reading data encrypted with an earlier
 * one (LEAN_SESSION_PASSWORD_PREVIOUS) after the session password changed.
 */
class EncryptionServiceProviderTest extends TestCase
{
    private const OLD_PASSWORD = '3evBlq9zdUEuzKvVJHWWx3QzsQhturBApxwcws2m';

    private const NEW_PASSWORD = '0f1e2d3c4b5a69788796a5b4c3d2e1f0';

    /**
     * Builds the encrypter the provider registers for the given configuration.
     */
    private function encrypterFor(string $sessionPassword, string $sessionPasswordPrevious = ''): Encrypter
    {
        config()->set('sessionPassword', $sessionPassword);
        config()->set('sessionPasswordPrevious', $sessionPasswordPrevious);

        $this->app->forgetInstance('encrypter');
        (new EncryptionServiceProvider($this->app))->register();

        return $this->app->make('encrypter');
    }

    public function test_data_encrypted_with_an_earlier_session_password_stays_readable(): void
    {
        $encryptedBeforeTheChange = $this->encrypterFor(self::OLD_PASSWORD)->encryptString('caldav-password');

        $encrypter = $this->encrypterFor(self::NEW_PASSWORD, 'some-other-old-one, '.self::OLD_PASSWORD);

        $this->assertSame('caldav-password', $encrypter->decryptString($encryptedBeforeTheChange));
    }

    public function test_new_data_is_encrypted_with_the_current_session_password_only(): void
    {
        $encryptedAfterTheChange = $this->encrypterFor(self::NEW_PASSWORD, self::OLD_PASSWORD)->encryptString('caldav-password');

        $this->assertSame('caldav-password', $this->encrypterFor(self::NEW_PASSWORD)->decryptString($encryptedAfterTheChange));

        $this->expectException(DecryptException::class);
        $this->encrypterFor(self::OLD_PASSWORD)->decryptString($encryptedAfterTheChange);
    }

    public function test_without_an_earlier_session_password_old_data_cannot_be_read(): void
    {
        $encryptedBeforeTheChange = $this->encrypterFor(self::OLD_PASSWORD)->encryptString('caldav-password');

        $this->expectException(DecryptException::class);
        $this->encrypterFor(self::NEW_PASSWORD)->decryptString($encryptedBeforeTheChange);
    }

    public function test_previous_session_passwords_are_split_and_cleaned(): void
    {
        $this->assertSame([], EncryptionServiceProvider::previousSessionPasswords(''));
        $this->assertSame(['one', 'two'], EncryptionServiceProvider::previousSessionPasswords(' one, ,two ,'));
    }
}
