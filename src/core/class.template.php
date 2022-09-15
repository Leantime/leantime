<?php

/**
 * Template class - Template routing
 *
 */

namespace leantime\core {

    use JetBrains\PhpStorm\NoReturn;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services;

    class template
    {

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

        private $validStatusCodes = array("100","101","200","201","202","203","204","205","206","300","301","302","303","304","305","306","307","400","401","402","403","404","405","406","407","408","409","410","411","412","413","414","415","416","417","500","501","502","503","504","505");


        public $picture = array(
            'calendar'    => 'iconfa-calendar',
            'clients'     => 'iconfa-group',
            'dashboard' => 'iconfa-th-large',
            'files'     => 'iconfa-picture',
            'leads'     => 'iconfa-signal',
            'messages'     => 'iconfa-envelope',
            'projects'     => 'iconfa-bar-chart',
            'setting'    => 'iconfa-cogs',
            'tickets'    => 'iconfa-pushpin',
            'timesheets'=> 'iconfa-table',
            'users'        => 'iconfa-group',
            'default'    => 'iconfa-off'
        );

        /**
         * __construct - get instance of frontcontroller
         *
         * @access public
         */
        public function __construct()
        {
            $this->language = new language();
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

            $this->vars[$name] = $value;

        }

        /**
         * setError - assign errors to the template
         *
         * @param  $msg
         * @param  $type
         * @return string
         */
        public function setNotification($msg,$type)
        {

            $_SESSION['notification'] = $msg;
            $_SESSION['notifcationType'] = $type;

        }

        /**
         * display - display template from folder template including main layout wrapper
         *
         * @access public
         * @param  $template
         * @return void
         */
        public function display($template, $status = 200, $layout = "app")
        {

            //These variables are available in the template
            $config = new config();
            $settings = new appSettings();
            $login = services\auth::getInstance();
            $roles = new roles();

            $language = $this->language;

            $this->template = $template;

            //http_response_code($this->validStatusCodes[$status] ?? 200);

            //Load Layout file
            ob_start();

            $layout = htmlspecialchars($layout);

            if(file_exists(ROOT.'/../src/layouts/'.$layout.'.php')) {
                require ROOT . '/../src/layouts/'.$layout.'.php';
            }else{
                require ROOT . '/../src/layouts/app.php';
            }

            $layoutContent = ob_get_clean();

            //Load Template
            ob_start();

            //frontcontroller splits the name (actionname.modulename)
            $action = $this->frontcontroller::getActionName($template);

            $module = $this->frontcontroller::getModuleName($template);

            $strTemplate = ROOT.'/../src/domain/' . $module . '/templates/' . $action.'.tpl.php';

            if ((!file_exists($strTemplate)) || !is_readable($strTemplate)) {
                throw new \Exception($this->__("notifications.no_template"));
            }

            require_once $strTemplate;

            $content = ob_get_clean();

            //Load template content into layout content
            $render = str_replace("<!--###MAINCONTENT###-->", $content, $layoutContent);

            echo $render;

        }

        /**
         * displayJson - returns json data
         *
         * @access public
         * @param  $jsonContent
         * @return void
         */
        public function displayJson($jsonContent) {

            header('Content-Type: application/json; charset=utf-8');
            if($jsonContent !== false) {
                echo $jsonContent;
            }else{
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
        public function displayPartial($template, $statusCode = 200)
        {

            $this->display($template, $statusCode, 'blank');

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

            if (! isset($this->vars[$name])) {

                return null;
            }

            return $this->vars[$name];
        }

        public function getNotification()
        {

            if(isset($_SESSION['notifcationType']) && isset($_SESSION['notification'])) {

                return array('type' => $_SESSION['notifcationType'], 'msg' => $_SESSION['notification']);

            }else{

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
            $config = new config();
            $settings = new appSettings();
            $login = services\auth::getInstance();
            $roles = new roles();


            $submodule = array("module"=>'', "submodule"=>'');

            $aliasParts = explode("-", $alias);
            if(count($aliasParts) > 1) {
                $submodule['module'] = $aliasParts[0];
                $submodule['submodule'] = $aliasParts[1];
            }

            $file = '../src/domain/'.$submodule['module'].'/templates/submodules/'.$submodule['submodule'].'.sub.php';

            if (file_exists($file)) {

                include $file;

            }

        }

        public function displayNotification()
        {

            $notification = '';
            $note = $this->getNotification();
            $language = $this->language;

            if (!empty($note) && $note['msg'] != '' && $note['type'] != '') {

                $notification = '<script type="text/javascript">
                                  jQuery.jGrowl("'.$language->__($note['msg'], false).'", {theme: "'.$note['type'].'"});
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


            if (!empty($note) && $note['msg'] != '' && $note['type'] != '') {

                $notification = "<div class='inputwrapper login-alert login-".$note['type']."'>
                                    <div class='alert alert-".$note['type']."'>
                                        ".$language->__($note['msg'], false)."
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

            header("Location:".trim($url));
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


            $escaped = $this->escape($content);

            echo $escaped;

        }

        public function escape($content): string
        {

            if(!is_null($content)) {
                return htmlentities($content);
            }

            return '';

        }

        public function escapeMinimal($content): string
        {

            $config = array(
                'safe'=>1,
                'style_pass'=>1,
                'cdata'=>1,
                'comment'=>1,
                'deny_attribute'=>'* -href -style',
                'keep_bad'=>0);

            if(!is_null($content)) {
                return htmLawed($content, array('valid_xhtml'=>1));
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

           return  $this->language->getFormattedDateString($date);

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

            return  $this->language->getFormattedTimeString($date);

        }

        //Credit goes to Søren Løvborg (https://stackoverflow.com/users/136796/s%c3%b8ren-l%c3%b8vborg)
        //https://stackoverflow.com/questions/1193500/truncate-text-containing-html-ignoring-tags
        public function truncate($html, $maxLength = 100, $ending = '(...)', $exact = true, $considerHtml = false) {
            $printedLength = 0;
            $position = 0;
            $tags = array();
            $isUtf8 = true;
            $truncate = "";

            // For UTF-8, we need to count multibyte sequences as one character.
            $re = $isUtf8
                ? '{</?([a-z]+)[^>]*>|&#?[a-zA-Z0-9]+;|[\x80-\xFF][\x80-\xBF]*}'
                : '{</?([a-z]+)[^>]*>|&#?[a-zA-Z0-9]+;}';

            while ($printedLength < $maxLength && preg_match($re, $html, $match, PREG_OFFSET_CAPTURE, $position))
            {
                list($tag, $tagPosition) = $match[0];

                // Print text leading up to the tag.
                $str = substr($html, $position, $tagPosition - $position);
                if ($printedLength + strlen($str) > $maxLength)
                {
                    $truncate .= substr($str, 0, $maxLength - $printedLength);
                    $printedLength = $maxLength;
                    break;
                }

                $truncate .= $str;
                $printedLength += strlen($str);
                if ($printedLength >= $maxLength) break;

                if ($tag[0] == '&' || ord($tag) >= 0x80)
                {
                    // Pass the entity or UTF-8 multibyte sequence through unchanged.
                    $truncate .= $tag;
                    $printedLength++;
                }
                else
                {
                    // Handle the tag.
                    $tagName = $match[1][0];
                    if ($tag[1] == '/')
                    {
                        // This is a closing tag.

                        $openingTag = array_pop($tags);
                        assert($openingTag == $tagName); // check that tags are properly nested.

                        $truncate .= $tag;
                    }
                    elseif ($tag[strlen($tag) - 2] == '/')
                    {
                        // Self-closing tag.
                        $truncate .= $tag;
                    }
                    else
                    {
                        // Opening tag.
                        $truncate .= $tag;
                        $tags[] = $tagName;
                    }
                }

                // Continue after the tag.
                $position = $tagPosition + strlen($tag);
            }

            // Print any remaining text.
            if ($printedLength < $maxLength && $position < strlen($html))
                $truncate .= sprintf(substr($html, $position, $maxLength - $printedLength));

            // Close any open tags.
            while (!empty($tags))
                $truncate .= sprintf('</%s>', array_pop($tags));

            if(strlen($truncate)>$maxLength) {
                $truncate .= $ending;
            }

            return $truncate;
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

            if(is_array($mod) === true && count($mod) == 2) {

                $action = $mod[1];
                $module = $mod[0];

                $mod = $module.'/class.'.$action.'.php';

            }else{

                $mod = array();
                return false;

            }

            $returnLink = false;

            $url = "/".$module."/".$action."/";

            if (!empty($params)) {

                foreach ($params as $key => $value) {
                    $url .= $value."/";
                }
            }

            $attr = '';

            if ($attribute!=null) {

                foreach ($attribute as $key => $value){
                    $attr .= $key." = '".$value."' ";
                }
            }

            $returnLink = "<a href='".BASE_URL."".$url."' ".$attr.">".$name."</a>";

            return $returnLink;
        }


    }

}
