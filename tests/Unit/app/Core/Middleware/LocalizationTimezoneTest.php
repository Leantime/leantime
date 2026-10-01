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
 * The PHP process timezone stays UTC: every DB datetime is UTC, so date()/now() must agree.
 * The middleware used to switch it to the user's timezone on every request, which made every
 * date()/now() write store local time in UTC columns. The user's timezone lives in the session
 * and is applied explicitly by DateTimeHelper/format().
 */
class LocalizationTimezoneTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    private string $originalTimezone;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalTimezone = date_default_timezone_get();
        date_default_timezone_set('UTC');
    }

    protected function tearDown(): void
    {
        date_default_timezone_set($this->originalTimezone);
        parent::tearDown();
    }

    private function middleware(): Localization
    {
        return new Localization(
            $this->make(SettingRepository::class, [
                'getSettingsForKeys' => fn () => ['usersettings.5.timezone' => 'America/Chicago'],
                'getSetting' => fn () => false,
            ]),
            $this->make(Environment::class, ['defaultTimezone' => 'America/Los_Angeles', 'language' => 'en-US']),
            $this->make(Language::class, ['__' => fn ($key) => $key]),
        );
    }

    public function test_loading_user_settings_keeps_the_process_in_utc(): void
    {
        session()->forget('localization.cached');
        session(['userdata' => ['id' => 5]]);

        $this->middleware()->handle($this->make(IncomingRequest::class), fn () => new Response('ok'));

        $this->assertSame('America/Chicago', session('usersettings.timezone'), 'the user timezone is still loaded into the session');
        $this->assertSame('UTC', date_default_timezone_get(), 'but the process timezone is not switched');
    }

    public function test_cached_settings_path_keeps_the_process_in_utc(): void
    {
        session(['localization.cached' => true, 'usersettings.timezone' => 'Asia/Tokyo']);

        $this->middleware()->handle($this->make(IncomingRequest::class), fn () => new Response('ok'));

        $this->assertSame('UTC', date_default_timezone_get());
    }
}
