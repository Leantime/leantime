<?php

namespace Leantime\Core\Providers;

use Illuminate\Container\Container;
use Illuminate\Support\Str;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\DynamicComponent;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Engines\FileEngine;
use Illuminate\View\Engines\PhpEngine;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;
use Illuminate\View\ViewServiceProvider;
use Leantime\Core\Support\PathManifestRepository;

class Views extends ViewServiceProvider
{
    protected $viewPaths;

    protected $pathRepo;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

        $this->app['config']->set('view.compiled', storage_path('framework/views'));
        $this->app['config']->set('view.cache', true);
        $this->app['config']->set('view.compiled_extension', 'php');

        $this->registerFactory();
        $this->registerViewFinder();
        $this->registerBladeCompiler();
        $this->registerEngineResolver();

        $this->app->terminating(static function () {
            \Illuminate\View\Component::flushCache();
        });
    }

    /**
     * Register the view environment.
     *
     * @return void
     */
    public function registerFactory()
    {
        $this->app->singleton('view', function ($app) {
            // Next we need to grab the engine resolver instance that will be used by the
            // environment. The resolver will be used by an environment to get each of
            // the various engine implementations such as plain PHP or Blade engine.
            $resolver = $app['view.engine.resolver'];

            $finder = $app['view.finder'];

            $factory = $this->createFactory($resolver, $finder, $app['events']);

            //Backwards compatible view engine resolver
            array_map(fn ($ext) => $factory->addExtension($ext, 'php'), ['inc.php', 'sub.php', 'tpl.php']);

            // reprioritize blade
            //Use blade engine for all things blade
            $factory->addExtension('blade.php', 'blade');

            // We will also set the container instance on this view environment since the
            // view composers may be classes registered in the container, which allows
            // for great testable, flexible composers for the application developer.
            $factory->setContainer(app());

            $factory->share('app', $app);

            //Find and set composers
            $composers = $this->getComposerPaths();
            foreach ($composers as $key => $composerClass) {
                if (
                    (is_subclass_of($composerClass, \Leantime\Core\UI\Composer::class)
                    || is_subclass_of($composerClass, \Leantime\Core\Controller\Composer::class))
                    &&

                    ! (new \ReflectionClass($composerClass))->isAbstract()
                ) {
                    $factory->composer($composerClass::$views, $composerClass);
                }
            }

            $app->terminating(static function () {
                \Illuminate\View\Component::forgetFactory();
            });

            return $factory;
        });
    }

    /**
     * Create a new Factory Instance.
     *
     * @param  \Illuminate\View\Engines\EngineResolver  $resolver
     * @param  \Illuminate\View\ViewFinderInterface  $finder
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return \Illuminate\View\Factory
     */
    protected function createFactory($resolver, $finder, $events)
    {
        return new Factory($resolver, $finder, $events);
    }

    /**
     * Register the view finder implementation.
     *
     * @return void
     */
    public function registerViewFinder()
    {
        $this->app->bind('view.finder', function ($app) {

            $fileViewFinder = new FileViewFinder($app['files'], []);

            $this->viewPaths = $this->getViewPaths();

            array_map([$fileViewFinder, 'addNamespace'], array_keys($this->viewPaths), array_values($this->viewPaths));

            return $fileViewFinder;
        });
    }

    /**
     * Register the Blade compiler implementation.
     *
     * @return void
     */
    public function registerBladeCompiler()
    {
        $this->app->singleton('blade.compiler', function ($app) {
            $compiler = new BladeCompiler(
                $app['files'],
                $app['config']['view.compiled'],
                $app->basePath(),
                $app['config']->get('view.cache', true),
                $app['config']->get('view.compiled_extension', 'php'),
            );

            if (! $this->viewPaths) {
                $this->viewPaths = $this->getViewPaths();
            }

            $namespaces = array_keys($this->viewPaths);
            array_map(
                [$compiler, 'anonymousComponentNamespace'],
                array_map(fn ($namespace) => "$namespace::components", $namespaces),
                $namespaces
            );

            return tap($compiler, function ($blade) {
                $blade->component('dynamic-component', DynamicComponent::class);
            });
        });
    }

    /**
     * Register the engine resolver instance.
     *
     * @return void
     */
    public function registerEngineResolver()
    {
        $this->app->singleton('view.engine.resolver', function () {
            $resolver = new EngineResolver;

            // Next, we will register the various view engines with the resolver so that the
            // environment will resolve the engines needed for various views based on the
            // extension of view file. We call a method for each of the view's engines.
            foreach (['file', 'php', 'blade'] as $engine) {
                $this->{'register'.ucfirst($engine).'Engine'}($resolver);
            }

            return $resolver;
        });
    }

    /**
     * Register the file engine implementation.
     *
     * @param  \Illuminate\View\Engines\EngineResolver  $resolver
     * @return void
     */
    public function registerFileEngine($resolver)
    {
        $resolver->register('file', function () {
            return new FileEngine(Container::getInstance()->make('files'));
        });
    }

    /**
     * Register the PHP engine implementation.
     *
     * @param  \Illuminate\View\Engines\EngineResolver  $resolver
     * @return void
     */
    public function registerPhpEngine($resolver)
    {
        $resolver->register('php', function () {
            return new PhpEngine(Container::getInstance()->make('files'));
        });
    }

    /**
     * Register the Blade engine implementation.
     *
     * @param  \Illuminate\View\Engines\EngineResolver  $resolver
     * @return void
     */
    public function registerBladeEngine($resolver)
    {
        $resolver->register('blade', function () {
            $app = Container::getInstance();

            $compiler = new CompilerEngine(
                $app->make('blade.compiler'),
                $app->make('files'),
            );

            $app->terminating(static function () use ($compiler) {
                $compiler->forgetCompiledOrNotExpired();
            });

            return $compiler;
        });
    }

    public function getComposerPaths()
    {
        $pathRepo = app()->make(PathManifestRepository::class);

        if ($viewPaths = $pathRepo->loadManifest('composerPaths')) {
            return $viewPaths;
        }

        $storePaths = $this->discoverComposerPaths();

        $viewPaths = $pathRepo->writeManifest('composerPaths', $storePaths);

        return $viewPaths;
    }

    private function discoverComposerPaths()
    {
        $appComposerClasses = collect(glob(APP_ROOT.'/app/Views/Composers/*.php'))
            ->concat(glob(APP_ROOT.'/app/Domain/*/Composers/*.php'));

        $enabledPlugins = $this->app->make(\Leantime\Domain\Plugins\Services\Plugins::class)->getEnabledPlugins();
        $pluginComposerClasses = collect($enabledPlugins)
            ->map(fn ($plugin) => glob(APP_ROOT.'/app/Plugins/'.$plugin->foldername.'/Composers/*.php'))
            ->flatten();

        $composerList = $appComposerClasses
            ->concat($pluginComposerClasses)
            ->map(fn ($filepath) => Str::of($filepath)
                ->replace([APP_ROOT.'/app/', '.php'], ['', '', ''])
                ->replace('/', '\\')
                ->start($this->app->getNamespace())
                ->toString())
            ->all();

        return $composerList;
    }

    public function boot() {}

    public function getViewPaths()
    {
        $pathRepo = app()->make(PathManifestRepository::class);

        if ($viewPaths = $pathRepo->loadManifest('viewPaths')) {
            return $viewPaths;
        }

        $storePaths = $this->discoverViewPaths();

        $viewPaths = $pathRepo->writeManifest('viewPaths', $storePaths);

        return $viewPaths;
    }

    private function discoverViewPaths()
    {
        $domainPaths = collect(glob($this->app->basePath().'/app/Domain/*'))
            ->mapWithKeys(function ($path) {
                if (is_dir($path.'/Templates')) {
                    return [
                        $basename = strtolower(basename($path)) => [
                            "$path/Templates",
                        ],
                    ];
                }

                return [];
            });

        /** @var \Leantime\Domain\Plugins\Services\Plugins $pluginService */
        $pluginService = $this->app->make(\Leantime\Domain\Plugins\Services\Plugins::class);

        //We are in discovery mode so enabled plugins should be cleared for new plugins to show up
        //Otherwise the data comes from viewPaths manifest and doesn't need to be pulled in
        $pluginService->clearCache();
        $plugins = collect($pluginService->getEnabledPlugins());

        $pluginPaths = $plugins->mapWithKeys(function ($plugin) use ($domainPaths) {
            //Catch issue when plugins are cached on load but autoloader is not quite done loading.
            //Only happens because the plugin objects are stored in session and the unserialize is not keeping up.
            //Clearing session cache in that case.
            //@TODO: Check on callstack to make sure autoload loads before sessions
            if (! is_a($plugin, '__PHP_Incomplete_Class')) {
                if ($domainPaths->has($basename = strtolower($plugin->foldername))) {
                    //Clear cache, something is up
                    //session()->forget("enabledPlugins");
                    return [];
                }

                if ($plugin->format == 'phar') {
                    $path = 'phar://'.APP_ROOT.'/app/Plugins/'.$plugin->foldername.'/'.$plugin->foldername.'.phar/Templates';
                } else {
                    $path = APP_ROOT.'/app/Plugins/'.$plugin->foldername.'/Templates';
                }

                return [$basename => [$path]];
            }

            //session()->forget("enabledPlugins");
            return [];
        });

        $storePaths = $domainPaths
            ->merge($pluginPaths)
            ->merge(['global' => APP_ROOT.'/app/Views/Templates'])
            ->merge(['__components' => $this->app['config']->get('view.compiled')])
            ->all();

        return $storePaths;
    }
}
