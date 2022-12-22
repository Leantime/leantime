<?php

/**
 * core/theme - Engine for handling themes
 */

namespace leantime\core {

    use leantime\core\eventhelpers;

    class theme {

        use eventhelpers;

        public const DEFAULT = 'default';            // Name of default theme
        public const DEFAULT_INI = 'theme';          // Theme configuration file (excluding .ini extension)
        public const DEFAULT_CSS = 'theme';          // Theme style file  (excluding .css extension)
        public const DEFAULT_JS = 'theme';           // Theme JavasCript library (excluding .js extension)
        public const CUSTOM_CSS = 'custom';          // Theme style customination file (excluding .css)
        public const CUSTOM_JS = 'custom';           // Theme JavaScript customination file (excluding .js)

        private environment $config;
        private appSettings $settings;
        private array|false $iniData;

        /**
         * __construct - Constructor
         */
        function __construct() {
            $this->config = \leantime\core\environment::getInstance();
            $this->settings = new appSettings();
            $this->iniData = [];
        }

        /**
         * getActive - Return active theme id
         *
         * @access public
         * @return string Active theme identifier
         */
        public function getActive(): string {

            // Reset .ini data
            $this->iniData = [];
            // Return user specific theme, if active
            if (isset($_SESSION["userdata"]["id"])) {

                if (isset($_SESSION["usersettings." . $_SESSION["userdata"]["id"] . ".theme"]))
                    return $_SESSION["usersettings." . $_SESSION["userdata"]["id"] . ".theme"];
            }

            //Return generic theme
            if (isset($_SESSION['usersettings.theme']))
                return $_SESSION['usersettings.theme'];

            //Return saved
            if ($this->config->keepTheme && isset($_COOKIE['theme']))
                return $_COOKIE['theme'];

            //Return configured
            if (isset($this->config->defaultTheme) && !empty($this->config->defaultTheme))
                return $this->config->defaultTheme;

            //Return default
            return static::DEFAULT;
        }

        /**
         * setActive - Set active theme
         *
         * Note: After setActive, the language settings need to be reloaded/reset, because languages are theme specific
         *
         * @access public
         * @param  string $id Active theme identifier
         */
        public function setActive(string $id): void {

            if (!is_dir(ROOT . '/theme/' . $id) || !file_exists(ROOT . '/theme/' . $id . '/' . static::DEFAULT_INI . '.ini')) {
                throw new \Exception("Selected theme '$id' does not exist");
            }

            if (isset($_SESSION["userdata"]["id"])) {

                $_SESSION["usersettings." . $_SESSION["userdata"]["id"] . ".theme"] = $id;
            }

            $_SESSION['usersettings.theme'] = $id;
            setcookie(
                    'theme',
                    $id,
                    [
                        'expires' => time() + 60 * 60 * 24 * 30,
                        'path' => $this->config->appUrlRoot . '/',
                        'samesite' => 'Strict'
                    ]
            );
        }

        /**
         * getAll - Return an array of all themes
         *
         * @access public
         * @return Return an array of all themes
         */
        public function getAll(): array {

            $language = language::getInstance();
            $themeRoot = ROOT . '/theme/';
            $themeAll = [];
            $themeAll[static::DEFAULT] = 'theme.' . static::DEFAULT . '.name';

            $themeDirs = opendir($themeRoot);
            while (($theme = readdir($themeDirs)) !== false) {
                if ($theme !== 'sample' && is_dir(ROOT . '/theme/' . $theme) &&
                        file_exists(ROOT . '/theme/' . $theme . '/' . static::DEFAULT_INI . '.ini')) {

                    $iniData = parse_ini_file(ROOT . '/theme/' . $theme . '/' . static::DEFAULT_INI . '.ini', true, INI_SCANNER_TYPED);
                    if (isset($iniData['name'][$language->getCurrentLanguage()])) {

                        $themeAll[$theme] = $iniData['name'][$language->getCurrentLanguage()];
                    } elseif (isset($iniData['name']['en-US'])) {

                        $themeAll[$theme] = $iniData['name']['en-US'];
                    } else {

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
        public function getDir(): string {

            return ROOT . '/theme/' . $this->getActive();
        }

        /**
         * getDir - Return the root directory of the default theme
         *
         * @access public
         * @return string Root directory of default theme
         */
        public function getDefaultDir(): string {

            return ROOT . '/theme/' . static::DEFAULT;
        }

        /**
         * getLayoutDir - Return file path of a layout file in the current theme, reverting to the default theme if it does not exist
         *
         * @access public
         * @param  string $filename Filename of layout to look for
         * @return string|false Full filename of layout file or false, if it does not exist
         */
        public function getLayoutFilename(string $filename): string|false {

            $theme_layout = $this->getDir() . '/layout/' . $filename;
            $plugin_layout = self::dispatch_filter('filepath', $filename);
            $default_theme_layout = $this->getDefaultDir() . '/layout/' . $filename;

            if (file_exists($theme_layout)) {
                return $theme_layout;
            }

            if (file_exists($plugin_layout)) {
                return $plugin_layout;
            }

            if (file_exists($default_theme_layout)) {
                return $default_theme_layout;
            }

            return false;
        }

        /**
         * getUrl() - Return an URL pointing to the root directory of the currently active theme
         *
         * @access public
         * @return string Root URL currently active theme
         */
        public function getUrl(): string {

            return $this->config->appUrl . '/theme/' . $this->getActive();
        }

        /**
         * getDefaultUrl() - Return an URL pointing to the root directory of the default theme
         *
         * @access public
         * @return string Root URL default theme
         */
        public function getDefaultUrl(): string {

            return $this->config->appUrl . '/theme/' . static::DEFAULT;
        }

        /**
         * getStyleUrl - Return URL that allows loading the style file of the theme
         *
         * @access public
         * @return string|false URL to the css style file of the current theme or false, if it does not exist
         */
        public function getStyleUrl(): string|false {

            if (file_exists($this->getDir() . '/css/' . static::DEFAULT_CSS . '.min.css')) {
                return $this->getUrl() . '/css/' . static::DEFAULT_CSS . '.min.css?v=' . $this->settings->appVersion;
            }
            if (file_exists($this->getDir() . '/css/' . static::DEFAULT_CSS . '.css')) {
                return $this->getUrl() . '/css/' . static::DEFAULT_CSS . '.css?v=' . $this->settings->appVersion;
            }
            return false;
        }

        /**
         * getCustomStyleUrl - Return URL that allows loading the customized part of the style file of the theme
         *
         * @access public
         * @return string|false URL to the customized part of the css style file of the current theme or false, if it does not exist
         */
        public function getCustomStyleUrl(): string|false {

            if (file_exists($this->getDir() . '/css/' . static::CUSTOM_CSS . '.min.css')) {
                return $this->getUrl() . '/css/' . static::CUSTOM_CSS . '.min.css?v=' . $this->settings->appVersion;
            }
            if (file_exists($this->getDir() . '/css/' . static::CUSTOM_CSS . '.css')) {
                return $this->getUrl() . '/css/' . static::CUSTOM_CSS . '.css?v=' . $this->settings->appVersion;
            }

            return false;
        }

        /**
         * getJsUrl - Return URL that allows loading the JavaScript file of the theme
         *
         * @access public
         * @return string|false URL to the JavaScript file of the current theme or false, if it does not exist
         */
        public function getJsUrl(): string|false {

            if (file_exists($this->getDir() . '/js/' . static::DEFAULT_JS . '.min.js')) {
                return $this->getUrl() . '/js/' . static::DEFAULT_JS . '.min.js?v=' . $this->settings->appVersion;
            }
            if (file_exists($this->getDir() . '/js/' . static::DEFAULT_JS . '.js')) {
                return $this->getUrl() . '/js/' . static::DEFAULT_JS . '.js?v=' . $this->settings->appVersion;
            }
            return false;
        }

        /**
         * getCustomJsUrl - Return URL that allows loading the customized part of the JavaScript file of the theme
         *
         * @access public
         * @return string|false URL to the customized part of the JavaScript file of the current theme or false, if it does not exist
         */
        public function getCustomJsUrl(): string|false {

            if (file_exists($this->getDir() . '/js/' . static::CUSTOM_JS . '.min.js')) {
                return $this->getUrl() . '/js/' . static::CUSTOM_JS . '.min.js?v=' . $this->settings->appVersion;
            }

            if (file_exists($this->getDir() . '/js/' . static::CUSTOM_JS . '.js')) {
                return $this->getUrl() . '/js/' . static::CUSTOM_JS . '.js?v=' . $this->settings->appVersion;
            }

            return false;
        }

        /**
         * getJslibUrl - Return URL that allows loading the JavaScript library of the theme
         *
         * @access public
         * @return string|false URL to the JavaScript of the current theme or false, if it does not exist
         */
        public function getJslibUrl(): string|false {

            if (file_exists($this->getDir() . '/js/' . static::DEFAULT_JSLIB . '.min.js')) {
                return $this->getUrl() . '/js/' . static::DEFAULT_JSLIB . '.min.js?v=' . $this->settings->appVersion;
            }

            if (file_exists($this->getDir() . '/js/' . static::DEFAULT_JSLIB . '.js')) {
                return $this->getUrl() . '/js/' . static::DEFAULT_JSLIB . '.js?v=' . $this->settings->appVersion;
            }

            return false;
        }

        /**
         * getName - Get localized name of theme
         *
         * @access public
         * @return string Localized name of theme
         */
        public function getName(): string {

            // Make sure we get the current language
            $language = language::getInstance();

            if (empty($this->iniData))
                $this->readIniData();

            if (isset($this->iniData['name'][$language->getCurrentLanguage()])) {

                return $iniData['name'][$language->getCurrentLanguage()];
            }

            if (isset($this->iniData['name']['en-US'])) {

                return $this->iniData['name']['en-US'];
            }

            return $language->__("theme." . $this->getActive() . "name");
        }

        /**
         * getVersion - Get version of theme
         *
         * @access public
         * @return string Version of theme or empty string
         */
        public function getVersion(): string {

            if (empty($this->iniData))
                $this->readIniData();

            if (isset($this->iniData['general']['version']))
                return $this->iniData['general']['version'];

            return '';
        }

        /**
         * getLogoUrl - Get logo associated with the theme
         *
         * @access public
         * @return string Logo associated with the theme
         */
        public function getLogoUrl(): string|false {

            if (empty($this->iniData))
                $this->readIniData();

            if (isset($this->iniData['general']['logo']))
                return $this->iniData['general']['logo'];

            return false;
        }

        /*         * *
         * readIniData - Read theme.ini configuration data
         *
         * @access private
         */

        private function readIniData(): void {
            if (!file_exists(ROOT . '/theme/' . $this->getActive() . '/' . static::DEFAULT_INI . '.ini')) {

                throw Exception("Configuration file for theme " . $this->getActive() . " not found");
            }
            $this->iniData = parse_ini_file(ROOT . '/theme/' . $this->getActive() . '/' . static::DEFAULT_INI . '.ini', true, INI_SCANNER_TYPED);
            if ($this->iniData === false)
                $this->iniData = [];
        }

    }

}
