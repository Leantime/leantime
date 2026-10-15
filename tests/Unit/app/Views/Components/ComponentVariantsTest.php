<?php

namespace Unit\app\Views\Components;

use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;
use Illuminate\View\ViewException;
use Unit\TestCase;

/**
 * Variants added to shared components for the plugin move (3.12). Plugins build against these,
 * so the contracts are pinned here: icon-only buttons are named and never submit by accident,
 * and stat tiles accept the danger / warning / success tones.
 */
class ComponentVariantsTest extends TestCase
{
    /**
     * Renders a Blade snippet through an isolated Blade stack (see StatTileEscapingTest for why:
     * the app's view factory pulls in composers that resolve the database, which unit tests lack).
     */
    private function render(string $template, array $data = []): string
    {
        $files = new Filesystem;
        $cache = sys_get_temp_dir().'/lt-variants-blade-'.getmypid();
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

    public function test_icon_only_button_is_named_and_does_not_submit(): void
    {
        $html = $this->render('<x-global::forms.button icon="fa fa-pen" ariaLabel="Edit" />');

        $this->assertStringContainsString('type="button"', $html);
        $this->assertStringContainsString('aria-label="Edit"', $html);
        $this->assertStringContainsString('btn-icon', $html);
        $this->assertStringContainsString('<i class="fa fa-pen" aria-hidden="true"></i>', $html);
    }

    public function test_icon_only_button_can_still_submit_when_asked(): void
    {
        $html = $this->render('<x-global::forms.button icon="fa fa-check" ariaLabel="Save" inputType="submit" />');

        $this->assertStringContainsString('type="submit"', $html);
    }

    public function test_icon_only_button_without_a_name_fails(): void
    {
        $this->expectException(ViewException::class);
        $this->expectExceptionMessage('needs ariaLabel');

        $this->render('<x-global::forms.button icon="fa fa-pen" />');
    }

    public function test_plain_button_keeps_the_native_type_default(): void
    {
        $html = $this->render('<x-global::forms.button contentRole="primary">Save</x-global::forms.button>');

        $this->assertStringNotContainsString('type=', $html);
    }

    public function test_stat_tile_danger_is_the_risk_tone(): void
    {
        $html = $this->render('<x-global::statTile value="2" label="Overdue" tone="danger" />');

        $this->assertMatchesRegularExpression('/class="lt-stat risk"/', $html);
    }

    public function test_stat_tile_warning_and_success_tones(): void
    {
        $this->assertStringContainsString('lt-stat tone-warning', $this->render('<x-global::statTile value="3" label="Over" tone="warning" />'));
        $this->assertStringContainsString('lt-stat tone-success', $this->render('<x-global::statTile value="9" label="On track" tone="success" />'));
    }

    public function test_button_group_is_a_labelled_group_that_forwards_attributes(): void
    {
        $html = $this->render('<x-global::forms.button-group label="Save options" id="saveGroup" class="extra"><button class="btn">Save</button></x-global::forms.button-group>');

        $this->assertStringContainsString('role="group"', $html);
        $this->assertStringContainsString('aria-label="Save options"', $html);
        $this->assertStringContainsString('id="saveGroup"', $html);
        $this->assertMatchesRegularExpression('/class="lt-btn-group extra"/', $html);
        $this->assertStringNotContainsString('lt-btn-group--segmented', $html);
    }

    public function test_segmented_button_group_gets_its_modifier(): void
    {
        $html = $this->render('<x-global::forms.button-group segmented label="Chart metric"><button class="btn active" aria-pressed="true">Hours</button></x-global::forms.button-group>');

        $this->assertStringContainsString('lt-btn-group--segmented', $html);
        $this->assertStringContainsString('aria-label="Chart metric"', $html);
        $this->assertStringContainsString('aria-pressed="true"', $html);
    }
}
