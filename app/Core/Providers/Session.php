<?php

namespace Leantime\Core\Providers;

use Illuminate\Cache\MemcachedConnector;
use Illuminate\Session\SymfonySessionDecorator;
use Illuminate\Support\ServiceProvider;
use Leantime\Core\CliRequest;
use Leantime\Core\Events;
use Leantime\Core\IncomingRequest;
use Leantime\Domain\Setting\Services\Setting as SettingsService;

class Session extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(\Illuminate\Encryption\Encrypter::class, function ($app) {
            $configKey = $app['config']->sessionPassword;

            if (strlen($configKey) > 32) {
                $configKey = substr($configKey, 0, 32);
            }

            if (strlen($configKey) < 32) {
                $configKey =  str_pad($configKey, 32, "x", STR_PAD_BOTH);
            }

            $app['config']['app_key'] = $configKey;

            $encrypter = new \Illuminate\Encryption\Encrypter($app['config']['app_key'], "AES-256-CBC");
            return $encrypter;
        });

        $this->app->singleton(\Illuminate\Session\SessionManager::class, function ($app) {

            $app['config']['session'] = array(
                'driver' => "file",
                'lifetime' =>  $app['config']->sessionExpiration,
                'expire_on_close' => false,
                'encrypt' => false,
                'files' => APP_ROOT . '/cache/sessions',
                'store' => "instance",
                'block_store' => 'instance',
                'block_lock_seconds' => 10,
                'block_wait_seconds' => 10,
                'lottery' => [2, 100],
                'cookie' => "ltid",
                'path' => "/",
                'domain' => is_array(parse_url(BASE_URL)) ? parse_url(BASE_URL)['host'] : null,
                'secure' => true,
                'http_only' => true,
                'same_site' => "Strict",
            );

            $sessionManager = new \Illuminate\Session\SessionManager($app);

            return $sessionManager;
        });

        $this->app->singleton('session.store', fn($app) => $app['session']->driver());
        $this->app->singleton(SymfonySessionDecorator::class, SymfonySessionDecorator::class);
        $this->app->alias(\Illuminate\Session\SessionManager::class, 'session');

    }


}
