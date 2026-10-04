<?php

namespace Unit\app\Core;

use Leantime\Core\Configuration\AppSettings;
use Leantime\Core\Language;
use Unit\TestCase;

/**
 * The merged language strings are cached in the installation store, which only database updates
 * flush. The key must change with every release, otherwise strings added by a release without a
 * migration render as raw keys until the cache is cleared by hand.
 */
class LanguageCacheKeyTest extends TestCase
{
    public function test_cache_key_contains_language_and_app_version(): void
    {
        $language = (new \ReflectionClass(Language::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty(Language::class, 'language'))->setValue($language, 'de-DE');

        $key = (new \ReflectionMethod(Language::class, 'languageCacheKey'))->invoke($language);

        $this->assertSame('languages.lang_de-DE_'.app(AppSettings::class)->appVersion, $key);
    }
}
