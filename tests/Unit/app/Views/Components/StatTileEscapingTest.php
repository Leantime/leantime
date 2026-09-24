<?php

namespace Unit\app\Views\Components;

use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\HtmlString;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;
use Unit\TestCase;

/**
 * x-global::statTile is a shared component used across the project reports, the
 * strategy/program report decks and Resource Allocation. Its `sub` prop renders
 * a caption under the value.
 *
 * It originally rendered `sub` through {!! !!} so one caller (Resource
 * Allocation's budget tile) could emphasise an at-risk count with a <span>. That
 * left the prop unescaped for EVERY caller — a footgun that turns into stored
 * XSS the first time someone passes a project name or ticket headline through
 * it. Raised in review on #3771.
 *
 * It now escapes by default and lets a caller opt in per value with HtmlString.
 * These tests pin both halves of that contract.
 */
class StatTileEscapingTest extends TestCase
{
    /**
     * Renders through an ISOLATED Blade environment rather than the Blade facade.
     *
     * Going through the app's view factory drags in the registered view composers,
     * one of which resolves the database — and unit tests run with
     * `database.default => []`, so that surfaces as a misleading
     * "str_ends_with(): must be of type string, array given" from DatabaseManager.
     * It passed locally on a warm cache and failed on CI's cold one. The escaping
     * contract has nothing to do with app bootstrapping, so this builds the
     * minimum Blade stack the component needs and nothing else.
     */
    private function render(array $data): string
    {
        $files = new Filesystem;
        $cache = sys_get_temp_dir().'/lt-stattile-blade-'.getmypid();
        $files->ensureDirectoryExists($cache);

        $compiler = new BladeCompiler($files, $cache);
        $compiler->anonymousComponentNamespace('global::components', 'global');

        $resolver = new EngineResolver;
        $resolver->register('blade', fn () => new CompilerEngine($compiler, $files));

        $finder = new FileViewFinder($files, [APP_ROOT.'/app/Views/Templates']);
        $finder->addNamespace('global', APP_ROOT.'/app/Views/Templates');

        $factory = new Factory($resolver, $finder, new Dispatcher);

        // The component tag compiler resolves the view factory off the container.
        app()->instance(\Illuminate\Contracts\View\Factory::class, $factory);
        app()->instance('view', $factory);

        $template = $cache.'/tile.blade.php';
        $files->put($template, '<x-global::statTile :value="$value" :label="$label" :sub="$sub" />');

        return (string) $factory->file($template, $data)->render();
    }

    public function test_a_plain_string_sub_is_escaped(): void
    {
        $html = $this->render([
            'value' => 3,
            'label' => 'Milestones',
            'sub' => '<script>alert(1)</script>',
        ]);

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    /** The realistic vector: user-controlled entity names reaching a caption. */
    public function test_user_derived_text_in_sub_cannot_inject_markup(): void
    {
        $html = $this->render([
            'value' => 1,
            'label' => 'Overdue',
            'sub' => 'Project "><img src=x onerror=alert(1)>',
        ]);

        // The payload survives as escaped TEXT — that's fine and expected. What
        // matters is that no live tag is emitted, so assert on the delimiters
        // rather than on the substring, which is inert once escaped.
        $this->assertStringNotContainsString('<img', $html);
        $this->assertStringContainsString('&lt;img src=x onerror=alert(1)&gt;', $html);
    }

    /** Opt-in markup still renders, so the at-risk emphasis keeps working. */
    public function test_htmlstring_sub_is_rendered_as_markup(): void
    {
        $html = $this->render([
            'value' => '$1,000',
            'label' => 'Budget',
            'sub' => new HtmlString('of $5,000 · <span class="risk">2 at-risk</span>'),
        ]);

        $this->assertStringContainsString('<span class="risk">2 at-risk</span>', $html);
    }

    /** Arrays of sub-lines follow the same rule, per element. */
    public function test_array_subs_are_escaped_per_line_and_htmlstring_still_passes(): void
    {
        $html = $this->render([
            'value' => 0,
            'label' => 'Allocated',
            'sub' => ['<b>plain</b>', new HtmlString('<span class="risk">marked</span>')],
        ]);

        $this->assertStringContainsString('&lt;b&gt;plain&lt;/b&gt;', $html);
        $this->assertStringNotContainsString('<b>plain</b>', $html);
        $this->assertStringContainsString('<span class="risk">marked</span>', $html);
    }

    /** A "0" sub-line is real content and must not be filtered out as falsy. */
    public function test_zero_is_kept_as_a_sub_line(): void
    {
        $html = $this->render([
            'value' => 5,
            'label' => 'Hours',
            'sub' => '0',
        ]);

        $this->assertStringContainsString('lt-stat-sub', $html);
    }
}
