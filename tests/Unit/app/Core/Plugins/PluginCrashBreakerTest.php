<?php

namespace Unit\app\Core\Plugins;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Leantime\Core\Exceptions\NotFoundException;
use Leantime\Core\Plugins\PluginCrashBreaker;
use Leantime\Domain\Plugins\Services\Plugins as PluginService;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Unit\TestCase;

/**
 * A plugin that keeps failing requests is disabled after PluginCrashBreaker::THRESHOLD failures
 * in PluginCrashBreaker::WINDOW_SECONDS; expected errors (not found, 4xx) never count.
 */
class PluginCrashBreakerTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    private array $disabled = [];

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        $this->disabled = [];
        app()->instance(PluginService::class, $this->makeEmpty(PluginService::class, [
            'disableAfterFailures' => function (string $folder, string $error) {
                $this->disabled[] = $folder;

                return true;
            },
        ]));
    }

    protected function tearDown(): void
    {
        app()->forgetInstance(PluginService::class);
        Cache::flush();

        parent::tearDown();
    }

    /** An error whose trace runs through a plugin file (the trace comes from where it's built). */
    private function errorFromPlugin(string $pluginPath): \RuntimeException
    {
        $error = new \RuntimeException('plugin hook failed');
        $file = new \ReflectionProperty(\Exception::class, 'file');
        $file->setValue($error, $pluginPath);

        return $error;
    }

    public function test_attributes_errors_to_folder_and_phar_plugins(): void
    {
        $breaker = new PluginCrashBreaker;

        $this->assertSame('Notes', $breaker->pluginFor($this->errorFromPlugin(APP_ROOT.'/app/Plugins/Notes/Services/Notes.php')));
        $this->assertSame('PgmPro', $breaker->pluginFor($this->errorFromPlugin('phar://'.APP_ROOT.'/app/Plugins/PgmPro/PgmPro.phar/register.php')));
        $this->assertNull($breaker->pluginFor(new \RuntimeException('core error')));
    }

    public function test_view_errors_name_the_plugin_template_in_the_message(): void
    {
        $error = new \ErrorException('Undefined variable $x (View: '.APP_ROOT.'/app/Plugins/Whiteboards/Templates/show.blade.php)');

        $this->assertSame('Whiteboards', (new PluginCrashBreaker)->pluginFor($error));
    }

    public function test_expected_errors_do_not_count(): void
    {
        $breaker = new PluginCrashBreaker;

        $this->assertFalse($breaker->counts(new NotFoundException('missing')));
        $this->assertFalse($breaker->counts(new HttpException(404)));
        $this->assertTrue($breaker->counts(new HttpException(500)));
        $this->assertTrue($breaker->counts(new \TypeError('bad call')));
        // Auth::authOrRedirect() and friends throw a redirect on purpose: not a fault.
        $this->assertFalse($breaker->counts(new HttpResponseException(new RedirectResponse('/auth/login'))));
        $this->assertTrue($breaker->counts(new HttpResponseException(new Response('down', 503))));
    }

    public function test_plugin_is_disabled_at_the_threshold_not_before(): void
    {
        $breaker = new PluginCrashBreaker;
        $error = $this->errorFromPlugin(APP_ROOT.'/app/Plugins/Notes/register.php');

        for ($failure = 1; $failure < PluginCrashBreaker::THRESHOLD; $failure++) {
            $breaker->record($error);
        }
        $this->assertSame([], $this->disabled);

        $breaker->record($error);
        $this->assertSame(['Notes'], $this->disabled);
    }

    public function test_core_errors_and_expected_errors_never_disable_anything(): void
    {
        $breaker = new PluginCrashBreaker;

        for ($failure = 0; $failure < PluginCrashBreaker::THRESHOLD * 2; $failure++) {
            $breaker->record(new \RuntimeException('core error'));
            $breaker->record(new HttpException(404, 'not found', null, [], 0));
        }

        $this->assertSame([], $this->disabled);
    }
}
