<?php

namespace Unit\app\Domain\Plugins\Services;

use Illuminate\Support\Facades\Http;
use Leantime\Core\Configuration\AppSettings;
use Leantime\Core\Plugins\PluginCompatibility;
use Leantime\Domain\Plugins\Models\InstalledPlugin;
use Leantime\Domain\Plugins\Models\MarketplacePlugin;
use Leantime\Domain\Plugins\Repositories\Plugins as PluginRepository;
use Leantime\Domain\Plugins\Services\PluginArchive;
use Leantime\Domain\Plugins\Services\Plugins;
use Leantime\Domain\Setting\Services\Setting as SettingsService;
use Leantime\Domain\Users\Services\Users as UsersService;
use Unit\TestCase;

/**
 * The marketplace install/update path end to end, with the download faked, the archive and the
 * fresh-process load check stood in for: an update keeps the previous version, records the
 * new one only after it loaded, and goes back to the previous version when the check, reading
 * its composer.json, or saving it fails.
 */
class PluginUpdateFlowTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    private string $pluginDir;

    /** @var array<int, array<int, mixed>> */
    private array $archiveInstalls = [];

    private int $restores = 0;

    /** @var array<int, array{0: int, 1: string}> */
    private array $rowUpdates = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->pluginDir = sys_get_temp_dir().'/lt-update-flow-'.getmypid().'-'.uniqid().'/';
        mkdir($this->pluginDir.'Notes', 0777, true);

        Http::fake([
            'https://marketplace.test/ltmp-api/download/*' => Http::response('zip-bytes', 200, [
                'Content-Type' => 'application/zip',
                'Content-Disposition' => 'attachment; filename="Notes.zip"',
            ]),
        ]);
    }

    protected function tearDown(): void
    {
        @rmdir($this->pluginDir.'Notes');
        @rmdir($this->pluginDir);

        parent::tearDown();
    }

    /**
     * @param  array{0: array<string, mixed>, 1: string|null}  $verifyResult  What the fresh process reports.
     * @param  array<string, mixed>  $repositoryCalls  Overrides for the repository stub.
     */
    private function service(array $verifyResult, array $repositoryCalls = []): Plugins
    {
        $appSettings = new AppSettings;
        $appSettings->appVersion = '3.12.0';

        $plugins = $this->make(Plugins::class, [
            'marketplaceUrl' => 'https://marketplace.test',
            'appSettings' => $appSettings,
            'compatibility' => new PluginCompatibility($appSettings),
            'pluginArchive' => $this->makeEmpty(PluginArchive::class, [
                'install' => function (...$args) {
                    $this->archiveInstalls[] = $args;
                },
                'restore' => function () {
                    $this->restores++;
                },
            ]),
            'pluginRepository' => $this->makeEmpty(PluginRepository::class, $repositoryCalls + [
                'getAllPlugins' => fn () => [$this->installedNotes()],
                'getPluginIdsByFolder' => fn () => [3],
                'replacePluginRows' => function (int $id, $plugin, array $duplicates) {
                    $this->rowUpdates[] = [$id, $plugin->version];
                },
            ]),
            'settingsService' => $this->makeEmpty(SettingsService::class, ['getCompanyId' => 'instance-1']),
            'usersService' => $this->makeEmpty(UsersService::class, ['getNumberOfUsers' => 3]),
            'runPluginVerify' => fn () => $verifyResult,
            'clearCache' => null,
        ]);

        (new \ReflectionProperty(Plugins::class, 'pluginDirectory'))->setValue($plugins, $this->pluginDir);

        return $plugins;
    }

    /** The installed row the update replaces. */
    private function installedNotes(): InstalledPlugin
    {
        $installed = new InstalledPlugin;
        $installed->id = 3;
        $installed->name = 'leantime/notes';
        $installed->foldername = 'Notes';
        $installed->format = 'phar';
        $installed->version = '1.4.2';
        $installed->license = 'key-123';

        return $installed;
    }

    private function notes(): MarketplacePlugin
    {
        $plugin = new MarketplacePlugin;
        $plugin->identifier = 'leantime_notes';
        $plugin->license = 'key-123';

        return $plugin;
    }

    private function composer(string $version): array
    {
        return ['name' => 'leantime/notes', 'description' => 'Notes', 'version' => $version, 'homepage' => '', 'authors' => []];
    }

    public function test_a_verified_update_keeps_the_previous_version_and_rewrites_the_row(): void
    {
        $this->service([$this->composer('1.5.0'), null])->installMarketplacePlugin($this->notes(), '1.5.0');

        $this->assertCount(1, $this->archiveInstalls);
        $this->assertSame('Notes', $this->archiveInstalls[0][1]);
        $this->assertSame(storage_path('app/plugins-previous/Notes'), $this->archiveInstalls[0][3], 'The update keeps the version it replaces');
        $this->assertSame([[3, '1.5.0']], $this->rowUpdates);
        $this->assertSame(0, $this->restores);
        Http::assertSent(fn ($request) => $request->hasHeader('X-License-Key', 'key-123'));
    }

    public function test_an_update_that_does_not_load_is_rolled_back(): void
    {
        try {
            $this->service([[], 'broken release (register.php:57)'])->installMarketplacePlugin($this->notes(), '1.5.0');
            $this->fail('A failed load check must fail the update');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('broken release', $e->getMessage());
        }

        $this->assertSame(1, $this->restores);
        $this->assertSame([], $this->rowUpdates);
    }

    public function test_an_update_whose_composer_json_cannot_be_used_is_rolled_back(): void
    {
        // Loads, but its composer.json has no name: it can't become a plugin row.
        $this->expectException(\RuntimeException::class);

        try {
            $this->service([['version' => '1.5.0', 'description' => '', 'homepage' => '', 'authors' => []], null])
                ->installMarketplacePlugin($this->notes(), '1.5.0');
        } finally {
            $this->assertSame(1, $this->restores);
            $this->assertSame([], $this->rowUpdates);
        }
    }

    public function test_an_update_that_cannot_be_saved_is_rolled_back(): void
    {
        $this->expectException(\RuntimeException::class);

        try {
            $this->service([$this->composer('1.5.0'), null], [
                'replacePluginRows' => fn () => throw new \PDOException('database gone'),
            ])->installMarketplacePlugin($this->notes(), '1.5.0');
        } finally {
            $this->assertSame(1, $this->restores);
        }
    }

    public function test_a_new_install_that_cannot_be_saved_fails_without_a_restore(): void
    {
        rmdir($this->pluginDir.'Notes');
        $plugins = $this->service([[], 'not used for new installs'], [
            'getPluginIdsByFolder' => fn () => [],
            'addPlugin' => fn () => false,
        ]);

        try {
            $plugins->installMarketplacePlugin($this->notes(), '1.5.0');
            $this->fail('A new install that cannot be saved must fail');
        } catch (\Throwable $e) {
            $this->assertSame(0, $this->restores, 'Nothing to restore for a new install');
            $this->assertNull($this->archiveInstalls[0][3] ?? null, 'A new install keeps no previous version');
        }
    }

    public function test_a_download_for_another_plugin_folder_is_refused_before_files_change(): void
    {
        try {
            $this->service([$this->composer('1.5.0'), null])->installMarketplacePlugin($this->notes(), '1.5.0', 'PgmPro');
            $this->fail('A package for another folder must be refused');
        } catch (\RuntimeException $e) {
            // Refused before the archive step (the message names both folders in the language file).
        }

        $this->assertSame([], $this->archiveInstalls, 'No files were touched');
        $this->assertSame([], $this->rowUpdates);
    }

    public function test_a_different_version_than_requested_is_rolled_back(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('not 1.5.0');

        try {
            $this->service([$this->composer('1.4.9'), null])->installMarketplacePlugin($this->notes(), '1.5.0', 'Notes');
        } finally {
            $this->assertSame(1, $this->restores);
            $this->assertSame([], $this->rowUpdates);
        }
    }

    public function test_a_different_package_than_installed_is_rolled_back(): void
    {
        $composer = $this->composer('1.5.0');
        $composer['name'] = 'someone/else';
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('someone/else');

        try {
            $this->service([$composer, null])->installMarketplacePlugin($this->notes(), '1.5.0', 'Notes');
        } finally {
            $this->assertSame(1, $this->restores);
        }
    }

    public function test_a_second_concurrent_update_of_the_same_plugin_is_refused(): void
    {
        $lock = \Illuminate\Support\Facades\Cache::lock('plugins.install.leantime_notes', 900);
        $this->assertTrue($lock->get());

        try {
            $this->service([$this->composer('1.5.0'), null])->installMarketplacePlugin($this->notes(), '1.5.0', 'Notes');
            $this->fail('A concurrent update must be refused');
        } catch (\RuntimeException $e) {
            $this->assertSame(__('notification.plugin_update_busy'), $e->getMessage());
        } finally {
            $lock->release();
        }

        $this->assertSame([], $this->archiveInstalls);
    }

    /** The row exists but the folder doesn't: no fallback, so no update (never a silent fresh install). */
    public function test_an_update_without_its_installed_files_is_refused_before_download(): void
    {
        rmdir($this->pluginDir.'Notes');

        try {
            $this->service([$this->composer('1.5.0'), null])->installMarketplacePlugin($this->notes(), '1.5.0', 'Notes');
            $this->fail('An update without its installed files must be refused');
        } catch (\RuntimeException $e) {
            // Refused (the message is in the language file).
        }

        $this->assertSame([], $this->archiveInstalls);
        $this->assertSame([], $this->rowUpdates);
        Http::assertNothingSent();
    }

    /** Decided from an older snapshot: with the lock held, the target must still be newer. */
    public function test_a_target_that_is_no_longer_newer_is_not_installed(): void
    {
        $this->expectException(\RuntimeException::class);

        try {
            // Installed is 1.4.2; asking for 1.4.2 (or older) again is refused before download.
            $this->service([$this->composer('1.4.2'), null])->installMarketplacePlugin($this->notes(), '1.4.2', 'Notes');
        } finally {
            $this->assertSame([], $this->archiveInstalls);
            Http::assertNothingSent();
        }
    }
}
