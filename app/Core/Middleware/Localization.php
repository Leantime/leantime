<?php

namespace Leantime\Core\Middleware;

use Carbon\CarbonImmutable;
use Closure;
use Leantime\Core\Environment;
use Leantime\Core\IncomingRequest;
use Leantime\Core\Language;
use Leantime\Core\Support\CarbonMacros;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Setting\Services\Setting;
use Symfony\Component\HttpFoundation\Response;

class Localization
{

    public function __construct(
        private Setting $settings,
        private Environment $config,
        private Language $language,
    ) {
        //
    }

    /**
     * Handle the incoming request.
     *
     * @param IncomingRequest $request The incoming request object.
     * @param Closure         $next    The closure to execute next.
     * @return Response The response object.
     */
    public function handle(IncomingRequest $request, Closure $next): Response
    {

        $_SESSION['companysettings.language'] ??= $this->settings->getSetting("companysettings.language") ?: $this->config->language;

        if (! $userId = $_SESSION['userdata']['id'] ?? false) {
            return $next($request);
        }

        $_SESSION['usersettings.language'] ??= $this->settings->getSetting("usersettings.$userId.language") ?: $_SESSION["companysettings.language"];
        $_SESSION['usersettings.timezone'] ??= $this->settings->getSetting("usersettings.$userId.timezone") ?: $this->config->defaultTimezone;
        date_default_timezone_set($_SESSION['usersettings.timezone']);

        $_SESSION['usersettings.language.date_format'] ??= $this->settings->getSetting("usersettings.$userId.date_format") ?: $this->language->__("language.dateformat");
        $_SESSION['usersettings.language.time_format'] ??= $this->settings->getSetting("usersettings.$userId.time_format") ?: $this->language->__("language.timeformat");

        //Set macros for CabonImmutable date handling
        CarbonImmutable::mixin(new CarbonMacros(
            $_SESSION['usersettings.timezone'],
            $_SESSION['usersettings.language'],
            $_SESSION['usersettings.language.date_format'],
            $_SESSION['usersettings.language.time_format']
        ));


        return $next($request);
    }
}
