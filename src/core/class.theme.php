<?php
/**
 * core/theme - Engine for handling themes
 */

namespace leantime\core {

	class theme {

		public const DEFAULT = 'default';            // Name of default theme
		
		public const DEFAULT_INI = 'theme';          // Theme configuration file (excluding .ini extension)
		public const DEFAULT_CSS = 'theme';          // Theme style file  (excluding .css extension)
		public const DEFAULT_JSLIB = 'theme';        // Theme JavasCript library (excluding .js extension)
		
		public const CUSTOM_CSS = 'custom';          // Theme style customination file (excluding .css)

        private config $config;
        private appSettings $settings;


        /**
         * __construct - Constructor
         */
        function __construct()
        {
            $this->config = new config();
            $this->settings = new appSettings();
        }
        
		/**
		 * getActive - Return active theme id
		 *
		 * @access public
		 * @return string Active theme identifier
		 */
		public function getActive(): string
		{

			if(!isset($_SESSION['usersettings.theme'])) {

                if(isset($this->config->defaultTheme) && !empty($this->config->defaultTheme)) {
                    
                    $_SESSION['usersettings.theme'] = $this->config->defaultTheme;

                }else{
                    
                    $_SESSION['usersettings.theme'] = static::DEFAULT;

                }
                
			}
			
			return $_SESSION['usersettings.theme'];
			
		}

		/**
		 * setActive - Set active theme
		 *
		 * @access public
		 * @param  string $id Active theme identifier
		 */
		public function setActive(string $id): void
		{

			if(!is_dir(ROOT.'/theme/'.$id) || !file_exists(ROOT.'/theme/'.$id.'/'.static::DEFAULT_INI.'.ini')) {
                throw new \Exception("Selected theme '$id' does not exist");
            }

			$_SESSION['usersettings.theme'] = $id;

		}
        
		/**
         * getAll - Return an array of all themes
         *
         * @access public
         * @return Return an array of all themes
         */
        public function getAll(): array
        {

            $language = new language();
            $themeRoot = ROOT.'/theme/';
            $themeAll = [];
            $themeAll[static::DEFAULT] = 'theme.'.static::DEFAULT.'.name';

            $themeDirs = opendir($themeRoot);
            while(($theme = readdir($themeDirs)) !== false) {
                
                if($theme !== 'sample' && is_dir(ROOT.'/theme/'.$theme) &&
                   file_exists(ROOT.'/theme/'.$theme.'/'.static::DEFAULT_INI.'.ini')) {

                    $iniData = parse_ini_file(ROOT.'/theme/'.$theme.'/'.static::DEFAULT_INI.'.ini', true);
                    if(isset($iniData['name'][$language->getCurrentLanguage()])) {
                            
                        $themeAll[$theme] = $iniData['name'][$language->getCurrentLanguage()];
                        
                    }elseif(isset($iniData['name']['en-US'])) {
                            
                        $themeAll[$theme] = $iniData['name']['en-US'];

                    }else{
                            
                        $themeAll[$theme] = $language->__("theme.$theme.name");

                    }                        

                }
            }
            
            closedir($themeDirs);
            return $themeAll;
        }

        /**
         * getDir - Return the root directory of the currently active theme
         *
         * @access public
         * @return string Root directory of currently active theme
         */
        public function getDir(): string
        {

            return ROOT.'/theme/'.$this->getActive();
            
        }

        /**
         * getDir - Return the root directory of the default theme
         *
         * @access public
         * @return string Root directory of default theme
         */
        public function getDefaultDir(): string
        {

            return ROOT.'/theme/'.static::DEFAULT;
            
        }
        
        /**
         * getLayoutDir - Return file path of a layout file in the current theme, reverting to the default theme if it does not exist
         *
         * @access public
         * @param  string $filename Filename of layout to look for
         * @return string|false Full filename of layout file or false, if it does not exist
         */
        public function getLayoutFilename(string $filename): string|false
        {

            if(file_exists($this->getDir().'/layout/'.$filename)) {
                return $this->getDir().'/layout/'.$filename;
            }
            
            if(file_exists($this->getDefaultDir().'/layout/'.$filename)) {
                return $this->getDefaultDir().'/layout/'.$filename;
            }

            return false;
            
        }

        /**
         * getUrl() - Return an URL pointing to the root directory of the currently active theme
         *
         * @access public
         * @return string Root URL currently active theme
         */
        public function getUrl(): string
        {

            return $this->config->appUrl.'/theme/'.$this->getActive();
            
        }

        /**
         * getDefaultUrl() - Return an URL pointing to the root directory of the default theme
         *
         * @access public
         * @return string Root URL default theme
         */
        public function getDefaultUrl(): string
        {

            return $this->config->appUrl.'/theme/'.static::DEFAULT;
            
        }

        /**
         * getStyleUrl - Return URL that allows loading the style file of the theme
         *
         * @access public
         * @return string|false URL to the css style file of the current theme or false, if it does not exist
         */
        public function getStyleUrl(): string|false
        {

            if(file_exists($this->getDir().'/css/'.static::DEFAULT_CSS.'.min.css')) {
                return $this->getUrl().'/css/'.static::DEFAULT_CSS.'.min.css?v='.$this->settings->appVersion;
            }
                                      
            if(file_exists($this->getDir().'/css/'.static::DEFAULT_CSS.'.css')) {
                return $this->getUrl().'/css/'.static::DEFAULT_CSS.'.css?v='.$this->settings->appVersion;
            }
            return false;
            
        }

        /**
         * getCustomStyleUrl - Return URL that allows loading the customozed part of the style file of the theme
         *
         * @access public
         * @return string|false URL to the customized part of the css style file of the current theme or false, if it does not exist
         */
        public function getCustomStyleUrl(): string|false
        {

            if(file_exists($this->getDir().'/css/'.static::CUSTOM_CSS.'.min.css')) {
                return $this->getUrl().'/css/'.static::CUSTOM_CSS.'.min.css?v='.$this->settings->appVersion;
            }
                                      
            if(file_exists($this->getDir().'/css/'.static::CUSTOM_CSS.'.css')) {
                return $this->getUrl().'/css/'.static::CUSTOM_CSS.'.css?v='.$this->settings->appVersion;
            }

            return false;

        }
        
        /**
         * getJslibUrl - Return URL that allows loading the JavaScript library of the theme
         *
         * @access public
         * @return string|false URL to the JavaScript of the current theme or false, if it does not exist
         */
        public function getJslibUrl(): string|false
        {

            if(file_exists($this->getDir().'/js/'.static::DEFAULT_JSLIB.'.min.js')) {
                return $this->getUrl().'/js/'.static::DEFAULT_JSLIB.'.min.js?v='.$this->settings->appVersion;
            }
                                      
            if(file_exists($this->getDir().'/js/'.static::DEFAULT_JSLIB.'.js')) {
                return $this->getUrl().'/js/'.static::DEFAULT_JSLIB.'.js?v='.$this->settings->appVersion;
            }

            return false;

        }
        
	}
}

