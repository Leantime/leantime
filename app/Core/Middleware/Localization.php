<?php

namespace Leantime\Core\Middleware;

use Closure;
use Leantime\Core\Environment;
use Leantime\Core\IncomingRequest;
use Leantime\Core\Language;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Setting\Services\Setting;
use Symfony\Component\HttpFoundation\Response;

class Localization
{
    /**
     * Handle the incoming request.
     *
     * @param IncomingRequest $request The incoming request object.
     * @param Closure         $next    The closure to execute next.
     * @return Response The response object.
     */
    public function handle(IncomingRequest $request, Closure $next): Response
    {

        $settings = app()->make(Setting::class);
        $config = app()->make(Environment::class);

        $userId = isset($_SESSION['userdata']) && isset($_SESSION['userdata']['id']) ? $_SESSION['userdata']['id'] : false;

        if (!isset($_SESSION["companysettings.language"])) {
            $language = $settings->getSetting("companysettings.language");
            $_SESSION["companysettings.language"] = $language === false ? $config->language : $language;
        }

        if (!isset($_SESSION['usersettings.language']) && $userId !== false) {
            $language = $settings->getSetting("usersettings." . $userId . ".language");
            $_SESSION["usersettings.language"] = $language === false ? $_SESSION["companysettings.language"] : $language;
        }

        $timezone = $config->defaultTimezone;
        if (!isset($_SESSION['usersettings.timezone']) && $userId !== false) {
            $timezone = $settings->getSetting("usersettings." . $userId . ".timezone");
            $_SESSION['usersettings.timezone'] = $timezone === false ? $config->defaultTimezone : $timezone;
        }

        date_default_timezone_set($timezone);

        if (
            (!isset($_SESSION['usersettings.language.date_format'])
            || !isset($_SESSION['usersettings.language.time_format'])) && $userId !== false
        ) {
            $language = app()->make(Language::class);

            //Language manager will get the default if user setting is not set
            $dateformatDefault = $language->__("language.dateformat");
            $timeformatDefault = $language->__("language.timeformat");

            $userDateformat = $settings->getSetting("usersettings." . $userId . ".date_format");
            $userTimeformat = $settings->getSetting("usersettings." . $userId . ".time_format");

            $_SESSION['usersettings.language.date_format'] = $userDateformat === false ? $dateformatDefault : $userDateformat;
            $_SESSION['usersettings.language.time_format'] = $userTimeformat === false ? $timeformatDefault : $userTimeformat;
        }

        return $next($request);
    }
}
