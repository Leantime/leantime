<?php

namespace Leantime\Core\UI;

use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;
use Illuminate\View\Compilers\Compiler;
use Illuminate\View\View;
use Illuminate\View\ViewException;
use Leantime\Core\Configuration\AppSettings;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\Language;
use Leantime\Core\Support\DateTimeInfoEnum;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Template class
 * Support Leantime UI with custom template roues and various UI helpers
 */
class Template
{
    use DispatchesEvents;

    /** @var array - vars that are set in the action */
    private array $vars = [];

    private string $notifcation = '';

    private string $notificationType = '';

    private string $hookContext = '';

    public string $tmpError = '';

    public string $template = '';

    protected array $headers = [];

    public $viewFactory;

    public array $picture = [
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
    ];

    /**
     * __construct - get instance of frontcontroller
     *
     * @param  Compiler|null  $bladeCompiler
     *
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function __construct(
        /** @var Theme */
        private Theme $theme,

        /** @var Language */
        public Language $language,

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

        private Filesystem $files,

    ) {

        $this->setupGlobalVars();
    }

    /**
     * setupGlobalVars - setup global vars
     */
    public function setupGlobalVars(): void
    {
        app('view')->share([
            'frontController' => app('frontcontroller'),
            'config' => $this->config,
            /** @todo remove settings after renaming all uses to appSettings */
            'settings' => $this->settings,
            'appSettings' => $this->settings,
            'login' => $this->login,
            'roles' => $this->roles,
            'language' => $this->language,
            'dateTimeInfoEnum' => DateTimeInfoEnum::class,
            'tpl' => $this,
            'request' => $this->incomingRequest,
        ]);

        $this->viewFactory = app('view');
    }

    /**
     * assign - assign variables in the action for template
     *
     * @param  string  $name  Name of variable
     * @param  mixed  $value  Value of variable
     */
    public function assign(string $name, mixed $value): void
    {
        /**
         * Filter to access template variable names after they have been assigned
         *
         * @var mixed $value The current value of the variable.
         */
        $value = self::dispatchFilter("var.$name", $value);

        $this->vars[$name] = $value;
    }

    /**
     * get - get assigned values
     *
     * @return array
     */
    public function get(string $name): mixed
    {
        if (! isset($this->vars[$name])) {
            return null;
        }

        return $this->vars[$name];
    }

    /**
     * getAll - get all assigned values
     *
     **/
    public function getAll(): array
    {
        return $this->vars ?? [];
    }

    /**
     * Sets a header in the response object.
     *
     * @param  string  $key  The key of the header.
     * @param  string  $value  The value of the header.
     */
    public function setResponseHeader(string $key, string $value): void
    {
        $this->headers[$key] = $value;
    }

    /**
     * Sets the response header to trigger an htmx event
     *
     **/
    public function setHTMXEvent(string $eventName): void
    {
        $this->headers['HX-Trigger'] ??= [];
        $this->headers['HX-Trigger'][] = $eventName;
    }

    /**
     * Refreshes (main url page in the background)
     *
     * @param  string  $eventName
     **/
    public function htmxRefresh(): void
    {

        $hxCurrentUrl = $this->incomingRequest->headers->get('hx-current-url');
        $mainPageUrl = Str::before($hxCurrentUrl, '#');
        $this->headers['HX-Location'] = $mainPageUrl;

    }

    /**
     * Sets the response header to trigger an htmx event
     *
     * @param  string  $eventName
     **/
    public function closeModal(): void
    {
        $this->setHTMXEvent('HTMX.closemodal');
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * display - display template from folder template including main layout wrapper
     *
     * @throws Exception
     */
    public function display(string $template, string $layout = 'app', int $responseCode = 200, array $headers = []): Response
    {

        $template = self::dispatchFilter('template', $template);
        $template = self::dispatchFilter("template.$template", $template);

        $this->template = $template;

        $layout = $this->confirmLayoutName($layout, $template);

        $templateParts = $this->parseViewPath($template);

        $loadFile = $this->getTemplatePath($templateParts['module'], $templateParts['path']);

        // app('view')->share([]);

        $this->hookContext = 'tpl.'.$templateParts['module'].'.'.$templateParts['path'];

        $viewFactory = app('view');

        /** @var View $view */
        $view = app('view')->make($loadFile);

        $path = $view->getPath();

        $this->setHookContext($templateParts, $path);

        /** @todo this can be reduced to just the 'if' code after removal of php template support */
        if (str_ends_with($path, 'blade.php')) {
            $view->with(array_merge(
                $this->vars,
                ['layout' => $layout]
            ));
        } else {
            $view = app('view')->make($layout, array_merge(
                $this->vars,
                ['module' => strtolower($templateParts['module']), 'action' => $templateParts['path']]
            ));
        }

        $content = $view->render();

        return new Response($content, $responseCode, array_merge($headers, $this->headers));
    }

    /**
     * displaySubmodule - display a submodule for a given module
     *
     * @throws Exception
     */
    public function displaySubmodule(string $alias): void
    {
        if (! str_contains($alias, '-')) {
            throw new Exception('Submodule alias must be in the format module-submodule');
        }

        [$module, $submodule] = explode('-', $alias);

        $relative_path = $this->getTemplatePath($module, "submodules.$submodule");

        echo app('view')->make($relative_path, array_merge($this->vars, ['tpl' => $this]))->render();
    }

    public function getRenderedTemplate(string $template): string
    {

        [$module, $partial] = explode('.', $template);

        $relative_path = $this->getTemplatePath($module, "partials.$partial");

        return app('view')->make($relative_path, array_merge($this->vars, ['tpl' => $this]))->render();
    }

    public function emptyResponse($responseCode = 200)
    {
        return new Response('', $responseCode, $this->headers);
    }

    /**
     * Display JSON content with an optional response code.
     *
     * @param  array|object|string  $jsonContent  The JSON content to be displayed.
     * @param  int  $statusCode  The HTTP response code to be returned (default: 200).
     * @return Response The response object after displaying the JSON content.
     *
     * @deprecated
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
     * @param  string  $template  The path to the partial template file.
     * @param  int  $responseCode  The HTTP response code to be returned (default: 200).
     * @return Response The response object after displaying the partial template.
     */
    public function displayPartial(string $template, int $responseCode = 200): Response
    {
        return $this->display($template, 'blank', $responseCode);
    }

    /**
     * gives HTMX response
     *
     * @param  string  $viewPath  The blade view path.
     * @param  string  $fragment  The fragment key.
     */
    public function displayFragment(string $viewPath, string $fragment = ''): Response
    {
        $layout = $this->confirmLayoutName('blank', ! empty($fragment) ? "$viewPath.fragment" : $viewPath);

        app('view')->share(['tpl' => $this]);

        /** @var View $view */
        $view = app('view')->make($viewPath, array_merge($this->vars, ['layout' => $layout]));

        $path = $view->getPath();
        $viewPathExplode = explode('::', $viewPath);

        $this->setHookContext(['module' => $viewPathExplode[0] ?? '', 'path' => $viewPathExplode[1] ?? ''], $path);

        return new Response($view->fragmentIf(! empty($fragment), $fragment));
    }

    /**
     * Confirm the layout name based on the provided parameters.
     *
     * @param  string  $layoutName  The layout name to be confirmed.
     * @param  string  $template  The template name associated with the layout.
     * @return bool|string The confirmed layout name, or false if not found.
     */
    protected function confirmLayoutName(string $layoutName, string $template): bool|string
    {
        $layout = htmlspecialchars($layoutName);
        $layout = self::dispatchFilter('layout', $layout);
        $layout = self::dispatchFilter("layout.$template", $layout);

        $layout = $this->getTemplatePath('global', "layouts.$layout");

        return $layout;
    }

    /**
     * Parse the view path from the given view name.
     *
     * @param  string  $viewName  The name of the view.
     * @return array An associative array containing the module and path parts of the view path.
     *
     * @throws ViewException If the view name cannot be parsed.
     */
    protected function parseViewPath(string $viewName)
    {

        $pathParts = [
            'module' => '',
            'path' => '',
        ];

        // view path style
        // module::path.name
        if (str_contains($viewName, '::')) {
            $parts = explode('::', $viewName);
            $pathParts['module'] = $parts[0];
            $pathParts['path'] = $parts[1];

            return $pathParts;
        }

        // leantime path
        // module.name
        if (str_contains($viewName, '.')) {
            $parts = explode('.', $viewName);
            $pathParts['module'] = $parts[0];
            $pathParts['path'] = $parts[1];

            return $pathParts;
        }

        throw new ViewException("View name $viewName could not be parsed");
    }

    /**
     * getTemplatePath - Find template in custom and src directories
     *
     * @param  string  $namespace  The namespace the template is for.
     * @param  string  $path  The path to the template.
     * @return string Full template path or false if file does not exist
     *
     * @throws Exception If template not found.
     */
    public function getTemplatePath(string $namespace, string $path): string
    {
        if ($namespace == '' || $path == '') {
            throw new Exception('Both namespace and path must be provided');
        }

        $namespace = strtolower($namespace);
        $fullpath = self::dispatchFilter(
            "template_path__{$namespace}_{$path}",
            "$namespace::$path",
            [
                'namespace' => $namespace,
                'path' => $path,
            ]
        );

        if (app('view')->exists($fullpath)) {
            return $fullpath;
        }

        throw new ViewException("Template $fullpath not found");
    }

    /**
     * getNotification - pulls notification from the current session
     */
    public function getNotification(): array
    {
        if (session()->exists('notificationType') && session()->exists('notification')) {
            $event_id = session('event_id') ?? '';

            return ['type' => session('notificationType'), 'msg' => session('notification'), 'event_id' => $event_id];
        } else {
            return ['type' => '', 'msg' => '', 'event_id' => ''];
        }
    }

    /**
     * displayNotification - display notification
     *
     * @throws BindingResolutionException
     */
    public function displayNotification(): string
    {
        $notification = '';
        $note = $this->getNotification();
        $language = $this->language;
        $message_id = $note['msg'];

        $message = self::dispatchFilter(
            'message',
            $language->__($message_id),
            $note
        );
        $message = self::dispatchFilter(
            "message_{$message_id}",
            $message,
            $note
        );

        if (! empty($note) && $note['msg'] != '' && $note['type'] != '') {
            $notification .= app('blade.compiler')::render(
                '<script type="text/javascript">jQuery.growl({message: "{!! $message !!}", style: "{{ $style }}"});</script>',
                [
                    'message' => $message,
                    'style' => $note['type'],
                ]
            );

            self::dispatchEvent('notification_displayed', $note);

            session(['notification' => '']);
            session(['notificationType' => '']);
            session(['event_id' => '']);
        }

        if (session()->exists('confettiInYourFace') && session('confettiInYourFace') === true) {
            $notification .= app('blade.compiler')::render(
                '<script type="text/javascript">confetti({
                     spread: 70,
                     origin: { y: 1.2 },
                     disableForReducedMotion: true
                });</script>',
                []
            );

            session(['confettiInYourFace' => false]);
        }

        return $notification;
    }

    /**
     * displayInlineNotification - display notification
     *
     * @throws BindingResolutionException
     *
     * @deprecated Component
     */
    public function displayInlineNotification(): string
    {
        $notification = '';
        $note = $this->getNotification();
        $language = $this->language;
        $message_id = $note['msg'];

        $message = self::dispatchFilter(
            'message',
            $language->__($message_id),
            $note
        );
        $message = self::dispatchFilter(
            "message_{$message_id}",
            $message,
            $note
        );

        if (! empty($note) && $note['msg'] != '' && $note['type'] != '') {
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

            self::dispatchEvent('notification_displayed', $note);

            session(['notification' => '']);
            session(['notificationType' => '']);
            session(['event_id' => '']);
        }

        if (session()->exists('confettiInYourFace') && session('confettiInYourFace') === true) {
            $notification .= app('blade.compiler')::render(
                '<script type="text/javascript">confetti({
                        spread: 70,
                        origin: { y: 1.2 },
                        disableForReducedMotion: true
                      });
                      </script>',
                []
            );

            session(['confettiInYourFace' => false]);
        }

        return $notification;
    }

    /**
     * setNotification - assign errors to the template
     *
     * @param  string  $event_id  as a string for further identification
     */
    public function setNotification(string $msg, string $type, string $event_id = ''): void
    {
        session(['notification' => $msg]);
        session(['notificationType' => $type]);
        session(['event_id' => $event_id]);

        $this->setHTMXEvent('HTMX.ShowNotification');
    }

    /**
     * getToggleState - retrieves the toggle state of a submenu by name from the session
     *
     * @param  string  $name  - the name of the submenu toggle
     * @return string - the toggle state of the submenu (either "true" or "false")
     *
     * @deprecated this should be in a component
     */
    public function getToggleState(string $name): string
    {
        if (session()->exists('usersettings.submenuToggle.'.$name)) {
            return session('usersettings.submenuToggle.'.$name);
        }

        return false;
    }

    /**
     * Set a flag to indicate that confetti should be displayed.
     * Will be displayed next time a notification is displayed
     *
     * @return void confetti, duh
     */
    public function sendConfetti()
    {
        session(['confettiInYourFace' => true]);
    }

    /**
     * __ - returns a language specific string. wraps language class method
     */
    public function __(string $index): string
    {
        return $this->language->__($index);
    }

    /**
     * e - echos and escapes content
     */
    public function e(?string $content): void
    {
        $content = $this->convertRelativePaths($content);
        $escaped = $this->escape($content);

        echo $escaped;
    }

    /**
     * escape - escapes content
     */
    public function escape(?string $content): string
    {
        if (! is_null($content)) {
            $content = $this->convertRelativePaths($content);

            return htmlentities($content);
        }

        return '';
    }

    /**
     * escapeMinimal - escapes content
     */
    public function escapeMinimal(?string $content): string
    {
        $content = $this->convertRelativePaths($content);
        $config = [
            'safe' => 1,
            'style_pass' => 1,
            'cdata' => 1,
            'comment' => 1,
            'deny_attribute' => '* -href -style',
            'keep_bad' => 0,
        ];

        if (! is_null($content)) {
            return htmLawed($content, [
                'comments' => 0,
                'cdata' => 0,
                'deny_attribute' => 'on*',
                'elements' => '* -applet -canvas -embed -object -script',
                'schemes' => 'href: aim, feed, file, ftp, gopher, http, https, irc, mailto, news, nntp, sftp, ssh, tel, telnet; style: !; *:file, http, https',
            ]);
        }

        return '';
    }

    /**
     * truncate - truncate text
     *
     * @see https://stackoverflow.com/questions/1193500/truncate-text-containing-html-ignoring-tags
     *
     * @author Søren Løvborg <https://stackoverflow.com/users/136796/s%c3%b8ren-l%c3%b8vborg>
     */
    public function truncate(string $html, int $maxLength = 100, string $ending = '(...)', bool $exact = true, bool $considerHtml = false): string
    {
        $printedLength = 0;
        $position = 0;
        $tags = [];
        $isUtf8 = true;
        $truncate = '';
        $html = $this->convertRelativePaths($html);
        // For UTF-8, we need to count multibyte sequences as one character.
        $re = $isUtf8 ? '{</?([a-z]+)[^>]*>|&#?[a-zA-Z0-9]+;|[\x80-\xFF][\x80-\xBF]*}' : '{</?([a-z]+)[^>]*>|&#?[a-zA-Z0-9]+;}';

        while ($printedLength < $maxLength && preg_match($re, $html, $match, PREG_OFFSET_CAPTURE, $position)) {
            [$tag, $tagPosition] = $match[0];

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
        while (! empty($tags)) {
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
     *
     * @deprecated
     */
    public function convertRelativePaths(?string $text): ?string
    {
        if (is_null($text)) {
            return $text;
        }

        $base = BASE_URL;

        // base url needs trailing /
        $base = rtrim($base, '/').'/';

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
     * patchDownloadUrlToFilenameOrAwsUrl - Replace all local files/get references in <img src=""> tags
     * by either local filenames or AWS URLs that can be accesse without being authenticated
     *
     * Note: This patch is required by the PDF generating engine as it retrieves URL data without being
     * authenticated
     *
     * @param  string  $textHtml  HTML text, potentially containing <img srv="https://local.domain/files/get?xxxx"> tags
     * @return string HTML text with the https://local.domain/files/get?xxxx replaced by either full qualified
     *                local filenames or AWS URLs
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
     * @param  string  $hookName  The name of the event hook.
     * @param  mixed  $payload  The payload to be passed to the event hook (default: null).
     */
    public function dispatchTplEvent(string $hookName, mixed $payload = null): void
    {
        try {
            $this->dispatchTplHook('event', $hookName, $payload);
        } catch (\Exception $e) {
            // If some plugin or other event decides to go rouge it shouldn't take down the entire page
            report($e);
        }
    }

    public function dispatchTplFilter(string $hookName, mixed $payload, array $available_params = []): mixed
    {
        try {

            return $this->dispatchTplHook('filter', $hookName, $payload, $available_params);

        } catch (\Exception $e) {
            // If some plugin or other event decides to go rouge it shouldn't take down the entire page
            report($e);

            return $payload;
        }
    }

    /**
     * @return null|mixed
     */
    private function dispatchTplHook(string $type, string $hookName, mixed $payload, array $available_params = []): mixed
    {
        if (! is_string($type) || ! in_array($type, ['event', 'filter'])) {
            return null;
        }

        if ($type == 'filter') {
            return self::dispatchFilter($hookName, $payload, $available_params, $this->hookContext);
        }

        self::dispatchEvent($hookName, $payload, $this->hookContext);

        return null;
    }

    /**
     * redirect - redirect to a given url
     *
     *
     * @deprecated
     */
    public function redirect(string $url): RedirectResponse
    {
        return Frontcontroller::redirect($url);
    }

    /**
     * getModulePicture - get module picture
     *
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

    protected function setHookContext($templateParts, $path)
    {

        if (str_contains($path, 'app/Plugins')) {
            $this->hookContext = 'leantime.plugins.'.$templateParts['module'].'.templates.'.$templateParts['path'];
        } else {
            $this->hookContext = 'leantime.domain.'.$templateParts['module'].'.templates.'.$templateParts['path'];
        }

    }

    public function clearViewPathCache()
    {

        $viewPathCachePath = storage_path('framework/viewPaths.php');
        $this->files->delete($viewPathCachePath);

    }
}
