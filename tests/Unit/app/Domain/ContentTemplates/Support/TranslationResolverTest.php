<?php

namespace Unit\app\Domain\ContentTemplates\Support;

use Leantime\Core\Language;
use Leantime\Domain\ContentTemplates\Support\TranslationResolver;
use Unit\TestCase;

/**
 * Unit tests for TranslationResolver — the small helper that lets YAML
 * content templates carry t:KEY translation references through to
 * consumers without each consumer learning the convention.
 */
class TranslationResolverTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    protected function setUp(): void
    {
        parent::setUp();

        // __() routes through the bound Language. Stub it to a deterministic
        // prefix so the test doesn't depend on real locale files being present.
        $this->app->instance(Language::class, $this->make(Language::class, [
            '__' => fn (string $key): string => 'T:'.$key,
        ]));
    }

    public function test_passes_through_strings_without_t_references_untouched(): void
    {
        $this->assertSame('plain string', TranslationResolver::resolve('plain string'));
        $this->assertSame('', TranslationResolver::resolve(''));
        $this->assertSame('<h1>Hello</h1>', TranslationResolver::resolve('<h1>Hello</h1>'));
    }

    public function test_whole_string_t_prefix_resolves_via_translator(): void
    {
        $this->assertSame('T:templates.prd.title', TranslationResolver::resolve('t:templates.prd.title'));
        $this->assertSame('T:status.draft', TranslationResolver::resolve('t:status.draft'));
    }

    public function test_substring_t_substitution_replaces_each_occurrence_in_place(): void
    {
        $resolved = TranslationResolver::resolve('<h1>{{ t:templates.prd.title }}</h1>');
        $this->assertSame('<h1>T:templates.prd.title</h1>', $resolved);

        // Multiple substitutions in one string, with various whitespace inside braces.
        $resolved = TranslationResolver::resolve('{{t:templates.author}} Gloria — {{ t:templates.dates }} 2026');
        $this->assertSame('T:templates.author Gloria — T:templates.dates 2026', $resolved);
    }

    public function test_resolve_array_walks_recursively_and_resolves_strings_only(): void
    {
        $resolved = TranslationResolver::resolveArray([
            'title' => 't:templates.prd.title',
            'description' => '{{ t:templates.prd.description }} (extra)',
            'nested' => [
                'content' => '<p>{{ t:templates.author }}</p>',
                'count' => 7,  // non-strings pass through
                'flag' => true,
            ],
            'plain' => 'no references here',
        ]);

        $this->assertSame([
            'title' => 'T:templates.prd.title',
            'description' => 'T:templates.prd.description (extra)',
            'nested' => [
                'content' => '<p>T:templates.author</p>',
                'count' => 7,
                'flag' => true,
            ],
            'plain' => 'no references here',
        ], $resolved);
    }
}
