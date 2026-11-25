<?php

namespace Leantime\Core\Encryption;

class EncryptionServiceProvider extends \Illuminate\Encryption\EncryptionServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerEncrypter();
        $this->registerSerializableClosureSecurityKey();
    }

    /**
     * Register the encrypter.
     *
     * @return void
     */
    protected function registerEncrypter()
    {

        $this->app->singleton('encrypter', function ($app) {

            $configKey = $this->toEncryptionKey((string) $app['config']->sessionPassword);

            $app['config']['app_key'] = $configKey;
            $app['config']['key'] = $configKey;

            $encrypter = new \Illuminate\Encryption\Encrypter($configKey, 'AES-256-CBC');

            // Data encrypted before the session password changed stays readable.
            $previousKeys = array_map(
                fn (string $previousPassword): string => $this->toEncryptionKey($previousPassword),
                self::previousSessionPasswords((string) $app['config']->sessionPasswordPrevious)
            );

            return $encrypter->previousKeys($previousKeys);
        });
    }

    /**
     * Fits a session password to the 32 bytes AES-256 needs: longer ones are cut, shorter ones padded.
     *
     * @param  string  $sessionPassword  The configured session password.
     * @return string The 32 byte key.
     */
    private function toEncryptionKey(string $sessionPassword): string
    {
        if (strlen($sessionPassword) > 32) {
            return substr($sessionPassword, 0, 32);
        }

        return str_pad($sessionPassword, 32, 'x', STR_PAD_BOTH);
    }

    /**
     * Splits the comma separated LEAN_SESSION_PASSWORD_PREVIOUS value into its passwords.
     *
     * @param  string  $configuredValue  The configured value, may be empty.
     * @return array<int, string> The earlier session passwords, without empty entries.
     */
    public static function previousSessionPasswords(string $configuredValue): array
    {
        $previousPasswords = array_map('trim', explode(',', $configuredValue));

        return array_values(array_filter($previousPasswords, fn (string $password): bool => $password !== ''));
    }
}
