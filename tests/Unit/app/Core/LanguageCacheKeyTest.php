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

    public function test_cache_key_for_builds_the_same_key_for_a_given_language(): void
    {
        $this->assertSame('languages.lang_en-US_'.app(AppSettings::class)->appVersion, Language::cacheKeyFor('en-US'));
    }

    public function test_forget_cached_language_clears_versioned_and_legacy_keys(): void
    {
        $store = new \Illuminate\Cache\Repository(new \Illuminate\Cache\ArrayStore);
        $store->put('languages.lang_de-DE', ['old' => 'legacy']);
        $store->put(Language::cacheKeyFor('de-DE'), ['new' => 'current']);
        $store->put(Language::cacheKeyFor('en-US'), ['other' => 'language']);

        \Illuminate\Support\Facades\Cache::shouldReceive('store')->with('installation')->andReturn($store);

        $this->assertTrue(Language::forgetCachedLanguage('de-DE'));

        $this->assertFalse($store->has('languages.lang_de-DE'));
        $this->assertFalse($store->has(Language::cacheKeyFor('de-DE')));
        $this->assertTrue($store->has(Language::cacheKeyFor('en-US')));
    }
}
