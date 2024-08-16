<?php

namespace Leantime\Core\Providers;

use Illuminate\Cache\MemcachedConnector;
use Illuminate\Session\SymfonySessionDecorator;
use Illuminate\Support\ServiceProvider;
use Leantime\Core\CliRequest;
use Leantime\Core\Events;
use Leantime\Core\IncomingRequest;
use Leantime\Domain\Setting\Services\Setting as SettingsService;
use Illuminate\Log;

class Logging extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

        $this->app->singleton('log', function ($app) {

            $logpath = $app['config']['log_path'];

                $app['config']['logging'] = [
                'channels' => [
                    'stack' => [
                        'driver' => 'stack',
                        'channels' => ['single'],
                        'ignore_exceptions' => false,
                    ],
                    'single' => [
                        'driver' => 'single',
                        'path' => !empty($app['config']['logPath']) ? $app['config']['logPath'] : APP_ROOT . '/logs/leantime.log',
                        'permission' => 0664,
                    ],
                    'deprecations' => [
                        'driver' => 'single',
                        'path' => APP_ROOT . '/logs/deprecation-warnings.log',
                    ],
                ],
                'default' => 'single',
            ];

            return new Log\LogManager($app);
        });
    }
}
