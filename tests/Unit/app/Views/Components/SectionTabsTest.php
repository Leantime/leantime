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
 * navigation.section-tabs keeps the ul > li > a[href=#id] + div#id markup that plugins print into
 * (CustomFields → To-Do dialog, AdvancedAuth → Company settings, PgmPro/StrategyPro → project tabs).
 * core/sectionTabs.js reads exactly this shape, so the markup is the contract.
 */
class SectionTabsTest extends TestCase
{
    /** Isolated Blade stack (see StatTileEscapingTest): the app's view factory needs a database. */
    private function render(string $template, array $data = []): string
    {
        $files = new Filesystem;
        $cache = sys_get_temp_dir().'/lt-section-tabs-blade-'.getmypid();
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

    public function test_tabs_link_to_their_panels_and_plugin_markup_sits_beside_them(): void
    {
        $html = $this->render(
            '<x-global::navigation.section-tabs class="ticketTabs" :label="$label" remember="query:tab">'
            .'<x-slot:tabs>'
            .'<x-global::navigation.section-tabs.tab for="files" icon="fa-file" id="filesTab">Files (2)</x-global::navigation.section-tabs.tab>'
            .'<li><a href="#custom-fields">Custom fields</a></li>'
            .'</x-slot:tabs>'
            .'<div id="files">files</div><div id="custom-fields">fields</div>'
            .'</x-global::navigation.section-tabs>',
            ['label' => 'To-Do <b>'],
        );

        $this->assertStringContainsString('class="tabbedwidget lt-section-tabs ticketTabs"', $html);
        $this->assertStringContainsString('data-lt-section-tabs', $html);
        $this->assertStringContainsString('data-tabs-remember="query:tab"', $html);
        $this->assertStringContainsString('<ul class="lt-section-tabs-nav" aria-label="To-Do &lt;b&gt;">', $html);
        $this->assertStringContainsString('<li><a href="#files" id="filesTab"><span class="fa fa-file" aria-hidden="true"></span> Files (2)</a></li>', $html);
        $this->assertStringContainsString('<li><a href="#custom-fields">Custom fields</a></li>', $html);
        $this->assertMatchesRegularExpression('/<\/ul>\s*<div id="files">files<\/div><div id="custom-fields">fields<\/div>\s*<\/div>/', $html);
    }

    public function test_without_remember_or_icon_nothing_extra_renders(): void
    {
        $html = $this->render(
            '<x-global::navigation.section-tabs label="Projects">'
            .'<x-slot:tabs><x-global::navigation.section-tabs.tab for="myProjects">Mine</x-global::navigation.section-tabs.tab></x-slot:tabs>'
            .'<div id="myProjects"></div>'
            .'</x-global::navigation.section-tabs>',
        );

        $this->assertStringNotContainsString('data-tabs-remember', $html);
        $this->assertStringContainsString('<li><a href="#myProjects">Mine</a></li>', $html);
    }
}
