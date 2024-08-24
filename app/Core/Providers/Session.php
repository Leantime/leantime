<?php

namespace Leantime\Core\Providers;

use Illuminate\Session\SymfonySessionDecorator;
use Illuminate\Support\ServiceProvider;

class Session extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(\Illuminate\Encryption\Encrypter::class, function () {

            $configKey =  app('config')->sessionPassword;

            if (strlen($configKey) > 32) {
                $configKey = substr($configKey, 0, 32);
            }

            if (strlen($configKey) < 32) {
                $configKey =  str_pad($configKey, 32, "x", STR_PAD_BOTH);
            }

            app('config')['app_key'] = $configKey;

            $encrypter = new \Illuminate\Encryption\Encrypter(app('config')['app_key'], "AES-256-CBC");
            return $encrypter;
        });

        $this->app->singleton(\Illuminate\Session\SessionManager::class, function () {


            app('config')['session'] = array(
                'driver' => !empty( app('config')->useRedis) && (bool) app('config')->useRedis === true ? 'redis' : 'file',
                'lifetime' =>  app('config')->sessionExpiration,
                'connection' => !empty(app('config')->useRedis) && (bool) app('config')->useRedis === true ? 'session' : null,
                'expire_on_close' => false,
                'encrypt' => true,
                'files' => APP_ROOT . '/cache/sessions',
                'store' => "installation",
                'block_store' => 'installation',
                'block_lock_seconds' => 10,
                'block_wait_seconds' => 10,
                'lottery' => [2, 100],
                'cookie' => "ltid",
                'path' => "/",
                'domain' => is_array(parse_url(BASE_URL)) ? parse_url(BASE_URL)['host'] : null,
                'secure' => true,
                'http_only' => true,
                'same_site' => "Lax",
            );

            $sessionManager = new \Illuminate\Session\SessionManager(app());

            return $sessionManager;
        });

        $this->app->singleton('session.store', fn() =>  app('session')->driver());
        $this->app->singleton(SymfonySessionDecorator::class, SymfonySessionDecorator::class);
        $this->app->alias(\Illuminate\Session\SessionManager::class, 'session');

    }


}
