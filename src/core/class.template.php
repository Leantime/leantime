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
            $this->controller = FrontController::getInstance();

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

            include '../src/content.php';

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

                throw ($this->__("notifications.no_template"));

            } else {

                include $strTemplate;

            }

            return;
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

        public function displaySubmoduleTitle($alias)
        {

            $setting = new repositories\setting();
            $language = $this->language;

            $title = '';

            if ($setting->submoduleHasRights($alias) !== false) {

                $submodule = $setting->getSubmodule($alias);

                if ($submodule['title'] !== '') {

                    $title = $this->__($submodule['title']);

                } else {

                    $title = ucfirst(str_replace('.sub.php', $submodule['submodule']));

                }
            }

            return $title;
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

        public function redirect($url)
        {

            header("Location:".trim($url));
            exit();
        }

        public function getSubdomain()
        {

            preg_match('/(?:http[s]*\:\/\/)*(.*?)\.(?=[^\/]*\..{2,5})/i', $_SERVER['HTTP_HOST'], $match);

            $domain = $_SERVER['HTTP_HOST'];
            $tmp = explode('.', $domain); // split into parts
            $subdomain = $tmp[0];

            return $subdomain;

        }

        public function __($index){

            return $this->language->__($index);

        }

        //Echos and escapes content
        public function e($content) {

            $escaped = $this->escape($content);

            echo $escaped;

        }

        public function escape($content) {

            return htmlentities($content);

        }

        /**
         * getFormattedDateString - returns a language specific formatted date string. wraps language class method
         *
         * @access public
         * @param $date string
         * @return string
         */
        public function getFormattedDateString($date) {

           return  $this->language->getFormattedDateString($date);

        }

        /**
         * getFormattedTimeString - returns a language specific formatted time string. wraps language class method
         *
         * @access public
         * @param $date string
         * @return string
         */
        public function getFormattedTimeString($date) {

            return  $this->language->getFormattedTimeString($date);

        }



    }

}
