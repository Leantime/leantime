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
 * layout.content-box is the page's content box (.maincontentinner) with optional sidebars. Without
 * sidebars it must stay the plain box (every .maincontentinner style depends on that); with them it
 * renders grey sidebars and resize handles, starting at the user's saved width (clamped).
 */
class ContentBoxTest extends TestCase
{
    /** Isolated Blade stack (see StatTileEscapingTest): the app's view factory needs a database. */
    private function render(string $template, array $data = []): string
    {
        $files = new Filesystem;
        $cache = sys_get_temp_dir().'/lt-content-box-blade-'.getmypid();
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

    /** Saved UI states for the current user (UiState on an in-memory database). */
    private function savedStates(array $states): void
    {
        $db = new \Illuminate\Database\SQLiteConnection(new \PDO('sqlite::memory:'));
        $db->getSchemaBuilder()->create('zp_settings', function ($table) {
            $table->string('key', 175)->primary();
            $table->text('value')->nullable();
        });
        session(['userdata.id' => 7]);
        $uiState = new \Leantime\Core\UI\UiState($db);
        foreach ($states as $name => $value) {
            $uiState->set($name, $value);
        }
        app()->instance(\Leantime\Core\UI\UiState::class, $uiState);
    }

    // Note: in these one-line templates a closing </x-slot> needs whitespace after it, or Blade
    // reads "@endslotText" as one word.
    public function test_without_sidebars_it_is_the_plain_content_box(): void
    {
        $html = $this->render('<x-global::layout.content-box class="project-progress">Body</x-global::layout.content-box>');

        $this->assertMatchesRegularExpression('/^<div class="maincontentinner project-progress">\s*Body\s*<\/div>$/', trim($html));
    }

    public function test_the_id_is_the_box_html_id(): void
    {
        $plain = $this->render('<x-global::layout.content-box id="ideaBoards" style="min-height:350px;">Body</x-global::layout.content-box>');
        $this->assertStringContainsString('id="ideaBoards"', $plain);
        $this->assertStringContainsString('class="maincontentinner"', $plain);
        $this->assertStringContainsString('style="min-height:350px;"', $plain);

        $split = $this->render('<x-global::layout.content-box id="tickets-list"><x-slot:left>L</x-slot:left> M</x-global::layout.content-box>');
        $this->assertStringContainsString('id="tickets-list"', $split);
    }

    public function test_a_left_sidebar_splits_the_box_with_a_resize_handle(): void
    {
        $html = $this->render('<x-global::layout.content-box id="tickets-list" :leftWidth="320"><x-slot:left class="ticketList">List</x-slot:left> Detail</x-global::layout.content-box>');

        $this->assertStringContainsString('class="maincontentinner lt-content"', $html);
        $this->assertStringContainsString('style="--lt-content-left-w: 320px;"', $html);
        $this->assertStringContainsString('data-lt-content="tickets-list"', $html);
        $this->assertStringContainsString('<aside class="lt-content-side lt-content-side--left ticketList">List</aside>', $html);
        $this->assertStringContainsString('role="separator"', $html);
        $this->assertStringContainsString('data-lt-side="left" data-lt-default="320"', $html);
        $this->assertStringContainsString('<div class="lt-content-main">', $html);
        $this->assertStringNotContainsString('lt-content-side--right', $html);
    }

    public function test_both_sidebars_render_in_order(): void
    {
        $html = $this->render('<x-global::layout.content-box><x-slot:left>L</x-slot:left> M <x-slot:right>R</x-slot:right> </x-global::layout.content-box>');

        $left = strpos($html, 'lt-content-side--left');
        $main = strpos($html, 'lt-content-main');
        $right = strpos($html, 'lt-content-side--right');
        $this->assertTrue($left < $main && $main < $right);
        $this->assertSame(2, substr_count($html, 'role="separator"'));
        $this->assertStringContainsString('--lt-content-right-w: 300px;', $html);
        // No id: nothing to remember.
        $this->assertStringNotContainsString('data-lt-content=', $html);
    }

    public function test_the_saved_width_wins_and_is_clamped(): void
    {
        $this->savedStates([
            'content-box_my-calendar_left' => '333',
            'content-box_my-calendar_right' => '5000',
        ]);

        $html = $this->render('<x-global::layout.content-box id="my-calendar"><x-slot:left>L</x-slot:left> M <x-slot:right>R</x-slot:right> </x-global::layout.content-box>');

        $this->assertStringContainsString('--lt-content-left-w: 333px;', $html);
        $this->assertStringContainsString('--lt-content-right-w: 800px;', $html);
        $this->assertStringContainsString('aria-valuenow="333"', $html);
    }

    public function test_a_non_numeric_saved_width_falls_back_to_the_default(): void
    {
        $this->savedStates(['content-box_my-calendar_left' => 'wide; color:red']);

        $html = $this->render('<x-global::layout.content-box id="my-calendar"><x-slot:left>L</x-slot:left> M</x-global::layout.content-box>');

        $this->assertStringContainsString('--lt-content-left-w: 260px;', $html);
        $this->assertStringNotContainsString('color:red', $html);
    }

    public function test_the_state_id_is_dot_free(): void
    {
        $html = $this->render('<x-global::layout.content-box id="board.1"><x-slot:left>L</x-slot:left> M</x-global::layout.content-box>');

        $this->assertStringContainsString('data-lt-content="board~2e1"', $html);
    }

    public function test_stack_lg_and_main_class(): void
    {
        $html = $this->render('<x-global::layout.content-box stack="lg" mainClass="hidden-sm"><x-slot:left>L</x-slot:left> M</x-global::layout.content-box>');

        $this->assertStringContainsString('class="maincontentinner lt-content lt-content--stack-lg"', $html);
        $this->assertStringContainsString('<div class="lt-content-main hidden-sm">', $html);
    }

    public function test_resizable_off_renders_no_handles(): void
    {
        $html = $this->render('<x-global::layout.content-box :resizable="false"><x-slot:left>L</x-slot:left> M</x-global::layout.content-box>');

        $this->assertStringContainsString('lt-content-side--left', $html);
        $this->assertStringNotContainsString('role="separator"', $html);
    }
}
