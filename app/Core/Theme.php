<?php

namespace Leantime\Core;

use Exception;

/**
 * theme - Engine for handling themes
 */
class Theme
{
    use Eventhelpers;

    /**
     * Name of default theme
     *
     * @var string
     * @static
     * @final
     */
    public const DEFAULT = 'default';

    /**
     * Theme configuration file (excluding .ini extension)
     *
     * @var string
     * @static
     * @final
     */
    public const DEFAULT_INI = 'theme';

    /**
     * Theme style file (excluding .css extension)
     *
     * @var string
     * @static
     * @final
     */
    public const DEFAULT_CSS = 'theme';

    /**
     * Theme JavaScript library (excluding .js extension)
     *
     * @var string
     * @access public
     * @static
     * @final
     */
    public const DEFAULT_JS = 'theme';

    /**
     * Theme style customization file (excluding .css extension)
     *
     * @var string
     * @access public
     * @static
     * @final
     */
    public const CUSTOM_CSS = 'custom';

    /**
     * Theme JavaScript customization file (excluding .js extension)
     *
     * @var string
     * @access public
     * @static
     * @final
     */
    public const CUSTOM_JS = 'custom';

    /**
     * @var environment
     */
    private Environment $config;

    /**
     * @var appSettings
     */
    private AppSettings $settings;

    /**
     * @var language
     */
    private Language $Language;

    /**
     * @var array|false
     */
    private array|false $iniData;

    /**
     * __construct - Constructor
     */
    public function __construct(
        environment $config,
        appSettings $settings,
        array $iniData = []
    ) {
        $this->config = $config;
        $this->settings = $settings;
        $this->iniData = [];
    }

    /**
     * getActive - Return active theme id
     *
     * @access public
     * @return string Active theme identifier
     */
    public function getActive(): string
    {

        // Reset .ini data
        $this->iniData = [];
        // Return user specific theme, if active
        if (isset($_SESSION["userdata"]["id"])) {
            if (isset($_SESSION["usersettings." . $_SESSION["userdata"]["id"] . ".theme"])) {
                return $_SESSION["usersettings." . $_SESSION["userdata"]["id"] . ".theme"];
            }
        }

        //Return generic theme
        if (isset($_SESSION['usersettings.theme'])) {
            return $_SESSION['usersettings.theme'];
        }

        //Return saved
        if ($this->config->keepTheme && isset($_COOKIE['theme'])) {
            return $_COOKIE['theme'];
        }

        //Return configured
        if (isset($this->config->defaultTheme) && !empty($this->config->defaultTheme)) {
            return $this->config->defaultTheme;
        }

        //Return default
        return static::DEFAULT;
    }

    /**
     * setActive - Set active theme
     *
     * Note: After setActive, the language settings need to be reloaded/reset, because languages are theme specific
     *
     * @access public
     * @param  string $id Active theme identifier.
     * @throws Exception Exception if theme does not exist.
     * @return void
     */
    public function setActive(string $id): void
    {

        if ($id == '') {
            $id = 'default';
        }

        if (!is_dir(ROOT . '/theme/' . $id) || !file_exists(ROOT . '/theme/' . $id . '/' . static::DEFAULT_INI . '.ini')) {
            throw new Exception("Selected theme '$id' does not exist");
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
                'path' => $this->config->appDir . '/',
                'samesite' => 'Strict',
            ]
        );
    }

    /**
     * getAll - Return an array of all themes
     *
     * @access public
     * @return array return an array of all themes
     */
    public function getAll(): array
    {
        $language = app()->make(Language::class);
        $theme = $this->getActive();

        $themes = [];
        $handle = opendir(ROOT . '/theme');
        if ($handle === false) {
            return $themes;
        }
        while (false !== ($themeDir = readdir($handle))) {
            if ($themeDir == '.' || $themeDir == '..') {
                continue;
            }
            if ($themeDir == $theme) {
                $themes[$themeDir] = $language->__("theme.name");
                continue;
            }

            //Check specific language file
            $language_file = ROOT
                . '/theme/'
                . $themeDir
                . '/language/'
                . $language->getCurrentLanguage()
                . '.ini';

            if (file_exists($language_file)) {
                $iniData = parse_ini_file(
                    $language_file,
                    true,
                    INI_SCANNER_RAW
                );

                if ($iniData['theme.name'] !== null) {
                    $themes[$themeDir] = $iniData['theme.name'];
                    continue;
                }
            }

            //Check english language file
            $language_file = ROOT
                . '/theme/'
                . $themeDir
                . '/language/en-Us.ini';

            if (file_exists($language_file)) {
                $iniData = parse_ini_file(
                    $language_file,
                    true,
                    INI_SCANNER_RAW
                );

                if ($iniData['theme.name'] !== null) {
                    $themes[$themeDir] = $iniData['theme.name'];
                    continue;
                }
            }

            //Else use directory name
            $themes[$themeDir] = $themeDir;
        }

        return $themes;
    }

    /**
     * getDir - Return the root directory of the currently active theme
     *
     * @access public
     * @return string Root directory of currently active theme
     */
    public function getDir(): string
    {

        return ROOT . '/theme/' . $this->getActive();
    }

    /**
     * getDir - Return the root directory of the default theme
     *
     * @access public
     * @return string Root directory of default theme
     */
    public function getDefaultDir(): string
    {

        return ROOT . '/theme/' . static::DEFAULT;
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
    public function getUrl(): string
    {

        return $this->config->appUrl . '/theme/' . $this->getActive();
    }

    /**
     * getDefaultUrl() - Return an URL pointing to the root directory of the default theme
     *
     * @access public
     * @return string Root URL default theme
     */
    public function getDefaultUrl(): string
    {

        return $this->config->appUrl . '/theme/' . static::DEFAULT;
    }

    /**
     * getStyleUrl - Return URL that allows loading the style file of the theme
     *
     * @access public
     * @return string|false URL to the css style file of the current theme or false, if it does not exist
     */
    public function getStyleUrl(): string|false
    {
        return $this->getAssetPath(static::DEFAULT_CSS, 'css');
    }

    /**
     * getCustomStyleUrl - Return URL that allows loading the customized part of the style file of the theme
     *
     * @access public
     * @return string|false URL to the customized part of the css style file of the current theme or false, if it does not exist
     */
    public function getCustomStyleUrl(): string|false
    {
        return $this->getAssetPath(static::CUSTOM_CSS, 'css');
    }

    /**
     * getJsUrl - Return URL that allows loading the JavaScript file of the theme
     *
     * @access public
     * @return string|false URL to the JavaScript file of the current theme or false, if it does not exist
     */
    public function getJsUrl(): string|false
    {
        return $this->getAssetPath(static::DEFAULT_JS, 'js');
    }

    /**
     * getCustomJsUrl - Return URL that allows loading the customized part of the JavaScript file of the theme
     *
     * @access public
     * @return string|false URL to the customized part of the JavaScript file of the current theme or false, if it does not exist
     */
    public function getCustomJsUrl(): string|false
    {
        return $this->getAssetPath(static::CUSTOM_JS, 'js');
    }

    /**
     * getAssetPath - Get localized name of theme
     *
     * @access private
     * @param string $fileName  Filename of asset without extension.
     * @param string $assetType Asset type either js or css.
     * @return string|boolean returns file path to asset. false if file does not exist
     */
    private function getAssetPath(string $fileName, string $assetType): string|bool
    {
        if ($fileName == '' || ($assetType != 'css' && $assetType != 'js')) {
            return false;
        }

        if (file_exists($this->getDir() . '/' . $assetType . '/' . $fileName . '.min.' . $assetType)) {
            return $this->getUrl() . '/' . $assetType . '/' . $fileName . '.min.' . $assetType . '?v=' . $this->settings->appVersion;
        }

        if (file_exists($this->getDir() . '/' . $assetType . '/' . $fileName . '.' . $assetType)) {
            return $this->getUrl() . '/' . $assetType . '/' . $fileName . '.' . $assetType . '?v=' . $this->settings->appVersion;
        }

        return false;
    }

    /**
     * getName - Get localized name of theme
     *
     * @access public
     * @return string Localized name of theme
     */
    public function getName(): string
    {
        // Make sure we get the current language
        $language = app()->make(Language::class);

        if (empty($this->iniData)) {
            try {
                $this->readIniData();
            } catch (Exception $e) {
                error_log($e);
                return $language->__("theme." . $this->getActive() . "name");
            }
        }

        if (isset($this->iniData['name'][$language->getCurrentLanguage()])) {
            return $this->iniData['name'][$language->getCurrentLanguage()];
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
    public function getVersion(): string
    {

        if (empty($this->iniData)) {
            try {
                $this->readIniData();
            } catch (Exception $e) {
                error_log($e);
                return '';
            }
        }

        if (isset($this->iniData['general']['version'])) {
            return $this->iniData['general']['version'];
        }

        return '';
    }

    /**
     * getLogoUrl - Get logo associated with the theme
     *
     * @access public
     * @return string|false Logo associated with the theme, false if logo cannot be found
     */
    public function getLogoUrl(): string|false
    {

        if (empty($this->iniData)) {
            try {
                $this->readIniData();
            } catch (Exception $e) {
                error_log($e);
                return false;
            }
        }

        if (isset($this->iniData['general']['logo'])) {
            return $this->iniData['general']['logo'];
        }

        return false;
    }

    /**
     * readIniData - Read theme.ini configuration data
     *
     * @access private
     * @throws Exception
     * @return void
     */
    private function readIniData(): void
    {
        if (!file_exists(ROOT . '/theme/' . $this->getActive() . '/' . static::DEFAULT_INI . '.ini')) {
            throw new Exception("Configuration file for theme " . $this->getActive() . " not found");
        }
        $this->iniData = parse_ini_file(
            ROOT . '/theme/' . $this->getActive() . '/' . static::DEFAULT_INI . '.ini',
            true,
            INI_SCANNER_TYPED
        );
        if ($this->iniData === false) {
            $this->iniData = [];
        }
    }
}
