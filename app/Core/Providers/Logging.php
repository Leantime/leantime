<?php

namespace Leantime\Core\Providers;

use Illuminate\Log;
use Illuminate\Support\ServiceProvider;

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

            if(!file_exists(APP_ROOT . '/logs/leantime.log')) {
                touch(APP_ROOT . '/logs/leantime.log');
            }

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
