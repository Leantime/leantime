<?php

namespace Unit\app\Core;

use Leantime\Core\Configuration\Environment;
use Leantime\Core\Language;

/**
 * The selected language becomes part of the language file path, so only codes from the list
 * of available languages may be selected — a tampered cookie or setting must fall back.
 */
class LanguageSelectionTest extends \Unit\TestCase
{
    use \Codeception\Test\Feature\Stub;

    private array $cookieBackup;

    private array $serverBackup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cookieBackup = $_COOKIE;
        $this->serverBackup = $_SERVER;
        session()->forget(['usersettings.language', 'companysettings.language']);
        unset($_SERVER['HTTP_ACCEPT_LANGUAGE']);
    }

    protected function tearDown(): void
    {
        $_COOKIE = $this->cookieBackup;
        $_SERVER = $this->serverBackup;

        parent::tearDown();
    }

    private function language(): Language
    {
        $language = (new \ReflectionClass(Language::class))->newInstanceWithoutConstructor();
        $language->langlist = ['en-US' => 'English', 'de-DE' => 'Deutsch'];
        $language->config = $this->make(Environment::class, [
            'get' => fn ($key, $default = null) => $key === 'language' ? 'en-US' : $default,
        ]);

        return $language;
    }

    public function test_valid_cookie_language_is_used(): void
    {
        $_COOKIE['language'] = 'de-DE';

        $this->assertSame('de-DE', $this->language()->getCurrentLanguage());
    }

    public function test_path_traversal_cookie_is_rejected(): void
    {
        $_COOKIE['language'] = '../../../../etc/passwd';

        $this->assertSame('en-US', $this->language()->getCurrentLanguage());
    }

    public function test_unknown_cookie_falls_through_to_company_language(): void
    {
        $_COOKIE['language'] = 'xx-XX';
        session(['companysettings.language' => 'de-DE']);

        $this->assertSame('de-DE', $this->language()->getCurrentLanguage());
    }

    public function test_invalid_session_language_is_ignored(): void
    {
        session(['usersettings.language' => '../custom/evil']);

        $this->assertSame('en-US', $this->language()->getCurrentLanguage());
    }

    public function test_non_string_cookie_is_ignored(): void
    {
        $_COOKIE['language'] = ['de-DE'];

        $this->assertSame('en-US', $this->language()->getCurrentLanguage());
    }
}
