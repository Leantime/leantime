<?php

namespace Unit\app\Views\Components;

use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;
use Unit\TestCase;

/**
 * elements.accordion replaced the hand-written accordionTitle / simpleAccordionContainer blocks;
 * the to-do group views wrap it in tickets::group-section. Pins the title colour (which lands in a
 * style attribute, so only plain colour values may pass), the subtitle, and the group rules: the
 * ungrouped "all" group has no section, others remember their state per group-by field.
 */
class AccordionTest extends TestCase
{
    /** Isolated Blade stack (see StatTileEscapingTest): the app's view factory needs a database. */
    private function render(string $template, array $data = []): string
    {
        $files = new Filesystem;
        $cache = sys_get_temp_dir().'/lt-accordion-blade-'.getmypid();
        $files->ensureDirectoryExists($cache);

        $compiler = new BladeCompiler($files, $cache);
        $compiler->anonymousComponentNamespace('global::components', 'global');
        $compiler->anonymousComponentNamespace('tickets::components', 'tickets');

        $resolver = new EngineResolver;
        $resolver->register('blade', fn () => new CompilerEngine($compiler, $files));

        $finder = new FileViewFinder($files, [APP_ROOT.'/app/Views/Templates']);
        $finder->addNamespace('global', APP_ROOT.'/app/Views/Templates');
        $finder->addNamespace('tickets', APP_ROOT.'/app/Domain/Tickets/Templates');

        $factory = new Factory($resolver, $finder, new Dispatcher);
        app()->instance(\Illuminate\Contracts\View\Factory::class, $factory);
        app()->instance('view', $factory);

        $file = $cache.'/snippet-'.md5($template).'.blade.php';
        $files->put($file, $template);

        return (string) $factory->file($file, $data)->render();
    }

    public function test_a_hex_or_variable_colour_tints_the_title(): void
    {
        $html = $this->render('<x-global::elements.accordion title="Launch" color="#1b75bb">Body</x-global::elements.accordion>');
        $this->assertStringContainsString('style="--lt-accordion-title-color: #1b75bb"', $html);

        $html = $this->render('<x-global::elements.accordion title="Done" color="var(--green)">Body</x-global::elements.accordion>');
        $this->assertStringContainsString('--lt-accordion-title-color: var(--green)', $html);
    }

    public function test_a_colour_that_could_add_css_declarations_is_dropped(): void
    {
        $html = $this->render('<x-global::elements.accordion title="Launch" :color="$color">Body</x-global::elements.accordion>', [
            'color' => 'red; background: url(https://example.com/x)',
        ]);

        $this->assertStringNotContainsString('--lt-accordion-title-color', $html);
        $this->assertStringNotContainsString('example.com', $html);
    }

    public function test_the_subtitle_renders_escaped_under_the_title(): void
    {
        $html = $this->render('<x-global::elements.accordion title="Launch" :subtitle="$subtitle">Body</x-global::elements.accordion>', [
            'subtitle' => 'Start: Oct 1 • <b>End</b>',
        ]);

        $this->assertStringContainsString('<span class="lt-accordion-heading">', $html);
        $this->assertStringContainsString('<span class="lt-accordion-subtitle">Start: Oct 1 • &lt;b&gt;End&lt;/b&gt;</span>', $html);
    }

    public function test_the_ungrouped_all_group_renders_its_content_without_a_section(): void
    {
        $html = $this->render('<x-tickets::group-section :group="$group" groupBy="">ROWS</x-tickets::group-section>', [
            'group' => ['label' => 'all', 'id' => 'all', 'class' => '', 'color' => '', 'items' => []],
        ]);

        $this->assertStringContainsString('ROWS', $html);
        $this->assertStringNotContainsString('lt-accordion', $html);
    }

    public function test_a_group_is_a_section_remembered_per_group_by_field(): void
    {
        $html = $this->render('<x-tickets::group-section :group="$group" groupBy="status">ROWS</x-tickets::group-section>', [
            'group' => ['label' => 'In <b>Progress</b>', 'id' => '3', 'class' => 'label-warning', 'color' => '', 'items' => [1, 2]],
        ]);

        $this->assertStringContainsString('data-lt-accordion="tickets-status-3"', $html);
        // The service escapes user text in labels; markup it builds (icons, avatars) stays.
        $this->assertStringContainsString('In <b>Progress</b>', $html);
        $this->assertStringContainsString('<span class="lt-badge lt-badge--count">2</span>', $html);
        $this->assertStringContainsString('--lt-accordion-title-color: var(--yellow)', $html);
        $this->assertStringContainsString('ROWS', $html);
    }

    public function test_a_milestone_colour_wins_over_the_status_colour(): void
    {
        $html = $this->render('<x-tickets::group-section :group="$group" groupBy="milestoneid">ROWS</x-tickets::group-section>', [
            'group' => ['label' => 'Launch', 'id' => '12', 'class' => 'label-info', 'color' => '#ff8800', 'items' => [1]],
        ]);

        $this->assertStringContainsString('--lt-accordion-title-color: #ff8800', $html);
        $this->assertStringNotContainsString('var(--dark-blue)', $html);
    }
}
