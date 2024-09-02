<?php

namespace Leantime\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\SerializableClosure\SerializableClosure;

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

            $configKey =  $app['config']->sessionPassword;

            if (strlen($configKey) > 32) {
                $configKey = substr($configKey, 0, 32);
            }

            if (strlen($configKey) < 32) {
                $configKey =  str_pad($configKey, 32, "x", STR_PAD_BOTH);
            }

            $app['config']['app_key'] = $configKey;
            $app['config']['key'] = $configKey;

            return new \Illuminate\Encryption\Encrypter($configKey, "AES-256-CBC");
        });
    }
}
