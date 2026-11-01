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
 * elements.board-column-header is the bar on top of a board column. The kanban script finds the
 * count by `.widgettitle .count` and the status colour comes from `title-border-{class}`, so those
 * classes are pinned here.
 */
class BoardColumnHeaderTest extends TestCase
{
    /** Isolated Blade stack (see StatTileEscapingTest): the app's view factory needs a database. */
    private function render(string $template, array $data = []): string
    {
        $files = new Filesystem;
        $cache = sys_get_temp_dir().'/lt-board-column-blade-'.getmypid();
        $files->ensureDirectoryExists($cache);

        $compiler = new BladeCompiler($files, $cache);
        $compiler->anonymousComponentNamespace('global::components', 'global');

        $resolver = new EngineResolver;
        $resolver->register('blade', fn () => new CompilerEngine($compiler, $files));

        $finder = new FileViewFinder($files, [APP_ROOT.'/app/Views/Templates']);
        $finder->addNamespace('global', APP_ROOT.'/app/Views/Templates');

        $factory = new Factory($resolver, $finder, new Dispatcher);
        app()->instance(\Illuminate\Contracts\View\Factory::class, $factory);
        app()->instance('view', $factory);

        $file = $cache.'/snippet-'.md5($template).'.blade.php';
        $files->put($file, $template);

        return (string) $factory->file($file, $data)->render();
    }

    public function test_a_kanban_column_has_its_status_border_count_and_menu(): void
    {
        $html = $this->render('<x-global::elements.board-column-header :count="0" statusClass="label-info"><x-slot:actions><a class="editHeadline">⋮</a></x-slot:actions> {{ $name }}</x-global::elements.board-column-header>', ['name' => 'In <b>Progress</b>']);

        $this->assertStringContainsString('<h4 class="widgettitle title-primary title-border-label-info">', $html);
        $this->assertStringContainsString('<strong class="count">0</strong>', $html);
        $this->assertStringContainsString('In &lt;b&gt;Progress&lt;/b&gt;', $html);
        // The menu floats right, so it comes first.
        $this->assertLessThan(strpos($html, 'class="count"'), strpos($html, 'editHeadline'));
    }

    public function test_a_centered_blueprint_row_keeps_its_own_classes_and_icon(): void
    {
        $html = $this->render('<x-global::elements.board-column-header align="center" class="canvas-title-only" icon="fa fa-user">Customer <em>jobs</em></x-global::elements.board-column-header>');

        $this->assertStringContainsString('<h4 class="widgettitle title-primary center canvas-title-only">', $html);
        $this->assertStringContainsString('<i class="fa fa-user" aria-hidden="true"></i>', $html);
        $this->assertStringContainsString('Customer <em>jobs</em>', $html);
        $this->assertStringNotContainsString('class="count"', $html);
        $this->assertStringNotContainsString('title-border-', $html);
    }
}
