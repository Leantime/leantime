<?php

namespace Unit\app\Core\Middleware;

use Leantime\Core\Configuration\Environment;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\Language;
use Leantime\Core\Middleware\Localization;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Symfony\Component\HttpFoundation\Response;
use Unit\TestCase;

/**
 * Regression for #3877: a user without their own time format kept the English "h:i A" after the
 * language changed to French, so calendars (which read language.timeformat via the JS i18n
 * dictionary) rendered "2:00 PM" instead of "14:00".
 *
 * The middleware stored the language default in the session, and Language::__ prefers the
 * session value, so every later recompute read the old default back.
 */
class LocalizationTimeFormatTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /**
     * Builds the middleware with the given stored user settings and a French language.
     *
     * @param  array<string, string>  $storedSettings
     */
    private function runMiddleware(array $storedSettings, Language $language): void
    {
        $middleware = new Localization(
            $this->make(SettingRepository::class, [
                'getSettingsForKeys' => fn () => $storedSettings,
                'getSetting' => fn () => false,
            ]),
            $this->make(Environment::class, ['defaultTimezone' => 'UTC', 'language' => 'fr-FR']),
            $language,
        );

        $middleware->handle($this->make(IncomingRequest::class), fn () => new Response('ok'));
    }

    private function frenchLanguage(): Language
    {
        return $this->make(Language::class, [
            'ini_array' => [
                'language.dateformat' => 'd/m/Y',
                'language.timeformat' => 'H:i',
            ],
        ]);
    }

    public function test_language_default_is_not_frozen_into_the_session(): void
    {
        // Session left over from when the app ran in English.
        session()->forget('localization.cached');
        session([
            'userdata' => ['id' => 5],
            'usersettings.time_format' => 'h:i A',
            'usersettings.date_format' => 'm/d/Y',
        ]);
        $language = $this->frenchLanguage();

        $this->runMiddleware(['companysettings.language' => 'fr-FR'], $language);

        $this->assertNull(session('usersettings.time_format'), 'no user choice, so nothing is pinned in the session');
        $this->assertSame('H:i', $language->__('language.timeformat'), 'the current language default applies');
        $this->assertSame('d/m/Y', $language->__('language.dateformat'));
    }

    public function test_the_users_own_time_format_wins_over_the_language_default(): void
    {
        session()->forget('localization.cached');
        session(['userdata' => ['id' => 5]]);
        $language = $this->frenchLanguage();

        $this->runMiddleware([
            'companysettings.language' => 'fr-FR',
            'usersettings.5.time_format' => 'h:i A',
        ], $language);

        $this->assertSame('h:i A', session('usersettings.time_format'));
        $this->assertSame('h:i A', $language->__('language.timeformat'));
        $this->assertSame('d/m/Y', $language->__('language.dateformat'), 'no own date format, so the language default');
    }
}
