<?php

namespace Leantime\Domain\Plugins;

use Illuminate\Console\Scheduling\Schedule;
use Leantime\Core\Events\EventDispatcher;
use Leantime\Domain\Setting\Services\Setting as SettingsService;
use Leantime\Domain\Users\Services\Users as UsersService;

EventDispatcher::add_event_listener('leantime.core.console.consolekernel.schedule.cron', function ($params) {
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

                if ($pluginsService->canActivate($plugin)) {
                    return;
                }

                $pluginsService->disablePlugin($plugin->id);
            });
    })->daily();
});
