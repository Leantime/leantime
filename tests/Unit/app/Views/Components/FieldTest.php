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
 * forms.field wraps one label + control (+ help line). The label must point at the control, the
 * prop texts are escaped (labels can carry user data, e.g. custom field names) while the label
 * slot takes markup, and the inline layout's column width lands on the label only when asked.
 */
class FieldTest extends TestCase
{
    /** Isolated Blade stack (see StatTileEscapingTest): the app's view factory needs a database. */
    private function render(string $template, array $data = []): string
    {
        $files = new Filesystem;
        $cache = sys_get_temp_dir().'/lt-field-blade-'.getmypid();
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

    public function test_a_stacked_field_labels_its_control(): void
    {
        $html = $this->render('<x-global::forms.field for="name" :label="$label" required><input id="name" /></x-global::forms.field>', ['label' => 'Name <b>']);

        $this->assertMatchesRegularExpression('/^<div class="lt-field">/', trim($html));
        $this->assertMatchesRegularExpression('/<label (?=[^>]*for="name")(?=[^>]*class="lt-label lt-field-label")[^>]*>Name &lt;b&gt;<span class="lt-label-required" aria-hidden="true">\*<\/span><\/label>/', $html);
        $this->assertStringContainsString('<div class="lt-field-control">', $html);
        $this->assertStringNotContainsString('style=', $html);
        $this->assertStringNotContainsString('lt-field-help', $html);
    }

    public function test_an_inline_field_takes_its_label_width_and_help(): void
    {
        $html = $this->render('<x-global::forms.field layout="inline" :labelWidth="120" for="hours" label="Hours" :help="$help" class="hoursField"><input id="hours" /></x-global::forms.field>', ['help' => 'Per week <i>']);

        $this->assertStringContainsString('class="lt-field lt-field--inline hoursField"', $html);
        $this->assertStringContainsString('style="--lt-field-label-w: 120px"', $html);
        $this->assertStringContainsString('<p class="lt-field-help">Per week &lt;i&gt;</p>', $html);
    }

    public function test_the_label_slot_takes_markup(): void
    {
        $html = $this->render('<x-global::forms.field for="key"><x-slot:label><i class="fa fa-key"></i> Key</x-slot:label> <input id="key" /></x-global::forms.field>');

        $this->assertStringContainsString('><i class="fa fa-key"></i> Key</label>', $html);
        $this->assertStringNotContainsString('style=', $html);
    }

    public function test_without_a_label_only_the_control_renders(): void
    {
        $html = $this->render('<x-global::forms.field><input id="x" /></x-global::forms.field>');

        $this->assertStringNotContainsString('<label', $html);
        $this->assertStringContainsString('<input id="x" />', $html);
    }
}
