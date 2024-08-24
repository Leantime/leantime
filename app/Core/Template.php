<?php

namespace Leantime\Core;

use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Compilers\Compiler;
use Illuminate\View\Compilers\CompilerInterface;
use Illuminate\View\DynamicComponent;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Engines\PhpEngine;
use Illuminate\View\FileViewFinder;
use Illuminate\View\View;
use Illuminate\View\ViewFinderInterface;
use Leantime\Core\Bootstrap\Application;
use Leantime\Core\Configuration\AppSettings;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Controller\Composer;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use ReflectionClass;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Template class - Template routing
 *
 * @package leantime
 * @subpackage core
 */
class Template
{
    use DispatchesEvents;

    /** @var array - vars that are set in the action */
    private array $vars = array();

    /** @var string */
    private string $notifcation = '';

    /**
     * @var string
     */
    private string $notificationType = '';

    /** @var string */
    private string $hookContext = '';

    /** @var string */
    public string $tmpError = '';

    /** @var string */
    public string $template = '';

    /** @var array */
    public array $picture = array(
        'calendar' => 'fa-calendar',
        'clients' => 'fa-people-group',
        'dashboard' => 'fa-th-large',
        'files' => 'fa-picture',
        'leads' => 'fa-signal',
        'messages' => 'fa-envelope',
        'projects' => 'fa-bar-chart',
        'setting' => 'fa-cogs',
        'tickets' => 'fa-pushpin',
        'timesheets' => 'fa-table',
        'users' => 'fa-people-group',
        'default' => 'fa-off',
    );


    /**
     * __construct - get instance of frontcontroller
     *
     * @param Theme           $theme
     * @param Language        $language
     * @param Frontcontroller $frontcontroller
     * @param IncomingRequest $incomingRequest
     * @param Environment     $config
     * @param AppSettings     $settings
     * @param AuthService     $login
     * @param Roles           $roles
     * @param Factory|null    $viewFactory
     * @param Compiler|null   $bladeCompiler
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function __construct(
        /** @var Theme */
        private Theme $theme,

        /** @var Language */
        public Language $language,

        /** @var Frontcontroller */
        public Frontcontroller $frontcontroller,

        /** @var IncomingRequest */
        public IncomingRequest $incomingRequest,

        /** @var Environment */
        private Environment $config,

        /** @var AppSettings */
        private AppSettings $settings,

        /** @var AuthService */
        private AuthService $login,

        /** @var Roles */
        private Roles $roles,

        /** @var \Illuminate\View\Factory|null */
        public ?Factory $viewFactory = null,

        /** @var CompilerInterface|null */
        private ?CompilerInterface $bladeCompiler = null,
    ) {

        if (! is_null($this->viewFactory) && ! is_null($this->bladeCompiler)) {
            return;

        }

        app()->call([$this, 'setupBlade']);
        $this->attachComposers();
        $this->setupDirectives();
        $this->setupGlobalVars();
    }

    /**
     * Create View Factory capable of rendering PHP and Blade templates
     *
     * @param Application $app
     * @param Dispatcher  $eventDispatcher
     * @return void
     * @throws BindingResolutionException
     */
    public function setupBlade(
        Application $app,
        Dispatcher $eventDispatcher
    ): void {
        // ComponentTagCompiler Expects the Foundation\Application Implmentation, let's trick it and give it the container.
        $app->instance(\Illuminate\Contracts\Foundation\Application::class, $app::getInstance());

        // View/Component createBladeViewFromString method needs to access the view compiled path, expects it to be attached to config
        $this->config->set('view.compiled', APP_ROOT . '/cache/views');

        // Find Template Paths
        if (!session()->has("template_paths") || $this->config->debug) {
            $domainPaths = collect(glob(APP_ROOT . '/app/Domain/*'))
                ->mapWithKeys(fn ($path) => [
                    $basename = strtolower(basename($path)) => [
                        APP_ROOT . '/custom/Domain/' . $basename . '/Templates',
                        "$path/Templates",
                    ],
                ]);

            $plugins = collect(app()->make(\Leantime\Domain\Plugins\Services\Plugins::class)->getEnabledPlugins());

            $pluginPaths = $plugins->mapWithKeys(function ($plugin) use ($domainPaths) {

                //Catch issue when plugins are cached on load but autoloader is not quite done loading.
                //Only happens because the plugin objects are stored in session and the unserialize is not keeping up.
                //Clearing session cache in that case.
                //@TODO: Check on callstack to make sure autoload loads before sessions
                if (!is_a($plugin, '__PHP_Incomplete_Class')) {
                    if ($domainPaths->has($basename = strtolower($plugin->foldername))) {
                        report("Plugin $basename conflicts with domain");
                        //Clear cache, something is up
                        session()->forget("enabledPlugins");
                        return [];
                    }

                    if ($plugin->format == "phar") {
                        $path = 'phar://' . APP_ROOT . '/app/Plugins/' . $plugin->foldername . '/' . $plugin->foldername . '.phar/Templates';
                    } else {
                        $path = APP_ROOT . '/app/Plugins/' . $plugin->foldername . '/Templates';
                    }

                    return [$basename => $path];
                }

                session()->forget("enabledPlugins");
                return [];
            });

           $storePaths = $domainPaths
                ->merge($pluginPaths)
                ->merge(['global' => APP_ROOT . '/app/Views/Templates'])
                ->merge(['__components' => $this->config->get('view.compiled')])
                ->all();

            session(["template_paths" => $storePaths]);
        }

        // Setup Blade Compiler
        $app->singleton(
            CompilerInterface::class,
            function ($app) {

                $bladeCompiler = new BladeCompiler(
                    $app->make(Filesystem::class),
                    $this->config->get('view.compiled'),
                );

                $namespaces = array_keys(session("template_paths"));
                array_map(
                    [$bladeCompiler, 'anonymousComponentNamespace'],
                    array_map(fn ($namespace) => "$namespace::components", $namespaces),
                    $namespaces
                );

                return tap($bladeCompiler, function ($blade) {
                    $blade->component('dynamic-component', DynamicComponent::class);
                });
            }
        );
        $app->alias(CompilerInterface::class, 'blade.compiler');

        // Register Blade Engines
        $app->singleton(
            EngineResolver::class,
            function ($app) {
                $viewResolver = new EngineResolver();
                $viewResolver->register('blade', fn () => $app->make(CompilerEngine::class));
                $viewResolver->register('php', fn () => $app->make(PhpEngine::class));
                return $viewResolver;
            }
        );
        $app->alias(EngineResolver::class, 'view.engine.resolver');

        // Setup View Finder
        $app->singleton(
            ViewFinderInterface::class,
            function ($app) {
                $viewFinder = $app->make(FileViewFinder::class, ['paths' => []]);
                array_map([$viewFinder, 'addNamespace'], array_keys(session("template_paths")), array_values(session("template_paths")));
                return $viewFinder;
            }
        );
        $app->alias(ViewFinderInterface::class, 'view.finder');

        // Setup EventDispatcher Dispatcher
        $app->bind(\Illuminate\Contracts\Events\Dispatcher::class, Dispatcher::class);

        // Setup View Factory
        $app->singleton(
            Factory::class,
            function ($app) {
                $viewFactory = $app->make(\Illuminate\View\Factory::class);
                array_map(fn ($ext) => $viewFactory->addExtension($ext, 'php'), ['inc.php', 'sub.php', 'tpl.php']);
                // reprioritize blade
                $viewFactory->addExtension('blade.php', 'blade');
                $viewFactory->setContainer($app);
                return $viewFactory;
            }
        );
        $app->alias(Factory::class, 'view');

        $this->bladeCompiler = $app->make(CompilerInterface::class);
        $this->viewFactory = $app->make(Factory::class);
    }

    /**
     * attachComposers - attach view composers
     *
     * @return void
     * @throws \ReflectionException
     */
    public function attachComposers(): void
    {
        if (!session()->has("composers") || $this->config->debug) {
            $customComposerClasses = collect(glob(APP_ROOT . '/custom/Views/Composers/*.php'))
                ->concat(glob(APP_ROOT . '/custom/Domain/*/Composers/*.php'));

            $appComposerClasses = collect(glob(APP_ROOT . '/app/Views/Composers/*.php'))
                ->concat(glob(APP_ROOT . '/app/Domain/*/Composers/*.php'));

            $pluginComposerClasses = collect(app()->make(\Leantime\Domain\Plugins\Services\Plugins::class)->getEnabledPlugins())
                ->map(fn ($plugin) => glob(APP_ROOT . '/app/Plugins/' . $plugin->foldername . '/Composers/*.php'))
                ->flatten();

            $testers = $customComposerClasses->map(fn ($path) => str_replace('/custom/', '/app/', $path));

            $stockComposerClasses = $appComposerClasses
                ->concat($pluginComposerClasses)
                ->filter(fn ($composerClass) => ! $testers->contains($composerClass));

            $storeComposers = $customComposerClasses
                ->concat($stockComposerClasses)
                ->map(fn ($filepath) => Str::of($filepath)
                    ->replace([APP_ROOT . '/app/', APP_ROOT . '/custom/', '.php'], ['', '', ''])
                    ->replace('/', '\\')
                    ->start(app()->getNamespace())
                    ->toString())
                ->all();

            session(["composers" => $storeComposers]);
        }

        foreach (session("composers") as $composerClass) {
            if (
                is_subclass_of($composerClass, Composer::class) &&
                ! (new ReflectionClass($composerClass))->isAbstract()
            ) {
                $this->viewFactory->composer($composerClass::$views, $composerClass);
            }
        }
    }

    /**
     * setupDirectives - setup blade directives
     *
     * @return void
     */
    public function setupDirectives(): void
    {
        $this->bladeCompiler->directive(
            'dispatchEvent',
            fn ($args) => "<?php \$tpl->dispatchTplEvent($args); ?>",
        );

        $this->bladeCompiler->directive(
            'dispatchFilter',
            fn ($args) => "<?php echo \$tpl->dispatchTplFilter($args); ?>",
        );

        $this->bladeCompiler->directive(
            'spaceless',
            fn ($args) => "<?php ob_start(); ?>",
        );

        $this->bladeCompiler->directive(
            'endspaceless',
            fn ($args) => "<?php echo preg_replace('/>\\s+</', '><', ob_get_clean()); ?>",
        );

        $this->bladeCompiler->directive(
            'formatDate',
            fn ($args) => "<?php echo format($args)->date(); ?>",
        );

        $this->bladeCompiler->directive(
            'formatTime',
            fn ($args) => "<?php echo format($args)->time(); ?>",
        );

        $this->bladeCompiler->directive(
            'displayNotification',
            fn ($args) => "<?php echo \$tpl->displayNotification(); ?>",
        );
    }

    /**
     * setupGlobalVars - setup global vars
     *
     * @return void
     */
    public function setupGlobalVars(): void
    {
        $this->viewFactory->share([
            'frontController' => $this->frontcontroller,
            'config' => $this->config,
            /** @todo remove settings after renaming all uses to appSettings */
            'settings' => $this->settings,
            'appSettings' => $this->settings,
            'login' => $this->login,
            'roles' => $this->roles,
            'language' => $this->language,

        ]);
    }

    /**
     * assign - assign variables in the action for template
     *
     * @param string $name  Name of variable
     * @param mixed  $value Value of variable
     * @return void
     */
    public function assign(string $name, mixed $value): void
    {
        /**
         * Filter to access template variable names after they have been assigned
         * @var mixed $value The current value of the variable.
         */
        $value = self::dispatch_filter("var.$name", $value);

        $this->vars[$name] = $value;
    }

    /**
     * setNotification - assign errors to the template
     *
     * @param string $msg
     * @param string $type
     * @param string $event_id as a string for further identification
     * @return void
     */
    public function setNotification(string $msg, string $type, string $event_id = ''): void
    {
        session(["notification" => $msg]);
        session(["notificationType" => $type]);
        session(["event_id" => $event_id]);
    }

    /**
     * Set a flag to indicate that confetti should be displayed.
     * Will be displayed next time a notification is displayed
     *
     * @return void confetti, duh
     */
    public function sendConfetti()
    {
        session(["confettiInYourFace" => true]);
    }

    /**
     * getTemplatePath - Find template in custom and src directories
     *
     * @access public
     * @param string $namespace The namespace the template is for.
     * @param string $path      The path to the template.
     * @return string Full template path or false if file does not exist
     * @throws Exception If template not found.
     */
    public function getTemplatePath(string $namespace, string $path): string
    {
        if ($namespace == '' || $path == '') {
            throw new Exception("Both namespace and path must be provided");
        }

        $namespace = strtolower($namespace);
        $fullpath = self::dispatch_filter(
            "template_path__{$namespace}_{$path}",
            "$namespace::$path",
            [
                'namespace' => $namespace,
                'path' => $path,
            ]
        );

        if ($this->viewFactory->exists($fullpath)) {
            return $fullpath;
        }

        throw new Exception("Template $fullpath not found");
    }

    /**
     * gives HTMX response
     *
     * @param string $viewPath The blade view path.
     * @param string $fragment The fragment key.
     * @return Response
     */
    public function displayFragment(string $viewPath, string $fragment = ''): Response
    {
        $layout = $this->confirmLayoutName('blank', ! empty($fragment) ? "$viewPath.fragment" : $viewPath);
        $this->viewFactory->share(['tpl' => $this]);
        /** @var View $view */
        $view = $this->viewFactory->make($viewPath, array_merge($this->vars, ['layout' => $layout]));
        return new Response($view->fragmentIf(! empty($fragment), $fragment));
    }

    /**
     * display - display template from folder template including main layout wrapper
     *
     * @access public
     * @param string $template
     * @param string $layout
     * @param int    $responseCode
     * @return Response
     * @throws Exception
     */
    public function display(string $template, string $layout = "app", int $responseCode = 200): Response
    {
        $template = self::dispatch_filter('template', $template);
        $template = self::dispatch_filter("template.$template", $template);

        $this->template = $template;

        $layout = $this->confirmLayoutName($layout, $template);

        $action = Frontcontroller::getActionName($template);
        $module = Frontcontroller::getModuleName($template);

        $loadFile = $this->getTemplatePath($module, $action);

        $this->hookContext = "tpl.$module.$action";
        $this->viewFactory->share(['tpl' => $this]);

        /** @var View $view */
        $view = $this->viewFactory->make($loadFile);

        /** @todo this can be reduced to just the 'if' code after removal of php template support */
        if ($view->getEngine() instanceof CompilerEngine) {
            $view->with(array_merge(
                $this->vars,
                ['layout' => $layout]
            ));
        } else {
            $view = $this->viewFactory->make($layout, array_merge(
                $this->vars,
                ['module' => strtolower($module), 'action' => $action]
            ));
        }

        $content = $view->render();
        $content = self::dispatch_filter('content', $content);
        $content = self::dispatch_filter("content.$template", $content);

        return new Response($content, $responseCode);
    }


    /**
     * Confirm the layout name based on the provided parameters.
     *
     * @param string $layoutName The layout name to be confirmed.
     * @param string $template   The template name associated with the layout.
     * @return bool|string The confirmed layout name, or false if not found.
     */
    protected function confirmLayoutName(string $layoutName, string $template): bool|string
    {
        $layout = htmlspecialchars($layoutName);
        $layout = self::dispatch_filter("layout", $layout);
        $layout = self::dispatch_filter("layout.$template", $layout);

        $layout = $this->getTemplatePath('global', "layouts.$layout");

        return $layout;
    }

    /**
     * Display JSON content with an optional response code.
     *
     * @param array|object|string $jsonContent The JSON content to be displayed.
     * @param int                 $statusCode  The HTTP response code to be returned (default: 200).
     * @return Response The response object after displaying the JSON content.
     */
    public function displayJson(array|object|string $jsonContent, int $statusCode = 200): Response
    {
        $response = new Response(json_encode(['error' => 'Invalid Json']), 500);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');

        if (is_array($jsonContent) || is_object($jsonContent)) {
            $collection = collect($jsonContent);
            $jsonContent = $collection->toJson();

            if (json_last_error() !== JSON_ERROR_NONE) {
                return $response;
            }
        }

        $response = $response->setContent($jsonContent);
        $response = $response->setStatusCode($statusCode);

        return $response;
    }

    /**
     * Display a partial template with an optional response code.
     *
     * @param string $template     The path to the partial template file.
     * @param int    $responseCode The HTTP response code to be returned (default: 200).
     * @return Response The response object after displaying the partial template.
     */
    public function displayPartial(string $template, int $responseCode = 200): Response
    {
        return $this->display($template, 'blank', $responseCode);
    }

    /**
     * get - get assigned values
     *
     * @access public
     * @param string $name
     * @return array
     */
    public function get(string $name): mixed
    {
        if (!isset($this->vars[$name])) {
            return null;
        }

        return $this->vars[$name];
    }

    /**
     * getAll - get all assigned values
     *
     * @return array
     **/
    public function getAll(): array
    {
        return $this->vars ?? [];
    }

    /**
     * getNotification - pulls notification from the current session
     *
     * @access public
     * @return array
     */
    public function getNotification(): array
    {
        if (session()->exists("notificationType") && session()->exists("notification")) {
            $event_id = session("event_id") ?? '';
            return array('type' => session("notificationType"), 'msg' => session("notification"), 'event_id' => $event_id);
        } else {
            return array('type' => "", 'msg' => "", 'event_id' => "");
        }
    }

    /**
     * displaySubmodule - display a submodule for a given module
     *
     * @access public
     * @param string $alias
     * @return void
     * @throws Exception
     */
    public function displaySubmodule(string $alias): void
    {
        if (! str_contains($alias, '-')) {
            throw new Exception("Submodule alias must be in the format module-submodule");
        }

        [$module, $submodule] = explode("-", $alias);

        $relative_path = $this->getTemplatePath($module, "submodules.$submodule");

        echo $this->viewFactory->make($relative_path, array_merge($this->vars, ['tpl' => $this]))->render();
    }

    /**
     * displayNotification - display notification
     *
     * @access public
     * @return string
     * @throws BindingResolutionException
     */
    public function displayNotification(): string
    {
        $notification = '';
        $note = $this->getNotification();
        $language = $this->language;
        $message_id = $note['msg'];

        $message = self::dispatch_filter(
            'message',
            $language->__($message_id),
            $note
        );
        $message = self::dispatch_filter(
            "message_{$message_id}",
            $message,
            $note
        );

        if (!empty($note) && $note['msg'] != '' && $note['type'] != '') {
            $notification .= app('blade.compiler')::render(
                '<script type="text/javascript">jQuery.growl({message: "{{ $message }}", style: "{{ $style }}"});</script>',
                [
                    'message' => $message,
                    'style' => $note['type'],
                ]
            );

            self::dispatch_event("notification_displayed", $note);

            session(["notification" => ""]);
            session(["notificationType" => ""]);
            session(["event_id" => ""]);
        }

        if (session()->exists("confettiInYourFace") && session("confettiInYourFace") === true) {
            $notification .= app('blade.compiler')::render(
                '<script type="text/javascript">confetti({
                     spread: 70,
                     origin: { y: 1.2 },
                     disableForReducedMotion: true
                    });
                  </script>',
                []
            );

            session(["confettiInYourFace" => false]);
        }

        return $notification;
    }

    /**
     * getToggleState - retrieves the toggle state of a submenu by name from the session
     *
     * @access  public
     * @param string $name - the name of the submenu toggle
     * @return  string - the toggle state of the submenu (either "true" or "false")
     */
    public function getToggleState(string $name): string
    {
        if (session()->exists("usersettings.submenuToggle.".$name)) {
            return session("usersettings.submenuToggle.".$name);
        }

        return false;
    }

    /**
     * displayInlineNotification - display notification
     *
     * @access public
     * @return string
     * @throws BindingResolutionException
     */
    public function displayInlineNotification(): string
    {
        $notification = '';
        $note = $this->getNotification();
        $language = $this->language;
        $message_id = $note['msg'];

        $message = self::dispatch_filter(
            'message',
            $language->__($message_id),
            $note
        );
        $message = self::dispatch_filter(
            "message_{$message_id}",
            $message,
            $note
        );

        if (!empty($note) && $note['msg'] != '' && $note['type'] != '') {
            $notification = app('blade.compiler')::render(
                '<div class="inputwrapper login-alert login-{{ $type }}" style="position: relative;">
                    <div class="alert alert-{{ $type }}" style="padding:15px;" >
                        <strong>{!! $message !!}</strong>
                    </div>
                </div>',
                [
                    'type' => $note['type'],
                    'message' => $message,
                ],
                deleteCachedView: true
            );

            self::dispatch_event("notification_displayed", $note);

            session(["notification" => ""]);
            session(["notificationType" => ""]);
            session(["event_id" => ""]);
        }

        if (session()->exists("confettiInYourFace") && session("confettiInYourFace") === true) {
            $notification .= app('blade.compiler')::render(
                '<script type="text/javascript">confetti({
                        spread: 70,
                        origin: { y: 1.2 },
                        disableForReducedMotion: true
                      });
                      </script>',
                []
            );

            session(["confettiInYourFace" => false]);
        }

        return $notification;
    }

    /**
     * redirect - redirect to a given url
     *
     * @param  string $url
     * @return RedirectResponse
     */
    public function redirect(string $url): RedirectResponse
    {
        return Frontcontroller::redirect($url);
    }

    /**
     * __ - returns a language specific string. wraps language class method
     *
     * @param  string $index
     * @return string
     */
    public function __(string $index): string
    {
        return $this->language->__($index);
    }

    /**
     * e - echos and escapes content
     *
     * @param string|null $content
     * @return void
     */
    public function e(?string $content): void
    {
        $content = $this->convertRelativePaths($content);
        $escaped = $this->escape($content);

        echo $escaped;
    }

    /**
     * escape - escapes content
     *
     * @param string|null $content
     * @return string
     */
    public function escape(?string $content): string
    {
        if (!is_null($content)) {
            $content = $this->convertRelativePaths($content);
            return htmlentities($content);
        }

        return '';
    }

    /**
     * escapeMinimal - escapes content
     *
     * @param string|null $content
     * @return string
     */
    public function escapeMinimal(?string $content): string
    {
        $content = $this->convertRelativePaths($content);
        $config = array(
            'safe' => 1,
            'style_pass' => 1,
            'cdata' => 1,
            'comment' => 1,
            'deny_attribute' => '* -href -style',
            'keep_bad' => 0,
        );

        if (!is_null($content)) {
            return htmLawed($content, array(
                'comments' => 0,
                'cdata' => 0,
                'deny_attribute' => 'on*',
                'elements' => '* -applet -canvas -embed -object -script',
                'schemes' => 'href: aim, feed, file, ftp, gopher, http, https, irc, mailto, news, nntp, sftp, ssh, tel, telnet; style: !; *:file, http, https',
            ));
        }

        return '';
    }

    /**
     * truncate - truncate text
     *
     * @see https://stackoverflow.com/questions/1193500/truncate-text-containing-html-ignoring-tags
     * @author Søren Løvborg <https://stackoverflow.com/users/136796/s%c3%b8ren-l%c3%b8vborg>
     * @access public
     * @param string $html
     * @param int    $maxLength
     * @param string $ending
     * @param bool   $exact
     * @param bool   $considerHtml
     * @return string
     */
    public function truncate(string $html, int $maxLength = 100, string $ending = '(...)', bool $exact = true, bool $considerHtml = false): string
    {
        $printedLength = 0;
        $position = 0;
        $tags = array();
        $isUtf8 = true;
        $truncate = "";
        $html = $this->convertRelativePaths($html);
        // For UTF-8, we need to count multibyte sequences as one character.
        $re = $isUtf8 ? '{</?([a-z]+)[^>]*>|&#?[a-zA-Z0-9]+;|[\x80-\xFF][\x80-\xBF]*}' : '{</?([a-z]+)[^>]*>|&#?[a-zA-Z0-9]+;}';

        while ($printedLength < $maxLength && preg_match($re, $html, $match, PREG_OFFSET_CAPTURE, $position)) {
            list($tag, $tagPosition) = $match[0];

            // Print text leading up to the tag.
            $str = substr($html, $position, $tagPosition - $position);
            if ($printedLength + strlen($str) > $maxLength) {
                $truncate .= substr($str, 0, $maxLength - $printedLength);
                $printedLength = $maxLength;
                break;
            }

            $truncate .= $str;
            $printedLength += strlen($str);
            if ($printedLength >= $maxLength) {
                break;
            }

            if ($tag[0] == '&' || ord($tag) >= 0x80) {
                // Pass the entity or UTF-8 multibyte sequence through unchanged.
                $truncate .= $tag;
                $printedLength++;
            } else {
                // Handle the tag.
                $tagName = $match[1][0];
                if ($tag[1] == '/') {
                    // This is a closing tag.

                    $openingTag = array_pop($tags);
                    assert($openingTag == $tagName); // check that tags are properly nested.

                    $truncate .= $tag;
                } elseif ($tag[strlen($tag) - 2] == '/') {
                    // Self-closing tag.
                    $truncate .= $tag;
                } else {
                    // Opening tag.
                    $truncate .= $tag;
                    $tags[] = $tagName;
                }
            }

            // Continue after the tag.
            $position = $tagPosition + strlen($tag);
        }

        // Print any remaining text.
        if ($printedLength < $maxLength && $position < strlen($html)) {
            $truncate .= sprintf(substr($html, $position, $maxLength - $printedLength));
        }

        // Close any open tags.
        while (!empty($tags)) {
            $truncate .= sprintf('</%s>', array_pop($tags));
        }

        if (strlen($truncate) >= $maxLength) {
            $truncate .= $ending;
        }

        return $truncate;
    }

    /**
     * convertRelativePaths - convert relative paths to absolute paths
     *
     * @access public
     * @param string|null $text
     * @return string|null
     */
    public function convertRelativePaths(?string $text): ?string
    {
        if (is_null($text)) {
            return $text;
        }

        $base = BASE_URL;

        // base url needs trailing /
        $base = rtrim($base, "/") . "/";

        // Replace links
        $text = preg_replace(
            '/<a([^>]*) href="((?!(http|ftp|https|mailto|#))[^"]*)"/',
            "<a\${1} href=\"$base\${2}\"",
            $text
        );

        // Replace images
        $text = preg_replace(
            '/<img([^>]*) src="((?!(http|ftp|https))[^"]*)"/',
            "<img\${1} src=\"$base\${2}\"",
            $text
        );

        // Done
        return $text;
    }

    /**
     * getModulePicture - get module picture
     *
     * @access public
     * @return string
     * @throws BindingResolutionException
     */
    public function getModulePicture(): string
    {
        $module = Frontcontroller::getModuleName($this->template);

        $picture = $this->picture['default'];
        if (isset($this->picture[$module])) {
            $picture = $this->picture[$module];
        }

        return $picture;
    }

    /**
     * patchDownloadUrlToFilenameOrAwsUrl - Replace all local files/get references in <img src=""> tags
     * by either local filenames or AWS URLs that can be accesse without being authenticated
     *
     * Note: This patch is required by the PDF generating engine as it retrieves URL data without being
     * authenticated
     *
     * @access public
     * @param  string $textHtml HTML text, potentially containing <img srv="https://local.domain/files/get?xxxx"> tags
     * @return string  HTML text with the https://local.domain/files/get?xxxx replaced by either full qualified
     *                 local filenames or AWS URLs
     */
    public function patchDownloadUrlToFilenameOrAwsUrl(string $textHtml): string
    {
        $patchedTextHtml = $this->convertRelativePaths($textHtml);

        // TO DO: Replace local files/get
        $patchedTextHtml = $patchedTextHtml;

        return $patchedTextHtml;
    }

    /**
     * Dispatch a template event with an optional payload.
     *
     * @param string $hookName The name of the event hook.
     * @param mixed  $payload  The payload to be passed to the event hook (default: null).
     * @return void
     */
    public function dispatchTplEvent(string $hookName, mixed $payload = null): void
    {
        try {
            $this->dispatchTplHook('event', $hookName, $payload);
        }catch(\Exception $e){
            //If some plugin or other event decides to go rouge it shouldn't take down the entire page
            report($e);
        }
    }

    /**
     * @param string $hookName
     * @param mixed  $payload
     * @param array  $available_params
     *
     * @return mixed
     */
    public function dispatchTplFilter(string $hookName, mixed $payload, array $available_params = []): mixed
    {
        try {

            return $this->dispatchTplHook('filter', $hookName, $payload, $available_params);

        }catch(\Exception $e){
            //If some plugin or other event decides to go rouge it shouldn't take down the entire page
            report($e);

            return $payload;
        }
    }

    /**
     * @param string $type
     * @param string $hookName
     * @param mixed  $payload
     * @param array  $available_params
     *
     * @return null|mixed
     */
    private function dispatchTplHook(string $type, string $hookName, mixed $payload, array $available_params = []): mixed
    {
        if (! is_string($type) || ! in_array($type, ['event', 'filter'])) {
            return null;
        }

        if ($type == 'filter') {
            return self::dispatch_filter($hookName, $payload, $available_params, $this->hookContext);
        }

        self::dispatch_event($hookName, $payload, $this->hookContext);

        return null;
    }
}
