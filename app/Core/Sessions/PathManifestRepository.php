<?php

namespace Leantime\Core\Sessions;

use Exception;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Filesystem\Filesystem;
use Leantime\Core\Configuration\AppSettings;

/**
 * Cached path lists in storage/framework (viewPaths, composerPaths).
 *
 * They never expire on their own: plugin changes, `system:update` and `cache:clearAll` delete
 * them. Each one is stamped with the Leantime version that wrote it, and a manifest from another
 * version counts as missing. Which plugins load depends on the version (the compatibility guard
 * leaves out plugins that don't support it), and an update finished in the web updater deletes
 * neither file, so a plugin that becomes compatible again would otherwise stay without its views
 * or composers.
 */
class PathManifestRepository
{
    /**
     * The application implementation.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The path to the manifest file.
     *
     * @var string
     */
    protected $manifestPath;

    /**
     * Create a new service repository instance.
     *
     * @return void
     */
    public function __construct(ApplicationContract $app, Filesystem $files, private AppSettings $appSettings)
    {
        $this->app = $app;
        $this->files = $files;
        $this->manifestPath = storage_path('framework');

    }

    /**
     * Load a manifest written by {@see writeManifest()} for the running Leantime version.
     *
     * @return array|null Null when there is none, or it was written by another version.
     */
    public function loadManifest(string $manifestName)
    {
        $manifestFile = $this->manifestPath.'/'.$manifestName.'.php';

        if (! $this->files->exists($manifestFile)) {
            return null;
        }

        $manifest = $this->files->getRequire($manifestFile);

        if (! is_array($manifest) || ! isset($manifest[$manifestName])) {
            return null;
        }

        if (($manifest['appVersion'] ?? null) !== $this->appSettings->appVersion) {
            return null;
        }

        return array_merge(['when' => []], $manifest[$manifestName]);
    }

    /**
     * Determine if the manifest should be compiled.
     *
     * @param  array|null  $manifest
     * @param  array  $paths
     * @return bool
     */
    public function shouldRefresh($manifest, $paths)
    {
        return is_null($manifest) || $manifest != $paths;
    }

    /**
     * Create a fresh service manifest data structure.
     *
     * @param  string  $manifestName
     * @return array
     */
    protected function freshManifest($manifestName, array $paths)
    {
        return [$manifestName => $paths, 'appVersion' => $this->appSettings->appVersion];
    }

    /**
     * Write the service manifest file to disk.
     *
     * @return array
     *
     * @throws \Exception
     */
    public function writeManifest(string $manifestName, array $paths)
    {
        if (! is_writable($dirname = dirname($this->manifestPath.'/'.$manifestName.'.php'))) {
            throw new Exception("The {$dirname} directory must be present and writable.");
        }

        $manifest = $this->freshManifest($manifestName, $paths);

        $this->files->replace(
            $this->manifestPath.'/'.$manifestName.'.php', '<?php return '.var_export($manifest, true).';'
        );

        return array_merge(['when' => []], $manifest[$manifestName]);
    }
}
