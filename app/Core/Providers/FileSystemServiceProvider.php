<?php

namespace Leantime\Core\Providers;

use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\ServiceProvider;

class FileSystemServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

        $this->prepareConfig();

        $this->registerNativeFilesystem();

        $this->registerFlysystem();

        $this->app->alias(\Illuminate\Filesystem\Filesystem::class, 'files');

        $this->app->alias(\Illuminate\Filesystem\FilesystemManager::class, 'filesystem');
        $this->app->alias(\Illuminate\Filesystem\FilesystemManager::class, \Illuminate\Contracts\Filesystem\Factory::class);

        $this->app->alias(\Illuminate\Contracts\Filesystem\Filesystem::class, 'filesystem.disk');
        $this->app->alias(\Illuminate\Contracts\Filesystem\Cloud::class, 'filesystem.cloud');

    }

    public function prepareConfig()
    {

        if ($this->app['config']['filesystem'] === null) {

            $this->app['config']['filesystem'] = [
                'default' => 'local',
                'cloud' => 's3',
                'disks' => [
                    'local' => [
                        'driver' => 'local',
                        'root' => storage_path('userfiles'),
                    ],

                    'public' => [
                        'driver' => 'local',
                        'root' => storage_path('public/userfiles'),
                        'url' => $this->app['config']['appUrl'].'/userfiles',
                        'visibility' => 'public',
                    ],
                    's3' => [
                        'driver' => 's3',
                        'key' => $this->app['config']['s3Key'],
                        'secret' => $this->app['config']['s3Secret'],
                        'region' => $this->app['config']['s3Region'],
                        'bucket' => $this->app['config']['s3Bucket'],
                        'url' => $this->app['config']['s3EndPoint'],

                        // Optional cache settings, available with any storage driver
                        'cache' => [
                            'driver' => 'laravel',
                        ],
                    ],
                    'null' => [
                        'driver' => 'null',
                    ],
                ],
            ];

        }

    }

    /**
     * Register the native filesystem implementation.
     *
     * @return void
     */
    protected function registerNativeFilesystem()
    {
        $this->app->singleton('files', function () {
            return new Filesystem;
        });
    }

    /**
     * Register the driver based filesystem.
     *
     * @return void
     */
    protected function registerFlysystem()
    {

        $this->registerManager();

        $this->app->singleton('filesystem.disk', function () {
            $app = Container::getInstance();
            return $app['filesystem']->disk($this->getDefaultDriver());
        });

        $this->app->singleton('filesystem.cloud', function () {
            $app = Container::getInstance();
            return $app['filesystem']->disk($this->getCloudDriver());
        });
    }

    /**
     * Register the filesystem manager.
     *
     * @return void
     */
    protected function registerManager()
    {
        $this->app->singleton('filesystem', function () {
            $app = Container::getInstance();
            return new FilesystemManager($app);
        });
    }

    /**
     * Get the default file driver.
     *
     * @return string
     */
    protected function getDefaultDriver()
    {

        $this->app['config']['filesystems.default'] = 'local';

        return $this->app['config']['filesystems.default'];
    }

    /**
     * Get the default cloud based file driver.
     *
     * @return string
     */
    protected function getCloudDriver()
    {
        return $this->app['config']['filesystems.cloud'];
    }
}
