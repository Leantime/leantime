<?php

namespace Leantime\Core\Middleware;

use Carbon\CarbonImmutable;
use Closure;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\Language;
use Leantime\Core\Support\CarbonMacros;
use Leantime\Domain\Setting\Services\Setting;
use Symfony\Component\HttpFoundation\Response;

class Localization
{
    public function __construct(
        private readonly Setting     $settings,
        private readonly Environment $config,
        private readonly Language    $language,
    ) {
    }

    /**
     * Handle the incoming request.
     *
     * @param IncomingRequest $request The incoming request object.
     * @param Closure $next The closure to execute next.
     *
     * @return Response The response object.
     *
     * @throws \ReflectionException
     */
    public function handle(IncomingRequest $request, Closure $next): Response
    {
        session()->put("companysettings.language", $this->settings->getSetting("companysettings.language") ?: $this->config->language);

        if (! $userId = session("userdata.id") ?? false) {

            CarbonImmutable::mixin(new CarbonMacros(
                $this->config->defaultTimezone,
                str_replace("-", "_", session("companysettings.language")),
                $this->language->__("language.dateformat"),
                $this->language->__("language.timeformat")
            ));

            return $next($request);
        }

        session()->put("usersettings.language", $this->settings->getSetting("usersettings.$userId.language") ?: session("companysettings.language"));
        session()->put("usersettings.timezone", $this->settings->getSetting("usersettings.$userId.timezone") ?: $this->config->defaultTimezone);
        date_default_timezone_set(session("usersettings.timezone"));

        session()->put("usersettings.date_format", $this->settings->getSetting("usersettings.$userId.date_format") ?: $this->language->__("language.dateformat"));
        session()->put("usersettings.time_format", $this->settings->getSetting("usersettings.$userId.time_format") ?: $this->language->__("language.timeformat"));

        // Set macros for CabonImmutable date handling
        CarbonImmutable::mixin(new CarbonMacros(
            session("usersettings.timezone"),
            str_replace("-", "_", session("usersettings.language")),
            session("usersettings.date_format"),
            session("usersettings.time_format")
        ));

        return $next($request);
    }
}
