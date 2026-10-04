<?php

namespace Unit\app\Core\Console;

use Leantime\Core\Configuration\Environment;
use Leantime\Core\Console\ConsoleKernel;
use Leantime\Core\Language;
use Leantime\Domain\Plugins\Services\Plugins as PluginService;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;

/**
 * The console kernel loads enabled plugins so their scheduled jobs run from cron. Pre-install it
 * skips quietly; on an installed instance a failure is never recorded as "loaded", and the
 * scheduler fails loudly instead of running without the plugin jobs.
 */
class ConsoleKernelPluginLoadingTest extends \Unit\TestCase
{
    use \Codeception\Test\Feature\Stub;

    protected function setUp(): void
    {
        parent::setUp();

        session()->forget(['usersettings.language', 'companysettings.language']);
    }

    private function kernel(?string $command): ConsoleKernel
    {
        $kernel = new ConsoleKernel(app(), app('events'));
        $this->setPrivate($kernel, 'currentCommand', $command);

        return $kernel;
    }

    private function setPrivate(ConsoleKernel $kernel, string $property, mixed $value): void
    {
        (new \ReflectionProperty(ConsoleKernel::class, $property))->setValue($kernel, $value);
    }

    private function pluginsLoaded(ConsoleKernel $kernel): bool
    {
        return (new \ReflectionProperty(ConsoleKernel::class, 'pluginsLoaded'))->getValue($kernel);
    }

    private function loadEnabledPlugins(ConsoleKernel $kernel): void
    {
        (new \ReflectionMethod(ConsoleKernel::class, 'loadEnabledPlugins'))->invoke($kernel);
    }

    private function bindSettings(bool $installed, mixed $companyLanguage = false): void
    {
        app()->instance(SettingRepository::class, $this->makeEmpty(SettingRepository::class, [
            'checkIfInstalled' => $installed,
            'getSetting' => fn ($key) => $key === 'companysettings.language' ? $companyLanguage : false,
        ]));
    }

    private function bindEnabledPlugins(callable $enabledPlugins): void
    {
        app()->instance(PluginService::class, $this->makeEmpty(PluginService::class, [
            'getEnabledPlugins' => $enabledPlugins,
        ]));
    }

    private function bindLanguage(): void
    {
        $language = $this->make(Language::class, ['readIni' => []]);
        $language->langlist = ['en-US' => 'English', 'de-DE' => 'Deutsch'];
        $language->config = $this->make(Environment::class, [
            'get' => fn ($key, $default = null) => $key === 'language' ? 'en-US' : $default,
        ]);
        app()->instance(Language::class, $language);
    }

    public function test_pre_install_is_skipped_quietly_and_not_marked_loaded(): void
    {
        $this->bindSettings(installed: false);
        $this->bindEnabledPlugins(fn () => $this->fail('Plugins must not be read before install'));

        $kernel = $this->kernel('schedule:run');
        $this->loadEnabledPlugins($kernel);

        $this->assertFalse($this->pluginsLoaded($kernel));
    }

    public function test_failure_on_installed_instance_fails_the_scheduler_loudly(): void
    {
        $this->bindSettings(installed: true);
        $this->bindEnabledPlugins(fn () => throw new \RuntimeException('plugin table unavailable'));

        $kernel = $this->kernel('schedule:run');

        try {
            $this->loadEnabledPlugins($kernel);
            $this->fail('Expected the plugin loading failure to propagate for schedule:run');
        } catch (\RuntimeException $e) {
            $this->assertSame('plugin table unavailable', $e->getMessage());
        }

        $this->assertFalse($this->pluginsLoaded($kernel));
    }

    public function test_failure_on_installed_instance_lets_other_commands_run_and_retries_later(): void
    {
        $this->bindSettings(installed: true);
        $this->bindEnabledPlugins(fn () => throw new \RuntimeException('plugin table unavailable'));

        $kernel = $this->kernel('system:update');
        $this->loadEnabledPlugins($kernel);

        $this->assertFalse($this->pluginsLoaded($kernel));
    }

    public function test_success_marks_loaded_and_sets_company_language_for_plugins(): void
    {
        $this->bindSettings(installed: true, companyLanguage: 'de-DE');
        $this->bindEnabledPlugins(fn () => []);
        $this->bindLanguage();

        $kernel = $this->kernel('schedule:run');
        $this->loadEnabledPlugins($kernel);

        $this->assertTrue($this->pluginsLoaded($kernel));
        $this->assertSame('de-DE', session('usersettings.language'));
    }

    public function test_invalid_company_language_falls_back_to_configured_default(): void
    {
        $this->bindSettings(installed: true, companyLanguage: '../../etc/passwd');
        $this->bindEnabledPlugins(fn () => []);
        $this->bindLanguage();

        $kernel = $this->kernel('schedule:run');
        $this->loadEnabledPlugins($kernel);

        $this->assertSame('en-US', session('usersettings.language'));
    }
}
