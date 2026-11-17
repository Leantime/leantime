<?php

namespace Leantime\Domain\Oauth;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Auth\OAuth\OAuthServiceProvider;
use Leantime\Core\Auth\OAuth\OAuthTokenStore;
use Leantime\Core\Events\EventDispatcher;

// Daily cleanup of dead OAuth tokens and codes, and of registered clients that never signed in
// (client registration is open, so unused rows would otherwise pile up).
EventDispatcher::add_event_listener('leantime.core.console.consolekernel.schedule.cron', function ($params) {

    if (get_class($scheduler = $params['schedule']) !== Schedule::class || ! OAuthServiceProvider::enabled()) {
        return;
    }

    $scheduler->call(function () {
        try {
            app()->make(OAuthTokenStore::class)->purge();
        } catch (\Throwable $e) {
            Log::error($e);
        }
    })->name('oauth:purge')->daily();
});
