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
 * actions.modal-header as the to-do dialog uses it (#4008): the headline is part of the title. A
 * headline is user input, so it must come out escaped.
 */
class ModalHeaderTest extends TestCase
{
    /** Isolated Blade stack (see StatTileEscapingTest): the app's view factory needs a database. */
    private function render(string $template, array $data = []): string
    {
        $files = new Filesystem;
        $cache = sys_get_temp_dir().'/lt-modal-header-blade-'.getmypid();
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

    public function test_the_headline_is_in_the_title_escaped(): void
    {
        $html = $this->render(
            '<x-global::actions.modal-header icon="fa fa-list-check">'
            .'<x-slot:title>Task #{{ $id }} - {{ $headline }}</x-slot:title>'
            .'</x-global::actions.modal-header>',
            ['id' => 123, 'headline' => 'Log hours "Q4" <script>alert(1)</script>']
        );

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString(
            'Task #123 - Log hours &quot;Q4&quot; &lt;script&gt;alert(1)&lt;/script&gt;',
            $html
        );
    }
}
