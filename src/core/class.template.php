<?php

/**
 * Template class - Template routing
 *
 */

namespace leantime\core {

    use leantime\domain\repositories;

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
        private $controller = '';

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
            $this->controller = frontcontroller::getInstance();

            $this->language = new language();

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
         * display - display template from folder template including main layout wrapper
         *
         * @access public
         * @param  $template
         * @return void
         */
        public function display($template)
        {

            //These variables are available in the template
            $frontController = frontcontroller::getInstance(ROOT);
            $config = new config();
            $settings = new settings();
            $login = login::getInstance();
            $language = $this->language;

            $this->template = $template;

            require ROOT.'/../src/content.php';

            $mainContent = ob_get_clean();
            ob_start();

            //frontcontroller splits the name (actionname.modulename)
            $action = frontcontroller::getActionName($template);

            $module = frontcontroller::getModuleName($template);

            $strTemplate = '../src/domain/' . $module . '/templates/' . $action.'.tpl.php';
            if ((! file_exists($strTemplate)) || ! is_readable($strTemplate)) {
                throw new Exception($this->__("notifications.no_template"));
            }

            include $strTemplate;

            $subContent = ob_get_clean();

            $content = str_replace("<!--###MAINCONTENT###-->", $subContent, $mainContent);

            echo $content;

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

            //These variables are available in the template
            $frontController = frontcontroller::getInstance(ROOT);
            $config = new config();
            $settings = new settings();
            $login = login::getInstance();

            $this->template = $template;

            //frontcontroller splits the name (actionname.modulename)
            $action = frontcontroller::getActionName($template);

            $module = frontcontroller::getModuleName($template);

                $strTemplate = '../src/domain/' . $module . '/templates/' . $action.'.tpl.php';

            if ((! file_exists($strTemplate)) || ! is_readable($strTemplate)) {

                error_log($this->__("notifications.no_template"), 0);
                echo $this->__("notifications.no_template");

            } else {

                include $strTemplate;

            }

        }


        /**
         * includeAction - possible to include Actions from erverywhere
         *
         * @access public
         * @param  $completeName
         * @return void
         */
        public function includeAction($completeName)
        {

            $this->controller->includeAction($completeName);

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
            $settings = new settings();
            $login = login::getInstance();


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

        /**
         * displayLink
         */
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

        public function displayNotification()
        {

            $notification = '';
            $note = $this->getNotification();
            $language = $this->language;

            $alertIcons = array(
                "success" => '<i class="far fa-check-circle"></i>',
                "error" => '<i class="fas fa-exclamation-triangle"></i>',
                "info" => '<i class="fas fa-info-circle"></i>'
            );

            if (!empty($note) && $note['msg'] != '' && $note['type'] != '') {

                $notification = "<div class='alert alert-".$note['type']."'>
                                    <div class='infoBox'>
                                        ".$alertIcons[$note['type']]."
                                    </div>
								<button data-dismiss='alert' class='close' type='button'>×</button>
								<div class='alert-content'><h4>"
                    .ucfirst($note['type']).
                    "!</h4>"
                    .$language->__($note['msg'], false).
                    "
								</div>
								<div class='clearall'></div>
							</div>";

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

            return htmlentities($content);

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

            $truncate .= $ending;

            return $truncate;
        }



    }

}
