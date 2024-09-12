<?php

namespace Leantime\Core\Support;

use Exception;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Filesystem\Filesystem;

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
    public function __construct(ApplicationContract $app, Filesystem $files)
    {
        $this->app = $app;
        $this->files = $files;
        $this->manifestPath = $app->basePath().'/cache';

    }

    /**
     * Load the service provider manifest JSON file.
     *
     * @return array|null
     */
    public function loadManifest(string $manifestName)
    {

        if ($this->files->exists($this->manifestPath.'/'.$manifestName.'.php')) {
            $manifest = $this->files->getRequire($this->manifestPath.'/'.$manifestName.'.php');

            if ($manifest) {
                return array_merge(['when' => []], $manifest[$manifestName]);
            }
        }

        return false;
    }

    /**
     * Determine if the manifest should be compiled.
     *
     * @param  array  $manifest
     * @param  array  $paths
     * @return bool
     */
    public function shouldRefresh($manifest, $paths)
    {
        return is_null($manifest) || $manifest[$manifest] != $paths;
    }

    /**
     * Create a fresh service manifest data structure.
     *
     * @param  array  return [$this->manifestKey => $paths];
     * @return array
     */
    protected function freshManifest($manifestName, array $paths)
    {
        return [$manifestName => $paths];
    }

    /**
     * Write the service manifest file to disk.
     *
     * @param  array  $manifest
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
