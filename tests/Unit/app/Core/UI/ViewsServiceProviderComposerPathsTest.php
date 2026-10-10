<?php

namespace Unit\app\Core\UI;

use Leantime\Core\Plugins\Plugins;
use Leantime\Core\Sessions\PathManifestRepository;
use Leantime\Core\UI\ViewsServiceProvider;

/**
 * The composerPaths manifest never expires, so a request that can't read the enabled plugins must not
 * cache a composer list without the plugin composers (it left plugin views without their data until
 * the file was deleted by hand).
 */
class ViewsServiceProviderComposerPathsTest extends \Unit\TestCase
{
    use \Codeception\Test\Feature\Stub;

    private function provider(): ViewsServiceProvider
    {
        return new ViewsServiceProvider(app());
    }

    private function manifestRepository(int $expectedWrites): PathManifestRepository
    {
        $repository = $this->createMock(PathManifestRepository::class);
        $repository->method('loadManifest')->willReturn(null);
        $repository->expects($this->exactly($expectedWrites))
            ->method('writeManifest')
            ->willReturnCallback(fn (string $name, array $paths) => array_merge(['when' => []], $paths));

        return $repository;
    }

    public function test_a_failed_plugin_lookup_is_not_cached(): void
    {
        app()->instance(PathManifestRepository::class, $this->manifestRepository(expectedWrites: 0));

        $plugins = $this->createMock(Plugins::class);
        $plugins->method('getEnabledPluginPaths')
            ->with(true)
            ->willThrowException(new \RuntimeException('no database'));
        app()->instance(Plugins::class, $plugins);

        $composers = $this->provider()->getComposerPaths();

        $this->assertContains('Leantime\\Views\\Composers\\App', $composers, 'app composers still register for this request');
    }

    public function test_a_successful_plugin_lookup_is_cached(): void
    {
        app()->instance(PathManifestRepository::class, $this->manifestRepository(expectedWrites: 1));

        $plugins = $this->createMock(Plugins::class);
        $plugins->method('getEnabledPluginPaths')->with(true)->willReturn([]);
        app()->instance(Plugins::class, $plugins);

        $composers = $this->provider()->getComposerPaths();

        $this->assertContains('Leantime\\Views\\Composers\\App', $composers);
    }

    public function test_the_strict_lookup_rethrows_instead_of_falling_back(): void
    {
        $plugins = $this->make(Plugins::class, ['enabledPlugins' => []]);

        app()->bind(\Leantime\Domain\Plugins\Services\Plugins::class, function () {
            throw new \RuntimeException('no database');
        });

        $this->assertSame([], $plugins->getEnabledPluginPaths(), 'the default lookup falls back to the system plugins');

        $this->expectException(\RuntimeException::class);
        $plugins->getEnabledPluginPaths(strict: true);
    }
}
