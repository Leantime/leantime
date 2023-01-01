<?php

/**
 * Template class - Template routing
 *
 */

namespace leantime\core {

    use leantime\domain\models\auth\roles;
    use leantime\domain\services;

    class template
    {
        use eventhelpers;

        /**
         * @access private
         * @var    array - vars that are set in the action
         */
        private $vars = array();

        /**
         *
         * @access private
         * @var    string
         */
        private $frontcontroller = '';

        /**
         *
         * @access private
         * @var    string
         */
        private $notifcation = '';

        /**
         *
         * @access private
         * @var    string
         */
        private $notifcationType = '';

        /**
         * @access private
         * @var    string
         */
        private $hookContext = '';

        /**
         * @access public
         * @var    string
         */
        public $tmpError = '';

        /**
         * @access public
         * @var    object
         */
        public $language = '';
        public $template = '';
        public $mainContent = '';

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
            'default' => 'fa-off'
        );

        /**
         * __construct - get instance of frontcontroller
         *
         * @access public
         */
        public function __construct()
        {
            $this->theme = new theme();
            $this->language = language::getInstance();
            $this->frontcontroller = frontcontroller::getInstance(ROOT);
        }

        /**
         * assign - assign variables in the action for template
         *
         * @param $name
         * @param $value
         */
        public function assign($name, $value)
        {

            $value = self::dispatch_filter("var.$name", $value);

            $this->vars[$name] = $value;
        }

        /**
         * setError - assign errors to the template
         *
         * @param  $msg
         * @param  $type
         * @return string
         */
        public function setNotification($msg, $type)
        {

            $_SESSION['notification'] = $msg;
            $_SESSION['notifcationType'] = $type;
        }

        /**
         * getTemplatePath - Find template in custom and src directories
         *
         * @access public
         * @param  string $module Module template resides in
         * @param  string $name   Template filename name (including tpl.php extension)
         * @return string|bool Full template path or false if file does not exist
         *
         */
        public function getTemplatePath(string $module, string $name): string|false
        {

            if ($module == '' || $name == '') {
                return false;
            }

            $plugin_path = self::dispatch_filter('relative_plugin_template_path', '', [
                        'module' => $module,
                        'name' => $name
            ]);

            if ($_SESSION['isInstalled'] === true && $_SESSION['isUpdated'] === true) {
                $pluginService = new services\plugins();
            }

            if (empty($plugin_path) || !file_exists($plugin_path)) {
                $file = '/' . $module . '/templates/' . $name;

                if (file_exists(ROOT . '/../src/custom' . $file) && is_readable(ROOT . '/../src/custom' . $file)) {
                    return ROOT . '/../src/custom' . $file;
                }

                if (file_exists(ROOT . '/../src/plugins' . $file) && is_readable(ROOT . '/../src/plugins' . $file)) {
                    if ($_SESSION['isInstalled'] === true && $_SESSION['isUpdated'] === true && $pluginService->isPluginEnabled($module)) {
                        return ROOT . '/../src/plugins' . $file;
                    }
                }

                if (file_exists(ROOT . '/../src/domain' . $file) && is_readable(ROOT . '/../src/domain/' . $file)) {
                    return ROOT . '/../src/domain/' . $file;
                }

                return false;
            }

            if (file_exists(ROOT . '/../src/custom' . $plugin_path) && is_readable(ROOT . '/../src/custom' . $plugin_path)) {
                return ROOT . '/../src/custom' . $plugin_path;
            }

            if (file_exists(ROOT . '/../src/plugins' . $plugin_path) && is_readable(ROOT . '/../src/plugins' . $plugin_path)) {
                if ($_SESSION['isInstalled'] === true && $_SESSION['isUpdated'] === true && $pluginService->isPluginEnabled($module)) {
                    return ROOT . '/../src/plugins' . $plugin_path;
                }
            }

            if (file_exists(ROOT . '/../src/domain' . $plugin_path) && is_readable(ROOT . '/../src/domain/' . $plugin_path)) {
                return ROOT . '/../src/domain/' . $plugin_path;
            }

            return false;
        }

        /**
         * display - display template from folder template including main layout wrapper
         *
         * @access public
         * @param  $template
         * @return void
         */
        public function display($template, $layout = "app")
        {

            //These variables are available in the template
            $config = \leantime\core\environment::getInstance();
            $settings = new appSettings();
            $login = services\auth::getInstance();
            $roles = new roles();

            $language = $this->language;

            foreach (
                [
                'template',
                "template.$template"
                ] as $tplfilter
            ) {
                $template = self::dispatch_filter($tplfilter, $template);
            }

            $this->template = $template;

            //Load Layout file
            $layout = htmlspecialchars($layout);

            foreach (
                [
                'layout',
                "layout.$template"
                ] as $tplfilter
            ) {
                $layout = self::dispatch_filter($tplfilter, $layout);
            }

            $layoutFilename = $this->theme->getLayoutFilename($layout . '.php', $template);

            if ($layoutFilename === false) {
                $layoutFilename = $this->theme->getLayoutFilename('app.php');
            }

            if ($layoutFilename === false) {
                die("Cannot find default 'app.php' layout file");
            }

            ob_start();

            require($layoutFilename);

            $layoutContent = ob_get_clean();

            foreach (
                [
                'layoutContent',
                "layoutContent.$template"
                ] as $tplfilter
            ) {
                $layoutContent = self::dispatch_filter($tplfilter, $layoutContent);
            }

            //Load Template
            //frontcontroller splits the name (actionname.modulename)
            $action = $this->frontcontroller::getActionName($template);
            $module = $this->frontcontroller::getModuleName($template);

            $loadFile = $this->getTemplatePath($module, $action . '.tpl.php');

            $this->hookContext = "tpl.$module.$action";

            ob_start();

            if ($loadFile !== false) {
                require_once($loadFile);
            } else {
                error_log("Tried loading " . $loadFile . ". File not found");
                $this->frontcontroller::redirect(BASE_URL . "/error/error404");
            }

            $content = ob_get_clean();

            foreach (
                [
                'content',
                "content.$template"
                ] as $tplfilter
            ) {
                $content = self::dispatch_filter($tplfilter, $content);
            }

            //Load template content into layout content
            $render = str_replace("<!--###MAINCONTENT###-->", $content, $layoutContent);

            foreach (
                [
                'render',
                "render.$template"
                ] as $filter
            ) {
                $render = self::dispatch_filter($filter, $render);
            }

            echo $render;
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
                echo "{Invalid Json}";
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
        public function get($name)
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
         *
         * @return array
         */
        public function getNotification(): array
        {

            if (isset($_SESSION['notifcationType']) && isset($_SESSION['notification'])) {
                return array('type' => $_SESSION['notifcationType'], 'msg' => $_SESSION['notification']);
            } else {
                return array('type' => "", 'msg' => "");
            }
        }

        /**
         * displaySubmodule - display a submodule for a given module
         *
         * @access public
         * @param  $alias
         * @return void
         */
        public function displaySubmodule($alias)
        {

            $frontController = frontcontroller::getInstance(ROOT);
            $config = \leantime\core\environment::getInstance();
            $settings = new appSettings();
            $login = services\auth::getInstance();
            $roles = new roles();

            $submodule = array("module" => '', "submodule" => '');

            $aliasParts = explode("-", $alias);
            if (count($aliasParts) > 1) {
                $submodule['module'] = $aliasParts[0];
                $submodule['submodule'] = $aliasParts[1];
            }

            $relative_path = self::dispatch_filter(
                'submodule_relative_path',
                "{$submodule['module']}/templates/submodules/{$submodule['submodule']}.sub.php",
                [
                                'module' => $submodule['module'],
                                'submodule' => $submodule['submodule']
                            ]
            );

            $file = "../custom/domain/$relative_path";

            if (!file_exists($file)) {
                $file = "../src/domain/$relative_path";
            }

            if (file_exists($file)) {
                include $file;
            }
        }

        public function displayNotification()
        {

            $notification = '';
            $note = $this->getNotification();
            $language = $this->language;

            foreach (
                [
                'message',
                "message_{$note['msg']}"
                ] as $filter
            ) {
                $message = self::dispatch_filter(
                    $filter,
                    $language->__($note['msg'], false),
                    $note
                );
            }

            if (!empty($note) && $note['msg'] != '' && $note['type'] != '') {
                $notification = '<script type="text/javascript">
                                  jQuery.jGrowl("' . $message . '", {theme: "' . $note['type'] . '"});
                                </script>';

                $_SESSION['notification'] = "";
                $_SESSION['notificationType'] = "";
            }

            return $notification;
        }

        public function displayInlineNotification()
        {

            $notification = '';
            $note = $this->getNotification();
            $language = $this->language;

            foreach (
                [
                'message',
                "message_{$note['msg']}"
                ] as $filter
            ) {
                $message = self::dispatch_filter(
                    $filter,
                    $language->__($note['msg'], false),
                    $note
                );
            }

            if (!empty($note) && $note['msg'] != '' && $note['type'] != '') {
                $notification = "<div class='inputwrapper login-alert login-" . $note['type'] . "'>
                                    <div class='alert alert-" . $note['type'] . "'>
                                        " . $message . "
                                    </div>
								</div>
								";

                $_SESSION['notification'] = "";
                $_SESSION['notificationType'] = "";
            }

            return $notification;
        }

        public function redirect($url): void
        {

            header("Location:" . trim($url));
            exit();
        }

        public function getSubdomain(): string
        {

            preg_match('/(?:http[s]*\:\/\/)*(.*?)\.(?=[^\/]*\..{2,5})/i', $_SERVER['HTTP_HOST'], $match);

            $domain = $_SERVER['HTTP_HOST'];
            $tmp = explode('.', $domain); // split into parts
            $subdomain = $tmp[0];

            return $subdomain;
        }

        public function __($index): string
        {

            return $this->language->__($index);
        }

        //Echos and escapes content
        public function e($content): void
        {

            $content = $this->convertRelativePaths($content);
            $escaped = $this->escape($content);

            echo $escaped;
        }

        public function escape($content): string
        {

            if (!is_null($content)) {
                $content = $this->convertRelativePaths($content);
                return htmlentities($content);
            }

            return '';
        }

        public function escapeMinimal($content): string
        {

            $content = $this->convertRelativePaths($content);
            $config = array(
                'safe' => 1,
                'style_pass' => 1,
                'cdata' => 1,
                'comment' => 1,
                'deny_attribute' => '* -href -style',
                'keep_bad' => 0);

            if (!is_null($content)) {
                return htmLawed($content, array('valid_xhtml' => 1));
            }

            return '';
        }

        /**
         * getFormattedDateString - returns a language specific formatted date string. wraps language class method
         *
         * @access public
         * @param $date string
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

        public function get24HourTimestring($dateTime)
        {
            return $this->language->get24HourTimestring($dateTime);
        }

        //Credit goes to Søren Løvborg (https://stackoverflow.com/users/136796/s%c3%b8ren-l%c3%b8vborg)
        //https://stackoverflow.com/questions/1193500/truncate-text-containing-html-ignoring-tags
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

            if (strlen($truncate) > $maxLength) {
                $truncate .= $ending;
            }

            return $truncate;
        }

        public function convertRelativePaths($text)
        {
            if (is_null($text)) {
                return $text;
            }

            $base = BASE_URL;

            // base url needs trailing /
            if (substr($base, -1, 1) != "/") {
                $base .= "/";
            }

            // Replace links
            $pattern = "/<a([^>]*) " .
                    "href=\"([^http|ftp|https|mailto|#][^\"]*)\"/";
            $replace = "<a\${1} href=\"" . $base . "\${2}\"";
            $text = preg_replace($pattern, $replace, $text);

            // Replace images
            $pattern = "/<img([^>]*) " .
                    "src=\"([^http|ftp|https][^\"]*)\"/";
            $replace = "<img\${1} src=\"" . $base . "\${2}\"";

            $text = preg_replace($pattern, $replace, $text);
            // Done
            return $text;
        }

        public function getModulePicture()
        {

            $module = frontcontroller::getModuleName($this->template);

            $picture = $this->picture['default'];
            if (isset($this->picture[$module])) {
                $picture = $this->picture[$module];
            }

            return $picture;
        }

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

        /*         * *
         * patchDownloadUrlToFilenameOrAwsUrl- Replace all local download.php references in <img src=""> tags
         *     by either local filenames or AWS URLs that can be accesse without being authenticated
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
         * @param mixed $payload
         */
        private function dispatchTplEvent($hookName, $payload = [])
        {
            $this->dispatchTplHook('event', $hookName, $payload);
        }

        /**
         * @param string $hookName
         * @param mixed $payload
         * @param mixed $available_params
         *
         * @return mixed
         */
        private function dispatchTplFilter($hookName, $payload = [], $available_params = [])
        {
            return $this->dispatchTplHook('filter', $hookName, $payload, $available_params);
        }

        /**
         * @param string $type
         * @param string $hookName
         * @param mixed $payload
         * @param mixed $available_params
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

}
