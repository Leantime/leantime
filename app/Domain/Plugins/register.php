<?php

namespace Leantime\Domain\Plugins;

use Leantime\Core\Events;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Http;
use Leantime\Domain\Users\Services\Users as UsersService;

Events::add_event_listener('leantime.core.consolekernel.schedule.cron', function ($params) {
    if (get_class($params['schedule']) !== Schedule::class) {
        return;
    }

    $params['schedule']->call(function () {
        /**
         * @var Services\Plugins $pluginsService
         **/
        $pluginsService = app()->make(Services\Plugins::class);

        /**
         * @var UsersService $userService
         **/
        $userService = app()->make(UsersService::class);

        $numberOfUsers = count(collect($userService->getAll() ?: [])->filter()->all());
        $instanceId = app()->make(Services\Plugins::class)->getInstanceId();

        collect($pluginsService->getAllPlugins(true))
            ->filter(fn ($plugin) => $plugin->type === 'phar')
            ->filter(fn ($plugin) => $plugin->enabled)
            /**
             * @var Leantime\Core\Domain\Plugins\Models\Plugin $plugin
             **/
            ->each(function ($plugin) use ($pluginsService, $numberOfUsers, $instanceId) {
                $response = Http::get($pluginsService->marketplaceUrl, [
                    'wp-api' => 'software-api',
                    'request' => 'check',
                    'users' => $numberOfUsers,
                    'product_id' => $plugin->id,
                    'license_key' => $plugin->license,
                    'instance_id' => $instanceId,
                ]);

                if ($response->failed()) {
                    $pluginsService->disablePlugin($plugin->id);
                }
            });
    })->daily();
});

