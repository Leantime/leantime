<?php

namespace Leantime\Core\Middleware;

use Carbon\CarbonImmutable;
use Closure;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\Language;
use Leantime\Core\Support\CarbonMacros;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Symfony\Component\HttpFoundation\Response;

class Localization
{
    public function __construct(
        private readonly SettingRepository $settingsRepo,
        private readonly Environment $config,
        private readonly Language $language,
    ) {}

    /**
     * Handle the incoming request.
     *
     * @param  IncomingRequest  $request  The incoming request object.
     * @param  Closure  $next  The closure to execute next.
     * @return Response The response object.
     *
     * @throws \ReflectionException
     */
    public function handle(IncomingRequest $request, Closure $next): Response
    {
        // Check if localization settings are already cached in the session.
        // Settings rarely change mid-session, so we only fetch from DB on first load.
        // When users change their settings, the settings save endpoint refreshes the session.
        if (session()->has('localization.cached')) {

            CarbonImmutable::mixin(new CarbonMacros(
                session('usersettings.timezone') ?: $this->config->defaultTimezone,
                str_replace('-', '_', session('usersettings.language') ?: session('companysettings.language') ?: $this->config->language),
                session('usersettings.date_format') ?: $this->language->__('language.dateformat'),
                session('usersettings.time_format') ?: $this->language->__('language.timeformat')
            ));

            return $next($request);
        }

        // First request in session: batch-fetch all localization settings at once
        $userId = session('userdata.id') ?? false;

        $settingKeys = ['companysettings.language'];
        if ($userId) {
            $settingKeys = array_merge($settingKeys, [
                "usersettings.$userId.language",
                "usersettings.$userId.timezone",
                "usersettings.$userId.date_format",
                "usersettings.$userId.time_format",
            ]);
        }

        // Single batch query instead of 5 individual queries
        $settings = $this->settingsRepo->getSettingsForKeys($settingKeys);

        $companyLanguage = $settings['companysettings.language'] ?? $this->config->language;
        session()->put('companysettings.language', $companyLanguage ?: $this->config->language);

        if (! $userId) {
            CarbonImmutable::mixin(new CarbonMacros(
                $this->config->defaultTimezone,
                str_replace('-', '_', session('companysettings.language')),
                $this->language->__('language.dateformat'),
                $this->language->__('language.timeformat')
            ));

            session()->put('localization.cached', true);

            return $next($request);
        }

        session()->put('usersettings.language', ($settings["usersettings.$userId.language"] ?? false) ?: session('companysettings.language'));
        session()->put('usersettings.timezone', ($settings["usersettings.$userId.timezone"] ?? false) ?: $this->config->defaultTimezone);

        // Only the formats the user chose go into the session. Without a choice the session holds null
        // and Language::__('language.timeformat') answers with the default of the language loaded for
        // that request. Storing the language default here instead froze it: Language::__ prefers the
        // session value, so the next recompute (e.g. after the language changed to French) read the
        // old English "h:i A" back and kept it for the rest of the session (#3877).
        $userDateFormat = ($settings["usersettings.$userId.date_format"] ?? null) ?: null;
        $userTimeFormat = ($settings["usersettings.$userId.time_format"] ?? null) ?: null;
        session()->put('usersettings.date_format', $userDateFormat);
        session()->put('usersettings.time_format', $userTimeFormat);

        // Set macros for CarbonImmutable date handling
        CarbonImmutable::mixin(new CarbonMacros(
            session('usersettings.timezone'),
            str_replace('-', '_', session('usersettings.language')),
            $this->language->__('language.dateformat'),
            $this->language->__('language.timeformat')
        ));

        session()->put('localization.cached', true);

        return $next($request);
    }
}
