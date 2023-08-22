<?php

/**
 * Template class - Template routing
 *
 */

namespace leantime\core;

use leantime\domain\models\auth\roles;
use leantime\domain\services;

/**
 * Template class - Template routing
 *
 * @package leantime
 * @subpackage core
 */
class template
{
    use eventhelpers;

    /**
     * @var array - vars that are set in the action
     */
    private $vars = array();

    /**
     *
     * @var string
     */
    public $frontcontroller = '';

    /**
     * @var string
     */
    private $notifcation = '';

    /**
     * @var string
     */
    private $notifcationType = '';

    /**
     * @var string
     */
    private $hookContext = '';

    /**
     * @var string
     */
    public $tmpError = '';

    /**
     * @var IncomingRequest|string
     */
    public $incomingRequest = '';

    /**
     * @var language|string
     */
    public $language = '';

    /**
     * @var string
     */
    public $template = '';

    /**
     * @var array
     */
    public $picture = array(
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
     * @var theme
     */
    private theme $theme;

    /**
     * @var \Illuminate\View\Factory
     */
    public \Illuminate\View\Factory $viewFactory;

    /**
     * __construct - get instance of frontcontroller
     *
     * @param  theme $theme
     * @param  language $language
     * @param  frontcontroller $frontcontroller
     * @param  IncomingRequest $incomingRequest
     * @param  environment $config
     * @param  appSettings $settings
     * @param  services\auth $login
     * @param  roles $roles
     * @access public
     * @return self
     */
    public function __construct(
        theme $theme,
        language $language,
        frontcontroller $frontcontroller,
        IncomingRequest $incomingRequest,
        environment $config,
        appSettings $settings,
        services\auth $login,
        roles $roles,
        \Illuminate\Contracts\View\Factory $viewFactory = null,
        \Illuminate\View\Compilers\Compiler $bladeCompiler = null
    ) {
        $this->theme = $theme;
        $this->language = $language;
        $this->frontcontroller = $frontcontroller;
        $this->incomingRequest = $incomingRequest;
        $this->config = $config;
        $this->settings = $settings;
        $this->login = $login;
        $this->roles = $roles;

        if (! is_null($viewFactory) && ! is_null($bladeCompiler)) {
            $this->viewFactory = $viewFactory;
            $this->bladeCompiler = $bladeCompiler;
        } else {
            app()->call([$this, 'setupBlade']);
            $this->attachComposers();
            $this->setupDirectives();
            $this->setupGlobalVars();
        }
    }

    /**
     * Create View Factory capable of rendering PHP and Blade templates
     *
     * @param \leantime\core\application $app
     * @param \Illuminate\View\Engines\EngineResolver $viewResolver
     *
     * @return void
     */
    public function setupBlade(
        \leantime\core\application $app,
        \Illuminate\Events\Dispatcher $eventDispatcher
    ) {
        // ComponentTagCompiler Expects the Foundation\Application Implmentation, let's trick it and give it the container.
        $app->instance(\Illuminate\Contracts\Foundation\Application::class, $app::getInstance());

        // Find Template Paths
        if (empty($_SESSION['template_paths']) || $this->config->debug) {
            $domainPaths = $customPaths = [];
            $domainModules = glob(APP_ROOT . '/app/domain/*');
            array_walk($domainModules, function ($path, $key) use (&$domainPaths, &$customPaths) {
                $domainPaths[basename($path)] = "$path/templates";
                $customPaths['custom' . basename($path)] = APP_ROOT . '/app/custom/' . basename($path) . '/templates';
            });

            $pluginPaths = glob(APP_ROOT . '/app/plugins/*');
            array_walk($pluginPaths, function ($path, $key) use (&$pluginPaths) {
                $domainPaths[basename($path)] = "$path/templates";
            });

            $_SESSION['template_paths'] = array_merge($domainPaths, $customPaths, $pluginPaths, ['global' => APP_ROOT . '/app/views/templates']);
        }

        // Setup Blade Compiler
        $app->singleton(
            \Illuminate\View\Compilers\CompilerInterface::class,
            function ($app) {
                $bladeCompiler = new \Illuminate\View\Compilers\BladeCompiler(
                    $app->make(\Illuminate\Filesystem\Filesystem::class),
                    APP_ROOT . '/cache/views'
                );

                $namespaces = array_keys($_SESSION['template_paths']);
                array_map(
                    [$bladeCompiler, 'anonymousComponentNamespace'],
                    array_map(fn ($namespace) => "$namespace::components", $namespaces),
                    $namespaces
                );

                return $bladeCompiler;
            }
        );

        // Register Blade Engines
        $app->singleton(
            \Illuminate\View\Engines\EngineResolver::class,
            function ($app) {
                $viewResolver = new \Illuminate\View\Engines\EngineResolver();
                $viewResolver->register('blade', fn () => $app->make(\Illuminate\View\Engines\CompilerEngine::class));
                $viewResolver->register('php', fn () => $app->make(\Illuminate\View\Engines\PhpEngine::class));
                return $viewResolver;
            }
        );

        // Setup View Finder
        $app->singleton(
            \Illuminate\View\ViewFinderInterface::class,
            function ($app) {
                $viewFinder = $app->make(\Illuminate\View\FileViewFinder::class, ['paths' => []]);
                array_map([$viewFinder, 'addNamespace'], array_keys($_SESSION['template_paths']), array_values($_SESSION['template_paths']));
                return $viewFinder;
            }
        );

        // Setup Events Dispatcher
        $app->bind(\Illuminate\Contracts\Events\Dispatcher::class, \Illuminate\Events\Dispatcher::class);

        // Setup View Factory
        $app->singleton(
            \Illuminate\Contracts\View\Factory::class,
            function ($app) {
                $viewFactory = $app->make(\Illuminate\View\Factory::class);
                array_map(fn ($ext) => $viewFactory->addExtension($ext, 'php'), ['inc.php', 'sub.php', 'tpl.php',]);
                // reprioritize blade
                $viewFactory->addExtension('blade.php', 'blade');
                $viewFactory->setContainer($app);
                return $viewFactory;
            }
        );
        $app->alias(\Illuminate\Contracts\View\Factory::class, 'view');

        $this->bladeCompiler = $app->make(\Illuminate\View\Compilers\CompilerInterface::class);
        $this->viewFactory = $app->make(\Illuminate\Contracts\View\Factory::class);
    }

    /**
     * attachComposers - attach view composers
     *
     * @return void
     */
    public function attachComposers()
    {
        if (empty($_SESSION['composers']) || $this->config->debug) {
            $globalComposerClasses = array_map(
                fn ($composerFile) => "leantime\\views\\composers\\" . basename($composerFile, '.php'),
                glob(APP_ROOT . '/app/views/composers/*.php')
            );

            $domainComposerClasses = array_map(function ($composerFile) {
                $domain = basename(dirname($composerFile, 2));
                $class = basename($composerFile, '.php');
                return "leantime\\domain\\composers\\$domain\\$class";
            }, glob(APP_ROOT . '/app/domain/*/composers/*.php'));

            $_SESSION['composers'] = array_merge($globalComposerClasses, $domainComposerClasses);
        }

        foreach ($_SESSION['composers'] as $composerClass) {
            if (
                is_subclass_of($composerClass, Composer::class) &&
                ! (new \ReflectionClass($composerClass))->isAbstract()
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
    public function setupDirectives()
    {
        $this->bladeCompiler->directive(
            'dispatchEvent',
            function ($args) {
                return "<?php \$tpl->dispatchTplEvent($args); ?>";
            }
        );

        $this->bladeCompiler->directive(
            'dispatchFilter',
            function ($args) {
                return "<?php echo \$tpl->dispatchTplFilter($args); ?>";
            }
        );

        $this->bladeCompiler->directive(
            'spaceless',
            function ($args) {
                return "<?php ob_start(); ?>";
            },
        );

        $this->bladeCompiler->directive(
            'endspaceless',
            function ($args) {
                return "<?php echo preg_replace('/>\\s+</', '><', ob_get_clean()); ?>";
            },
        );
    }

    /**
     * setupGlobalVars - setup global vars
     *
     * @return void
     */
    public function setupGlobalVars()
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
     * @param $name
     * @param $value
     * @return void
     */
    public function assign(string $name, mixed $value): void
    {
        $value = self::dispatch_filter("var.$name", $value);

        $this->vars[$name] = $value;
    }

    /**
     * setNotification - assign errors to the template
     *
     * @param  string $msg
     * @param  string $type
     * @param  string $event_id as a string for further identification
     * @return string
     */
    public function setNotification(string $msg, string $type, string $event_id = ''): void
    {
        $_SESSION['notification'] = $msg;
        $_SESSION['notifcationType'] = $type;
        $_SESSION['event_id'] = $event_id;
    }

    /**
     * getTemplatePath - Find template in custom and src directories
     *
     * @access public
     * @throws \Exception If template not found.
     * @param  string $namespace The namespace the template is for.
     * @param  string $path      The path to the template.
     * @return string|boolean Full template path or false if file does not exist
     */
    public function getTemplatePath(string $namespace, string $path): string
    {
        if ($namespace == '' || $path == '') {
            throw new \Exception("Both namespace and path must be provided");
        }

        $fullpath = self::dispatch_filter(
            "template_path__{$namespace}_{$path}",
            "$namespace::$path",
            [
                'namespace' => $namespace,
                'path' => $path,
            ]
        );

        if ($this->viewFactory->exists("custom$fullpath")) {
            return $fullpath;
        }

        if ($this->viewFactory->exists($fullpath)) {
            return $fullpath;
        }

        throw new \Exception("Template $fullpath not found");
    }

    /**
     * gives HTMX response
     *
     * @param string $view     The blade view path.
     * @param string $fragment The fragment key.
     * @return never
     */
    public function displayFragment(string $view, string $fragment = ''): never
    {
        $this->viewFactory->share(['tpl' => $this]);
        echo $this->viewFactory
            ->make($view, $this->vars)
            ->fragmentIf(! empty($fragment), $fragment);
        exit;
    }

    /**
     * display - display template from folder template including main layout wrapper
     *
     * @access public
     * @param  $template
     * @return void
     */
    public function display(string $template, string $layout = "app"): void
    {
        $template = self::dispatch_filter('template', $template);
        $template = self::dispatch_filter("template.$template", $template);

        $this->template = $template;

        $layout = $this->confirmLayoutName($layout, $template);

        $action = $this->frontcontroller::getActionName($template);
        $module = $this->frontcontroller::getModuleName($template);

        $loadFile = $this->getTemplatePath($module, $action);

        $this->hookContext = "tpl.$module.$action";
        $this->viewFactory->share(['tpl' => $this]);

        /** @var \Illuminate\View\View */
        $view = $this->viewFactory->make($loadFile);

        /** @todo this can be reduced to just the 'if' code after removal of php template support */
        if ($view->getEngine() instanceof \Illuminate\View\Engines\CompilerEngine) {
            $view->with(array_merge(
                $this->vars,
                ['layout' => $layout]
            ));
        } else {
            $view = $this->viewFactory->make($layout, array_merge(
                $this->vars,
                ['module' => $module, 'action' => $action]
            ));
        }

        $content = $view->render();
        $content = self::dispatch_filter('content', $content);
        $content = self::dispatch_filter("content.$template", $content);

        echo $content;
    }

    protected function confirmLayoutName($layoutName, $template)
    {
        $layout = htmlspecialchars($layoutName);
        $layout = self::dispatch_filter('layout', $layout);
        $layout = self::dispatch_filter("layout.$template", $layout);

        $layout = $this->getTemplatePath('global', "layouts.$layout");

        return $layout;
    }

    /**
     * displayJson - returns json data
     *
     * @access public
     * @param  $jsonContent
     * @return void
     */
    public function displayJson($jsonContent)
    {
        header('Content-Type: application/json; charset=utf-8');
        if ($jsonContent !== false) {
            echo $jsonContent;
        } else {
            echo json_encode(['error' => 'Invalid Json']);
        }
    }

    /**
     * display - display only the template from the template folder without a wrapper
     *
     * @access public
     * @param  $template
     * @return void
     */
    public function displayPartial($template)
    {
        $this->display($template, 'blank');
    }

    /**
     * get - get assigned values
     *
     * @access public
     * @param  $name
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
     * getNotification - pulls notification from the current session
     *
     * @access public
     * @return array
     */
    public function getNotification(): array
    {
        if (isset($_SESSION['notifcationType']) && isset($_SESSION['notification'])) {
            if(isset($_SESSION['event_id'])) {
                $event_id = $_SESSION['event_id'];
            }else{
                $event_id='';
            }
            return array('type' => $_SESSION['notifcationType'], 'msg' => $_SESSION['notification'], 'event_id' => $event_id);
        } else {
            return array('type' => "", 'msg' => "", 'event_id' => "");
        }
    }

    /**
     * displaySubmodule - display a submodule for a given module
     *
     * @access public
     * @param  string $alias
     * @return void
     */
    public function displaySubmodule(string $alias)
    {
        if (! str_contains($alias, '-')) {
            throw new \Exception("Submodule alias must be in the format module-submodule");
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
     */
    public function displayNotification()
    {
        $notification = '';
        $note = $this->getNotification();
        $language = $this->language;
        $message_id = $note['msg'];

        $message = self::dispatch_filter(
            'message',
            $language->__($message_id, false),
            $note
        );
        $message = self::dispatch_filter(
            "message_{$message_id}",
            $message,
            $note
        );

        if (!empty($note) && $note['msg'] != '' && $note['type'] != '') {
            $notification = '<script type="text/javascript">jQuery.growl({message: "'
                . $message . '", style: "' . $note['type'] . '"});</script>';

            self::dispatch_event("notification_displayed", $note);

            $_SESSION['notification'] = "";
            $_SESSION['notificationType'] = "";
            $_SESSION['event_id'] = "";
        }

        return $notification;
    }

    /**
     * displayInlineNotification - display notification
     *
     * @access public
     * @return string
     */
    public function displayInlineNotification()
    {

        $notification = '';
        $note = $this->getNotification();
        $language = $this->language;
        $message_id = $note['msg'];

        $message = self::dispatch_filter(
            'message',
            $language->__($message_id, false),
            $note
        );
        $message = self::dispatch_filter(
            "message_{$message_id}",
            $message,
            $note
        );

        if (!empty($note) && $note['msg'] != '' && $note['type'] != '') {
            $notification = "<div class='inputwrapper login-alert login-" . $note['type'] . "' style='position: relative;'>
                                <div class='alert alert-" . $note['type'] . "' style='padding:15px;' >
                                    <strong>" . $message . "</strong>
                                </div>
                            </div>
                            ";

            self::dispatch_event("notification_displayed", $note);

            $_SESSION['notification'] = "";
            $_SESSION['notificationType'] = "";
            $_SESSION['event_id'] = "";
        }

        return $notification;
    }

    /**
     * redirect - redirect to a given url
     *
     * @param  string $url
     * @return void
     */
    public function redirect(string $url): void
    {
        header("Location:" . trim($url));
        exit();
    }

    /**
     * getSubdomain - get subdomain from url
     *
     * @return string
     */
    public function getSubdomain(): string
    {
        preg_match('/(?:http[s]*\:\/\/)*(.*?)\.(?=[^\/]*\..{2,5})/i', $_SERVER['HTTP_HOST'], $match);

        $domain = $_SERVER['HTTP_HOST'];
        $tmp = explode('.', $domain); // split into parts
        $subdomain = $tmp[0];

        return $subdomain;
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
     * @param  ?string $content
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
     * @param  ?string $content
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
     * @param  ?string $content
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
     * getFormattedDateString - returns a language specific formatted date string. wraps language class method
     *
     * @access public
     * @param  string $date
     * @return string
     */
    public function getFormattedDateString($date): string
    {
        return $this->language->getFormattedDateString($date);
    }

    /**
     * getFormattedTimeString - returns a language specific formatted time string. wraps language class method
     *
     * @access public
     * @param $date string
     * @return string
     */
    public function getFormattedTimeString($date): string
    {
        return $this->language->getFormattedTimeString($date);
    }

    /**
     * getFormattedDateTimeString - returns a language specific formatted date and time string. wraps language class method
     *
     * @access public
     * @param  string $date
     * @return string
     */
    public function get24HourTimestring(string $dateTime): string
    {
        return $this->language->get24HourTimestring($dateTime);
    }

    /**
     * truncate - truncate text
     *
     * @see https://stackoverflow.com/questions/1193500/truncate-text-containing-html-ignoring-tags
     * @author Søren Løvborg <https://stackoverflow.com/users/136796/s%c3%b8ren-l%c3%b8vborg>
     * @access public
     * @param  string $html
     * @param  int $maxLength
     * @param  string $ending
     * @param  bool $exact
     * @param  bool $considerHtml
     * @return string
     */
    public function truncate($html, $maxLength = 100, $ending = '(...)', $exact = true, $considerHtml = false)
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
     * @param  ?string $text
     * @return string
     */
    public function convertRelativePaths(?string $text)
    {
        if (is_null($text)) {
            return $text;
        }

        $base = BASE_URL;

        // base url needs trailing /
        $base = rtrim($base, "/") . "/";

        // Replace links
        $text = preg_replace(
            '/<a([^>]*) href="([^http|ftp|https|mailto|#][^"]*)"/',
            "<a\${1} href=\"$base\${2}\"",
            $text
        );

        // Replace images
        $text = preg_replace(
            '/<img([^>]*) src="([^http|ftp|https][^"]*)"/',
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
     */
    public function getModulePicture()
    {
        $module = frontcontroller::getModuleName($this->template);

        $picture = $this->picture['default'];
        if (isset($this->picture[$module])) {
            $picture = $this->picture[$module];
        }

        return $picture;
    }

    /**
     * displayLink - display link
     *
     * @access public
     * @param  string $module
     * @param  string $name
     * @param  ?array $params
     * @param  ?array $attribute
     * @return string
     */
    public function displayLink($module, $name, $params = null, $attribute = null)
    {

        $mod = explode('.', $module);

        if (is_array($mod) === true && count($mod) == 2) {
            $action = $mod[1];
            $module = $mod[0];

            $mod = $module . '/class.' . $action . '.php';
        } else {
            $mod = array();
            return false;
        }

        $returnLink = false;

        $url = "/" . $module . "/" . $action . "/";

        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $url .= $value . "/";
            }
        }

        $attr = '';

        if ($attribute != null) {
            foreach ($attribute as $key => $value) {
                $attr .= $key . " = '" . $value . "' ";
            }
        }

        $returnLink = "<a href='" . BASE_URL . "" . $url . "' " . $attr . ">" . $name . "</a>";

        return $returnLink;
    }

    /**
    * patchDownloadUrlToFilenameOrAwsUrl - Replace all local download.php references in <img src=""> tags
    * by either local filenames or AWS URLs that can be accesse without being authenticated
    *
    * Note: This patch is required by the PDF generating engine as it retrieves URL data without being
    * authenticated
    *
    * @access public
    * @param  string  $textHtml HTML text, potentially containing <img srv="https://local.domain/download.php?xxxx"> tags
    * @return string  HTML text with the https://local.domain/download.php?xxxx replaced by either full qualified
    *                 local filenames or AWS URLs
    */

    public function patchDownloadUrlToFilenameOrAwsUrl(string $textHtml): string
    {
        $patchedTextHtml = $this->convertRelativePaths($textHtml);

        // TO DO: Replace local download.php
        $patchedTextHtml = $patchedTextHtml;

        return $patchedTextHtml;
    }

    /**
     * @param string $hookName
     * @param mixed  $payload
     */
    public function dispatchTplEvent($hookName, $payload = [])
    {
        $this->dispatchTplHook('event', $hookName, $payload);
    }

    /**
     * @param string $hookName
     * @param mixed  $payload
     * @param mixed  $available_params
     *
     * @return mixed
     */
    public function dispatchTplFilter($hookName, $payload = [], $available_params = [])
    {
        return $this->dispatchTplHook('filter', $hookName, $payload, $available_params);
    }

    /**
     * @param string $type
     * @param string $hookName
     * @param mixed  $payload
     * @param mixed  $available_params
     *
     * @return null|mixed
     */
    private function dispatchTplHook($type, $hookName, $payload = [], $available_params = [])
    {
        if (
            !is_string($type) || !in_array($type, ['event', 'filter'])
        ) {
            return;
        }

        if ($type == 'filter') {
            return self::dispatch_filter($hookName, $payload, $available_params, $this->hookContext);
        }

        self::dispatch_event($hookName, $payload, $this->hookContext);
    }
}
