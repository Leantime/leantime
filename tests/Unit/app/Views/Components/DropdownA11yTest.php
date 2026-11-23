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
 * actions.dropdown and actions.modal-header, as screen readers and keyboards meet them: ⋮ menus
 * are named and say whether they are open (core/menus.js keeps aria-expanded current), opt-in
 * floating and keyboard menus are marked for core/menus.js, and a dialog's title is a heading with
 * an id the dialog is labelled by (core/modals.js).
 */
class DropdownA11yTest extends TestCase
{
    /** Isolated Blade stack (see StatTileEscapingTest): the app's view factory needs a database. */
    private function render(string $template, array $data = []): string
    {
        $files = new Filesystem;
        $cache = sys_get_temp_dir().'/lt-dropdown-a11y-blade-'.getmypid();
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

    public function test_a_command_menu_is_named_and_says_whether_it_is_open(): void
    {
        $html = $this->render('<x-global::actions.dropdown><li><a href="#">Edit</a></li></x-global::actions.dropdown>');

        $this->assertStringContainsString('aria-haspopup="true"', $html);
        $this->assertStringContainsString('aria-expanded="false"', $html);
        $this->assertStringContainsString('aria-label="'.e(__('label.actions')).'"', $html, 'an icon-only ⋮ gets "Actions"');
        $this->assertStringNotContainsString('data-lt-dropdown-float', $html, 'opt-in');
        $this->assertStringNotContainsString('data-lt-menu-keys', $html, 'opt-in');
    }

    public function test_a_given_name_wins_and_other_variants_are_unchanged(): void
    {
        $named = $this->render('<x-global::actions.dropdown aria-label="Actions for Order shelving"><li></li></x-global::actions.dropdown>');
        $this->assertStringContainsString('aria-label="Actions for Order shelving"', $named);

        $filter = $this->render('<x-global::actions.dropdown variant="filter" label="Group by"><li></li></x-global::actions.dropdown>');
        $this->assertStringNotContainsString('aria-haspopup', $filter);
        $this->assertStringNotContainsString('aria-label', $filter);
    }

    public function test_floating_and_keyboard_menus_are_marked_for_the_menu_script(): void
    {
        $html = $this->render('<x-global::actions.dropdown class="lt-x" floating keyboard><li></li></x-global::actions.dropdown>');

        $this->assertMatchesRegularExpression('/<div[^>]*data-lt-dropdown-float=""[^>]*class="inlineDropDownContainer lt-x"|<div[^>]*class="inlineDropDownContainer lt-x"[^>]*data-lt-dropdown-float=""/', $html);
        $this->assertMatchesRegularExpression('/<ul class="dropdown-menu"[^>]*data-lt-menu-keys/', $html);
    }

    public function test_the_dialog_title_is_a_heading_with_an_id(): void
    {
        $html = $this->render('<x-global::actions.modal-header title="Coming up" />');
        $this->assertMatchesRegularExpression('/<h4 class="lt-modal-title" id="lt-modal-title-[a-f0-9]{8}">/', $html);

        $fixed = $this->render('<x-global::actions.modal-header title="Coming up" title-id="comingUpTitle" description="Soon" />');
        $this->assertStringContainsString('<h4 class="lt-modal-title" id="comingUpTitle">', $fixed);
    }

    public function test_the_dialog_menu_has_keyboard_support(): void
    {
        $html = $this->render('<x-global::actions.modal-header title="To-do"><x-slot:actions><li><a href="#">Move</a></li></x-slot:actions></x-global::actions.modal-header>');

        $this->assertStringContainsString('data-lt-menu-keys', $html);
    }
}
