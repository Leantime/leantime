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
 * elements.details-list and feedback.alert are new for the plugin move (3.12) and have few core
 * callers yet, so their markup contracts are pinned here: escaped values, the item sub-component,
 * and the alert's tone, role and default icon.
 */
class DetailsListAndAlertTest extends TestCase
{
    /** Isolated Blade stack (see StatTileEscapingTest): the app's view factory needs a database. */
    private function render(string $template, array $data = []): string
    {
        $files = new Filesystem;
        $cache = sys_get_temp_dir().'/lt-details-alert-blade-'.getmypid();
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

    public function test_items_render_as_escaped_label_value_rows(): void
    {
        $html = $this->render('<x-global::elements.details-list :items="$items" />', [
            'items' => ['Owner' => '<b>Marcel</b>', 'Due' => 'Oct 30'],
        ]);

        $this->assertStringContainsString('<dl class="lt-details">', $html);
        $this->assertStringContainsString('<dt>Owner</dt>', $html);
        $this->assertStringContainsString('&lt;b&gt;Marcel&lt;/b&gt;', $html);
        $this->assertStringNotContainsString('<b>Marcel</b>', $html);
    }

    public function test_item_rows_take_markup_and_inline_layout(): void
    {
        $html = $this->render('<x-global::elements.details-list layout="inline"><x-global::elements.details-list.item label="Project"><a href="/p/1">Alpha</a></x-global::elements.details-list.item></x-global::elements.details-list>');

        $this->assertStringContainsString('lt-details--inline', $html);
        $this->assertStringContainsString('<dt>Project</dt>', $html);
        $this->assertStringContainsString('<a href="/p/1">Alpha</a>', $html);
    }

    public function test_info_alert_is_a_polite_status_with_its_default_icon(): void
    {
        $html = $this->render('<x-global::feedback.alert>Saved for later</x-global::feedback.alert>');

        $this->assertStringContainsString('role="status"', $html);
        $this->assertStringContainsString('lt-alert lt-alert--info', $html);
        $this->assertStringContainsString('fa-circle-info', $html);
    }

    public function test_danger_alert_interrupts_and_can_drop_its_icon(): void
    {
        $html = $this->render('<x-global::feedback.alert tone="danger" icon="" title="Import failed">2 rows</x-global::feedback.alert>');

        $this->assertStringContainsString('role="alert"', $html);
        $this->assertStringContainsString('lt-alert--danger', $html);
        $this->assertStringContainsString('<div class="lt-alert-title">Import failed</div>', $html);
        $this->assertStringNotContainsString('lt-alert-icon', $html);
    }
}
