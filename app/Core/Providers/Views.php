<?php

namespace Leantime\Core\Providers;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\Cache;
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
    /**
     * View paths
     * @var array $viewPaths
     */
    protected $viewPaths;

    /**
     * Path manifest repository
     * @var PathManifestRepository $pathRepo
     */
    protected PathManifestRepository $pathRepo;

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

        $this->app->terminating(static fn () => \Illuminate\View\Component::flushCache());
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
            foreach ($composers as $composerClass) {
                if (
                    is_subclass_of($composerClass, \Leantime\Core\UI\Composer::class)
                    && ! (new \ReflectionClass($composerClass))->isAbstract()
                ) {
                    $factory->composer($composerClass::$views, $composerClass);
                }
            }

            $app->terminating(static fn () => \Illuminate\View\Component::forgetFactory());

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

            collect($this->viewPaths)
            ->each(fn ($path, $namespace) => $fileViewFinder->addNamespace($namespace, $path));

            return $fileViewFinder;
        });
    }

    /**
     * Register the Blade compiler implementation.
     *
     * @return void
     */
    public function registerBladeCompiler(): void
    {
        $this->app->singleton('blade.compiler', fn ($app) => tap(new BladeCompiler(
            $app['files'],
            $app['config']['view.compiled'],
            $app->basePath(),
            $app['config']->get('view.cache', true),
            $app['config']->get('view.compiled_extension', 'php'),
        ), function ($compiler) {
            // Register anonymous components
            if (! $this->viewPaths) {
                $this->viewPaths = $this->getViewPaths();
            }

            collect(array_keys($this->viewPaths))
                ->map(fn ($namespace) => "$namespace::components")
                ->each([$compiler, 'anonymousComponentNamespace']);

            // Register class component namespaces
            $namespaces = $this->getComponentNamespaces();
            foreach ($namespaces as $namespace => $component) {
                $compiler->componentNamespace($namespace, $component);
            }

            // Register class components
            foreach ($this->getComponentClasses() as $class) {
                foreach ($namespaces as $namespace) {
                    if (Str::startsWith($class, $namespace)) {
                        $componentNamespace = array_search($namespace, $namespaces) . '::';
                    }
                }

                $componentAlias = ($componentNamespace ?? '')
                    . Str::of($class)
                    ->after('Components\\')
                    ->ltrim('\\')
                    ->explode('\\')
                    ->map(fn ($part) => Str::kebab($part))
                    ->join('.');

                $compiler->component($componentAlias, $class);
            }

            // Register the dynamic component
            $compiler->component('dynamic-component', DynamicComponent::class);
        }));
    }

    /**
     * Register the engine resolver instance.
     *
     * @return void
     */
    public function registerEngineResolver()
    {
        $this->app->singleton('view.engine.resolver', fn () => tap(
            new EngineResolver,
            /**
             * Register the various view engines with the resolver so that the
             * environment will resolve the engines needed for various views based on the
             * extension of view file. We call a method for each of the view's engines.
             */
            fn ($resolver) => collect(['File', 'Php', 'Blade'])->each(
                fn ($engine) => $this->{"register{$engine}Engine"}($resolver)
            ),
        ));
    }

    /**
     * Register the file engine implementation.
     *
     * @param  \Illuminate\View\Engines\EngineResolver  $resolver
     * @return void
     */
    public function registerFileEngine($resolver)
    {
        $resolver->register(
            'file',
            fn () => new FileEngine(Container::getInstance()->make('files'))
        );
    }

    /**
     * Register the PHP engine implementation.
     *
     * @param  \Illuminate\View\Engines\EngineResolver  $resolver
     * @return void
     */
    public function registerPhpEngine($resolver): void
    {
        $resolver->register(
            'php',
            fn () => new PhpEngine(Container::getInstance()->make('files'))
        );
    }

    /**
     * Register the Blade engine implementation.
     *
     * @param  \Illuminate\View\Engines\EngineResolver  $resolver
     * @return void
     */
    public function registerBladeEngine($resolver): void
    {
        $resolver->register('blade', function () {
            /** @var \Leantime\Core\Application $app */
            $app = Container::getInstance();

            $compiler = new CompilerEngine(
                $app->make('blade.compiler'),
                $app->make('files'),
            );

            $app->terminating(static fn () => $compiler->forgetCompiledOrNotExpired());

            return $compiler;
        });
    }

    public function boot() {}

    /**
     * Discover paths
     * @param string $relativePattern
     * @param string $type
     * @param bool $returnClass
     * @return array
     * @throws BindingResolutionException
     */
    public function discoverPaths(
        string $relativePattern,
        string $type = '',
        bool $returnClass = false
    ): array {
        $domainPaths = collect(glob(APP_ROOT.'/app/Views/'.$relativePattern))
            ->concat(glob(APP_ROOT.'/app/Domain/*/'.$relativePattern));

        $pluginsService = $this->app->make(\Leantime\Domain\Plugins\Services\Plugins::class);

        /**
         * We are in discovery mode so enabled plugins should be cleared for new plugins to show up
         * Otherwise the data comes from viewPaths manifest and doesn't need to be pulled in
         */
        $pluginsService->clearCache();

        $pluginPaths = collect($pluginsService->getEnabledPlugins())
            ->map(function ($plugin) use ($relativePattern) {
                /**
                 * Catch issue when plugins are cached on load but autoloader is not quite done loading.
                 * Only happens because the plugin objects are stored in session and the unserialize is not keeping up.
                 * Clearing session cache in that case.
                 * @TODO: Check on callstack to make sure autoload loads before sessions
                 */
                if (is_a($plugin, '__PHP_Incomplete_Class')) {
                    return [];
                }

                if ($plugin->format !== 'phar') {
                    return glob(APP_ROOT."/app/Plugins/{$plugin->foldername}/{$relativePattern}") ?: [];
                }

                $pharPath = APP_ROOT."/app/Plugins/{$plugin->foldername}/{$plugin->foldername}.phar";

                if (! file_exists($pharPath)) {
                    return [];
                }

                try {
                    return phar_glob("phar://{$pharPath}/{$relativePattern}");
                } catch (\Exception) {
                    return [];
                }
            })
            ->flatten();

        $paths = $domainPaths->concat($pluginPaths);

        $classes = $paths->map(fn ($filepath) => Str::of($filepath)
            ->replace([APP_ROOT.'/app/', '.php'], ['', '', ''])
            ->replace('/', '\\')
            ->start($this->app->getNamespace())
            ->toString());

        return match (true) {
            $returnClass && ! empty($type) => $classes->filter(fn ($class) => is_subclass_of($class, $type))->all(),
            $returnClass && empty($type) => $classes->all(),
            ! $returnClass && ! empty($type) => $paths->intersectByKeys($classes->filter(fn ($class) => is_subclass_of($class, $type)))->all(),
            ! $returnClass && empty($type) => $paths->all(),
        };
    }

    /**
     * Get view paths
     * @return array
     * @throws BindingResolutionException
     */
    public function getViewPaths(): array
    {
        $this->pathRepo ??= app(PathManifestRepository::class);

        if ($viewPaths = $this->pathRepo->loadManifest('viewPaths')) {
            return $viewPaths;
        }

        $storePaths = collect($this->discoverPaths('Templates'))
            ->mapWithKeys(function ($path) {
                if (! is_dir($path)) {
                    return [];
                }

                return [
                    basename(Str::of($path)->beforeLast('/')->lower()->toString()) => $path
                ];
            });

        $storePaths = $storePaths
            ->merge(['global' => $storePaths['views']])
            ->merge(['views' => null])
            ->merge(['__components' => $this->app['config']->get('view.compiled')])
            ->filter()
            ->all();

        $viewPaths = $this->pathRepo->writeManifest('viewPaths', $storePaths);

        return $viewPaths;
    }

    /**
     * Get composer paths
     * @return array
     * @throws BindingResolutionException
     */
    public function getComposerPaths(): array
    {
        $this->pathRepo ??= app(PathManifestRepository::class);

        if ($viewPaths = $this->pathRepo->loadManifest('composerPaths')) {
            return $viewPaths;
        }

        $storePaths = $this->discoverPaths(
            'Composers/*.php',
            \Leantime\Core\UI\Composer::class,
            true
        );

        $viewPaths = $this->pathRepo->writeManifest('composerPaths', $storePaths);

        return $viewPaths;
    }

    /**
     * Get component namespaces
     * @return array
     * @throws BindingResolutionException
     */
    public function getComponentNamespaces(): array
    {
        $this->pathRepo ??= app(PathManifestRepository::class);

        if (
            Cache::store('installation')->has('componentNamespaces')
            && ! empty($namespaces = Cache::store('installation')->get('componentNamespaces'))
        ) {
            return $namespaces;
        }

        $storePaths = collect($this->discoverPaths('Components', '', true))
            ->mapWithKeys(fn ($class) => [
                Str::of($class)->beforeLast('\\')->afterLast('\\')->lower()->toString() => $class
            ]);

        $namespaces = $storePaths
            ->merge(['global' => $storePaths['views']])
            ->merge(['views' => null])
            ->filter()
            ->all();

        Cache::store('installation')->put('componentNamespaces', $namespaces);

        return $namespaces;
    }

    /**
     * Get component classes
     * @return array
     * @throws BindingResolutionException
     */
    public function getComponentClasses(): array
    {
        $this->pathRepo ??= app(PathManifestRepository::class);

        if (
            Cache::store('installation')->has('componentClasses')
            && ! empty($classes = Cache::store('installation')->get('componentClasses'))
        ) {
            return $classes;
        }

        $classes = $this->discoverPaths(
            'Components/**/*.php',
            \Illuminate\View\Component::class,
            true
        );

        Cache::store('installation')->put('componentClasses', $classes);

        return $classes;
    }
}
