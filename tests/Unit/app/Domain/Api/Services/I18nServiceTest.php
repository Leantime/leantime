<?php

namespace Unit\app\Domain\Api\Services;

use Leantime\Core\Language;
use Leantime\Domain\Api\Services\I18n as I18nService;
use Unit\TestCase;

/**
 * Unit tests for the I18n service that assembles the JavaScript i18n
 * dictionary payload extracted from the I18n controller.
 */
class I18nServiceTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    public function test_build_js_dictionary_embeds_dictionary_and_date_overrides(): void
    {
        $language = $this->make(Language::class, [
            'ini_array' => [
                'some.key' => 'Some value',
                'language.dateformat' => 'IGNORED',
                'language.timeformat' => 'IGNORED',
            ],
            '__' => fn (string $index) => $index === 'language.dateformat' ? 'm/d/Y' : 'H:i',
        ]);

        $payload = (new I18nService($language))->buildJsDictionary();

        // The JS wrapper is present.
        $this->assertStringContainsString('leantime', $payload);
        $this->assertStringContainsString('i18n', $payload);
        $this->assertStringContainsString('dictionary:', $payload);

        // Extract the JSON dictionary and assert the overrides won.
        preg_match('/dictionary: (\{.*\}),/', $payload, $matches);
        $this->assertNotEmpty($matches, 'Could not find dictionary JSON in payload');

        $decoded = json_decode($matches[1], true);
        $this->assertSame('Some value', $decoded['some.key']);
        $this->assertSame('m/d/Y', $decoded['language.dateformat']);
        $this->assertSame('H:i', $decoded['language.timeformat']);
        $this->assertArrayHasKey('usersettings.timezone', $decoded);
    }
}
