<?php

namespace Leantime\Core\UI;

use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
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
    protected array $headers = [];


    /**
     * __construct - get instance of frontcontroller
     *
     * @param Theme           $theme
     * @param Language        $language
     * @param IncomingRequest $incomingRequest
     * @param Environment     $config
     * @param AppSettings     $settings
     * @param AuthService     $login
     * @param Roles           $roles
     * @param Compiler|null   $bladeCompiler
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

    ) {

        $this->setupDirectives();
        $this->setupGlobalVars();
    }

    /**
     * setupDirectives - setup blade directives
     *
     * @return void
     */
    public function setupDirectives(): void
    {
        app('blade.compiler')->directive(
            'dispatchEvent',
            fn ($args) => "<?php \$tpl->dispatchTplEvent($args); ?>",
        );

        app('blade.compiler')->directive(
            'dispatchFilter',
            fn ($args) => "<?php echo \$tpl->dispatchTplFilter($args); ?>",
        );

        app('blade.compiler')->directive(
            'spaceless',
            fn ($args) => "<?php ob_start(); ?>",
        );

        app('blade.compiler')->directive(
            'endspaceless',
            fn ($args) => "<?php echo preg_replace('/>\\s+</', '><', ob_get_clean()); ?>",
        );

        app('blade.compiler')->directive(
            'formatDate',
            fn ($args) => "<?php echo format($args)->date(); ?>",
        );

        app('blade.compiler')->directive(
            'formatTime',
            fn ($args) => "<?php echo format($args)->time(); ?>",
        );

        app('blade.compiler')->directive(
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
        app("view")->share([
            'frontController' => app('frontcontroller'),
            'config' => $this->config,
            /** @todo remove settings after renaming all uses to appSettings */
            'settings' => $this->settings,
            'appSettings' => $this->settings,
            'login' => $this->login,
            'roles' => $this->roles,
            'language' => $this->language,
            'dateTimeInfoEnum' => DateTimeInfoEnum::class,
            'tpl' => $this
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
     * Sets a header in the response object.
     *
     * @param string $key The key of the header.
     * @param string $value The value of the header.
     * @return void
     */
    public function setResponseHeader(string $key, string $value):void {
        $this->headers[$key] = $value;
    }

    /**
     * Sets the response header to trigger an htmx event
     *
     * @param string $eventName
     * @return void
     **/
    public function setHTMXEvent(string $eventName): void
    {
        $this->headers['HX-Trigger'] ??= [];
        $this->headers['HX-Trigger'][] = $eventName;
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
    public function display(string $template, string $layout = "app", int $responseCode = 200, array $headers = []): Response
    {

        $template = self::dispatch_filter('template', $template);
        $template = self::dispatch_filter("template.$template", $template);

        $this->template = $template;

        $layout = $this->confirmLayoutName($layout, $template);

        $templateParts = $this->parseViewPath($template);

        $loadFile = $this->getTemplatePath($templateParts['module'], $templateParts['path']);

        app('view')->share([]);

        /** @var View $view */
        $view = app('view')->make($loadFile);

        $view->with(array_merge(
            $this->vars,
            ['layout' => $layout]
        ));

        $content = $view->render();

        return new Response($content, $responseCode, array_merge($headers, $this->headers));
    }

    /**
     * Display JSON content with an optional response code.
     *
     * @param array|object|string $jsonContent The JSON content to be displayed.
     * @param int                 $statusCode  The HTTP response code to be returned (default: 200).
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
     * @param string $template     The path to the partial template file.
     * @param int    $responseCode The HTTP response code to be returned (default: 200).
     * @return Response The response object after displaying the partial template.
     */
    public function displayPartial(string $template, int $responseCode = 200): Response
    {
        return $this->display($template, 'blank', $responseCode);
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
        app('view')->share(['tpl' => $this]);
        /** @var View $view */
        $view = app('view')->make($viewPath, array_merge($this->vars, ['layout' => $layout]));
        return new Response($view->fragmentIf(! empty($fragment), $fragment));
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
     * Parse the view path from the given view name.
     *
     * @param string $viewName The name of the view.
     * @return array An associative array containing the module and path parts of the view path.
     * @throws ViewException If the view name cannot be parsed.
     */
    protected function parseViewPath(string $viewName) {

        $pathParts = array(
            "module" => "",
            "path" => "",
        );

        //view path style
        //module::path.name
        if(str_contains($viewName, "::")) {
            $parts = explode("::", $viewName);
            $pathParts['module'] = $parts[0];
            $pathParts['path'] = $parts[1];

            return $pathParts;
        }

        //leantime path
        //module.name
        if(str_contains($viewName, ".")) {
            $parts = explode(".", $viewName);
            $pathParts['module'] = $parts[0];
            $pathParts['path'] = $parts[1];

            return $pathParts;
        }

        throw new ViewException("View name $viewName could not be parsed");

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
    protected function getTemplatePath(string $namespace, string $path): string
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

        if (app('view')->exists($fullpath)) {
            return $fullpath;
        }

        throw new ViewException("Template $fullpath not found");
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
                });</script>',
                []
            );

            session(["confettiInYourFace" => false]);
        }

        return $notification;
    }

    /**
     * displayInlineNotification - display notification
     *
     * @access public
     * @return string
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
     * getToggleState - retrieves the toggle state of a submenu by name from the session
     *
     * @access  public
     * @param string $name - the name of the submenu toggle
     * @return  string - the toggle state of the submenu (either "true" or "false")
     *
     * @deprecated this should be in a component
     */
    public function getToggleState(string $name): string
    {
        if (session()->exists("usersettings.submenuToggle.".$name)) {
            return session("usersettings.submenuToggle.".$name);
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
        session(["confettiInYourFace" => true]);
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

    /**
     * redirect - redirect to a given url
     *
     * @param  string $url
     * @return RedirectResponse
     *
     * @deprecated
     *
     */
    public function redirect(string $url): RedirectResponse
    {
        return Frontcontroller::redirect($url);
    }

}
