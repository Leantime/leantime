<?php

namespace Leantime\Domain\Plugins;

use Leantime\Core\Events;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Http;
use Leantime\Domain\Users\Services\Users as UsersService;
use Leantime\Domain\Setting\Services\Setting as SettingsService;

Events::add_event_listener('leantime.core.consolekernel.schedule.cron', function ($params) {
    if (get_class($params['schedule']) !== Schedule::class) {
        return;
    }

    $params['schedule']->call(function () {
        /**
         * @var Services\Plugins $pluginsService
         **/
        $pluginsService = app()->make(Services\Plugins::class);

        collect($pluginsService->getAllPlugins(true))
            ->filter(fn ($plugin) => $plugin->type === 'marketplace')
            ->filter(fn ($plugin) => $plugin->enabled)
            ->each(function (Models\InstalledPlugin $plugin) use ($pluginsService) {
                static $instanceId, $numberOfUsers;
                $instanceId ??= app()->make(SettingsService::class)->getCompanyId();
                $numberOfUsers ??= app()->make(UsersService::class)->getNumberOfUsers(activeOnly: true, includeApi: false);

                $response = Http::get($pluginsService->marketplaceUrl, [
                    'wp-api' => 'software-api',
                    'request' => 'check',
                    'users' => $numberOfUsers,
                    'product_id' => $plugin->id,
                    'license_key' => $plugin->license,
                    'instance' => $instanceId,
                    'user_count' => $numberOfUsers,
                ]);

                if (! $response->ok()) {
                    $pluginsService->disablePlugin($plugin->id);
                }
            });
    })->daily();
});

