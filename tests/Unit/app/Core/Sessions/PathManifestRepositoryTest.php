<?php

namespace Unit\app\Core\Sessions;

use Illuminate\Filesystem\Filesystem;
use Leantime\Core\Configuration\AppSettings;
use Leantime\Core\Sessions\PathManifestRepository;
use Unit\TestCase;

/**
 * The viewPaths / composerPaths manifests never expire on their own, and which plugins they include
 * depends on the Leantime version. A manifest written by another version must be rebuilt, or a
 * plugin that becomes compatible after an update stays without its views or composer data.
 */
class PathManifestRepositoryTest extends TestCase
{
    private string $manifestDirectory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manifestDirectory = sys_get_temp_dir().'/lt-manifest-'.getmypid().'-'.uniqid();
        mkdir($this->manifestDirectory);
    }

    protected function tearDown(): void
    {
        (new Filesystem)->deleteDirectory($this->manifestDirectory);

        parent::tearDown();
    }

    private function repository(string $leantimeVersion): PathManifestRepository
    {
        $appSettings = new AppSettings;
        $appSettings->appVersion = $leantimeVersion;

        $repository = new PathManifestRepository(app(), new Filesystem, $appSettings);
        (fn (string $path) => $this->manifestPath = $path)->call($repository, $this->manifestDirectory);

        return $repository;
    }

    public function test_a_manifest_is_read_back_by_the_version_that_wrote_it(): void
    {
        $this->repository('3.13.2')->writeManifest('composerPaths', ['App', 'FieldComposer']);

        $this->assertSame(['when' => [], 'App', 'FieldComposer'], $this->repository('3.13.2')->loadManifest('composerPaths'));
    }

    public function test_a_manifest_from_another_version_counts_as_missing(): void
    {
        $this->repository('3.12.0')->writeManifest('composerPaths', ['App']);

        $this->assertNull($this->repository('3.13.2')->loadManifest('composerPaths'));
    }

    public function test_a_manifest_without_a_version_counts_as_missing(): void
    {
        // Written before manifests were stamped.
        file_put_contents($this->manifestDirectory.'/viewPaths.php', '<?php return '.var_export(['viewPaths' => ['tickets' => '/x']], true).';');

        $this->assertNull($this->repository('3.13.2')->loadManifest('viewPaths'));
    }

    public function test_no_manifest_file_is_missing(): void
    {
        $this->assertNull($this->repository('3.13.2')->loadManifest('viewPaths'));
    }
}
