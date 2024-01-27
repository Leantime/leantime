<?php

namespace Leantime\Core;

use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Str;
use Leantime\Domain\Setting\Repositories\Setting;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

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
    public const DEFAULT_CSS = 'light';

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
     * Theme default logo
     *
     * @var string
     * @access public
     * @static
     * @final
     */
    public const DEFAULT_LOGO = '/dist/images/logo.svg';

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
    private Language $language;

    /**
     * @var array|false
     */
    private array|false $iniData;

    /**
     * possible color schemes.
     * @var array
     */
    public array $colorSchemes = [
            "themeDefault" => "themeDefault",
            "companyColors" => "companyColors",
            "leantime2_0" => array(
                "name" => "Leantime 2.0 Colors",
                "primaryColor" => "#1b75bb",
                "secondaryColor" => "#81B1A8",
            ),

        ];

    /**
     * possible font choices
     * @var array
     */
    public array $fonts = [
        "roboto" => "Roboto",
        "atkinson" => "Atkinson Hyperlegible",
        "shantell" => "Shantell Sans",
    ];
    /**
     * __construct - Constructor
     */
    public function __construct(
        environment $config,
        appSettings $settings,
        Language $language,
        array $iniData = []
    ) {
        $this->config = $config;
        $this->settings = $settings;
        $this->iniData = [];
        $this->language = $language;
    }

    public function getAvailableColorSchemes(): array
    {

        $this->readIniData();
        $parsedColorSchemes = $this->colorSchemes;
        $parsedColorSchemes["themeDefault"] = array(
            "name" => "label.themeDefault",
            "primaryColor" => $this->iniData["general"]["primaryColor"] ?? $this->colorSchemes["leantime2_0"]["primaryColor"],
            "secondaryColor" => $this->iniData["general"]["secondaryColor"] ?? $this->colorSchemes["leantime2_0"]["secondaryColor"],
        );

        $settingsRepo = app()->make(Setting::class);
        $primaryColor = $settingsRepo->getSetting("companysettings.primarycolor");
        $secondaryColor = $settingsRepo->getSetting("companysettings.secondarycolor");
        $parsedColorSchemes["companyColors"] = array(
            "name" => "label.companyColors",
            "primaryColor" => $primaryColor ?? $parsedColorSchemes["themeDefault"]["primaryColor"],
            "secondaryColor" => $secondaryColor ?? $parsedColorSchemes["themeDefault"]["secondaryColor"],
        );

        $colorschemes = self::dispatch_filter("colorschemes", $parsedColorSchemes);

        return $colorschemes;
    }

    public function getAvailableFonts()
    {
        return self::dispatch_filter("fonts", $this->fonts);
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

        if (isset($_SESSION['usersettings.theme'])) {
            return $_SESSION['usersettings.theme'];
        }

        // Return user specific theme, if active
        //This is an active logged in session.
        if (isset($_SESSION["userdata"]["id"])) {
            //User is logged in, we don't have a theme yet, check settings
            $settingsRepo = app()->make(Setting::class);
            $theme = $settingsRepo->getSetting("usersettings." . $_SESSION["userdata"]["id"] . ".theme");
            if ($theme !== false) {
                $this->setActive($theme);
                return $theme;
            }
        }


        //No generic theme set. Check if cookie is set
        if (isset($_COOKIE['theme'])) {
            $this->setActive($_COOKIE['theme']);
            return $_COOKIE['theme'];
        }

        //Return configured
        //Nothing set, get default theme from config
        if (isset($this->config->defaultTheme) && !empty($this->config->defaultTheme)) {
            $this->setActive($this->config->defaultTheme);
            return $this->config->defaultTheme;
        }

        //Return default
        return static::DEFAULT;
    }

    /**
     * getColorMode - Return active color mode
     *
     * @access public
     * @return string Active theme identifier
     */
    public function getColorMode()
    {

        //Return generic theme
        if (isset($_SESSION['usersettings.colorMode'])) {
            return $_SESSION['usersettings.colorMode'];
        }

        if (isset($_SESSION["userdata"]["id"])) {
            //User is logged in, we don't have a theme yet, check settings
            $settingsRepo = app()->make(Setting::class);
            $colorMode = $settingsRepo->getSetting("usersettings." . $_SESSION["userdata"]["id"] . ".colorMode");
            if ($colorMode !== false) {
                $this->setColorMode($colorMode);
                return $colorMode;
            }
        }

        //No generic theme set. Check if cookie is set
        if (isset($_COOKIE['colorMode'])) {
            $this->setColorMode($_COOKIE['colorMode']);
            return $_COOKIE['colorMode'];
        }

        //Return default
        $_SESSION['usersettings.colorMode'] = 'light';
        return 'light';
    }

    /**
     * getColorScheme - Return the active color scheme
     * Color schemes can be chosen by the user and can be themedefault, company colors or other predefined schemes
     * The colors that change are accent1 and accent2
     *
     * @access public
     * @return string Active theme identifier
     */
    public function getColorScheme()
    {

        //Return generic theme
        if (isset($_SESSION['usersettings.colorScheme'])) {
            $this->setAccentColors($_SESSION['usersettings.colorScheme']);
            return $_SESSION['usersettings.colorScheme'];
        }

        if (isset($_SESSION["userdata"]["id"])) {
            //User is logged in, we don't have a theme yet, check settings
            $settingsRepo = app()->make(Setting::class);
            $colorScheme = $settingsRepo->getSetting("usersettings." . $_SESSION["userdata"]["id"] . ".colorScheme");
            if ($colorScheme !== false) {
                $this->setColorScheme($colorScheme);
                return $colorScheme;
            }
        }

        if (isset($_COOKIE['colorScheme'])) {
            $this->setColorScheme($_COOKIE['colorScheme']);
            return $_COOKIE['colorScheme'];
        }

        //Return default
        $this->setColorScheme('themeDefault');
        return 'themeDefault';
    }

    /**
     * getFont - Return active font
     *
     * @access public
     * @return string Active theme identifier
     */
    public function getFont()
    {

        //Return generic theme
        if (isset($_SESSION['usersettings.themeFont'])) {
            $this->setFont($_SESSION['usersettings.themeFont']);
            return $_SESSION['usersettings.themeFont'];
        }

        if (isset($_SESSION["userdata"]["id"])) {
            //User is logged in, we don't have a theme yet, check settings
            $settingsRepo = app()->make(Setting::class);
            $themeFont = $settingsRepo->getSetting("usersettings." . $_SESSION["userdata"]["id"] . ".themeFont");
            if ($themeFont !== false) {
                $this->setFont($themeFont);
                return $themeFont;
            }
        }

        if (isset($_COOKIE['themeFont'])) {
            $this->setFont($_COOKIE['themeFont']);
            return $_COOKIE['themeFont'];
        }

        //Return default
        $this->setFont("roboto");
        return 'roboto';
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

        //not a valid theme. Use default
        if (!is_dir(ROOT . '/theme/' . $id) || !file_exists(ROOT . '/theme/' . $id . '/' . static::DEFAULT_INI . '.ini')) {
            $id = 'default';
        }

        $_SESSION['usersettings.theme'] = $id;

        Events::add_event_listener(
            'leantime.core.httpkernel.handle.beforeSendResponse',
            fn ($response) => tap($response, fn (Response $response) => $response->headers->setCookie(
                Cookie::create('theme')
                ->withValue($id)
                ->withExpires(time() + 60 * 60 * 24 * 30)
                ->withPath(Str::finish($this->config->appDir, '/'))
                ->withSameSite('Strict')
            ))
        );
    }

    /**
     * setColorModel - Set active theme
     *
     *
     * @access public
     * @param  string $colorMode color mode of theme (light, dark).
     * @return void
     */
    public function setColorMode(string $colorMode): void
    {
        if ($colorMode == '') {
            $colorMode = 'light';
        }

        $_SESSION['usersettings.colorMode'] = $colorMode;

        Events::add_filter_listener(
            'leantime.core.httpkernel.handle.beforeSendResponse',
            fn ($response) => tap($response, fn (Response $response) => $response->headers->setCookie(
                Cookie::create('colorMode')
                ->withValue($colorMode)
                ->withExpires(time() + 60 * 60 * 24 * 30)
                ->withPath(Str::finish($this->config->appDir, '/'))
                ->withSameSite('Strict')
            ))
        );
    }

    /**
     * setFont - Set active font
     *
     *
     * @access public
     * @param  string $font font name key (roboto, atkinson).
     * @return void
     */
    public function setFont(string $font): void
    {

        if ($font == '') {
            $font = 'roboto';
        }

        $_SESSION['usersettings.themeFont'] = $font;

        Events::add_event_listener(
            'leantime.core.httpkernel.handle.beforeSendResponse',
            fn ($response) => tap($response, fn (Response $response) => $response->headers->setCookie(
                Cookie::create('themeFont')
                ->withValue($font)
                ->withExpires(time() + 60 * 60 * 24 * 30)
                ->withPath(Str::finish($this->config->appDir, '/'))
                ->withSameSite('Strict')
            ))
        );
    }

    /**
     * setColorScheme - Set active theme
     *
     *
     * @access public
     * @param  string $colorScheme color scheme of theme (themeDefault, companyColors).
     * @return void
     */
    public function setColorScheme(string $colorScheme): void
    {

        if ($colorScheme == '') {
            $colorScheme = 'themeDefault';
        }

        $_SESSION['usersettings.colorScheme'] = $colorScheme;

        Events::add_filter_listener(
            'leantime.core.httpkernel.handle.beforeSendResponse',
            fn ($response) => tap($response, fn (Response $response) => $response->headers->setCookie(
                Cookie::create('colorScheme')
                ->withValue($colorScheme)
                ->withExpires(time() + 60 * 60 * 24 * 30)
                ->withPath(Str::finish($this->config->appDir, '/'))
                ->withSameSite('Strict')
            ))
        );

        $this->setAccentColors($colorScheme);
    }


    /**
     * getAll - Return an array of all themes
     *
     * @access public
     * @return array return an array of all themes
     * @throws BindingResolutionException
     */
    public function getAll(): array
    {
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

            //Ready theme ini
            $themeIni = ROOT
                . '/theme/'
                . $themeDir
                . '/theme.ini';

            if (file_exists($themeIni)) {
                $iniData = parse_ini_file(
                    $themeIni,
                    true,
                    INI_SCANNER_RAW
                );

                if (isset($iniData['general']['name']) && $iniData['general']['name'] !== null) {
                    $themes[$themeDir] = $iniData['general'];
                }
            }
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

        return ROOT . '/theme/' . static::DEFAULT;
    }

    /**
     * getStyleUrl - Return URL that allows loading the style file of the theme
     *
     * @access public
     * @return string|false URL to the css style file of the current theme or false, if it does not exist
     */
    public function getStyleUrl(): string|false
    {
        return $this->getAssetPath($this->getColorMode(), 'css');
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
     * @return string|bool returns file path to asset. false if file does not exist
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
     * @throws BindingResolutionException
     */
    public function getName(): string
    {

        if (empty($this->iniData)) {
            try {
                $this->readIniData();
            } catch (Exception $e) {
                error_log($e);
                return $this->language->__("theme." . $this->getActive() . "name");
            }
        }

        if (isset($this->iniData['name'])) {
            return $this->iniData['name'];
        }

        return $this->language->__("theme." . $this->getActive() . "name");
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

        //Session Logo Path needs to be set here
        //Logo will be in there. Session will be renewed when new logo is updated or theme is changed

        $logoPath = false;
        if (isset($_SESSION["companysettings.logoPath"]) === false || $_SESSION["companysettings.logoPath"] == '') {
            $settingsRepo = app()->make(Setting::class);
            $logoPath = $settingsRepo->getSetting("companysettings.logoPath");

            if (
                $logoPath !== false &&
                (file_exists(ROOT . $logoPath) || str_starts_with($logoPath, "http"))
            ) {
                if (str_starts_with($logoPath, "http")) {
                    $_SESSION["companysettings.logoPath"] = $logoPath;
                } else {
                    $_SESSION["companysettings.logoPath"] = BASE_URL . $logoPath;
                }

                return $_SESSION["companysettings.logoPath"];
            }

            //If we can't find a logo in the db, the company doesn't have a logo. Stop trying
            $_SESSION["companysettings.logoPath"] = false;
        }

        return false;
    }

    /**
     * @param string $colorScheme
     * @return void
     */
    public function setAccentColors(string $colorScheme)
    {

        if ($colorScheme == 'themeDefault') {
            $this->setThemeDefaultColors();
        } else if ($colorScheme == 'companyColors') {
            $this->setCompanyColors();
        } else {
            $this->setSchemeColors($colorScheme);
        }
    }


    /**
     * @return void
     */
    public function setThemeDefaultColors()
    {
        //Using default css values
        $_SESSION["usersettings.colorScheme.primaryColor"] = false;
        $_SESSION["usersettings.colorScheme.secondaryColor"] = false;
    }

    /**
     * @return void
     * @throws BindingResolutionException
     */
    public function setCompanyColors()
    {

        if (! isset($_SESSION["usersettings.colorScheme.primaryColor"])) {
            $settingsRepo = app()->make(Setting::class);
            $primaryColor = $settingsRepo->getSetting("companysettings.primarycolor");

            if ($primaryColor !== false) {
                $_SESSION["usersettings.colorScheme.primaryColor"] = $primaryColor;
                $_SESSION["usersettings.colorScheme.secondaryColor"] = $primaryColor;
            } else {
                $_SESSION["usersettings.colorScheme.primaryColor"] = $this->config->primaryColor;
                $_SESSION["usersettings.colorScheme.secondaryColor"] = $this->config->secondaryColor;
            }

            $secondaryColor = $settingsRepo->getSetting("companysettings.secondaryColor");
            if ($secondaryColor !== false) {
                $_SESSION["usersettings.colorScheme.secondaryColor"] = $secondaryColor;
            }
        }
    }

    /**
     * @param $colorscheme
     * @return void
     */
    public function setSchemeColors($colorscheme)
    {

        $colorSchemes = $this->getAvailableColorSchemes();
        if (isset($colorSchemes[$colorscheme]["primaryColor"])) {
            $primary = $colorSchemes[$colorscheme]["primaryColor"];
            $_SESSION["usersettings.colorScheme.primaryColor"] = $primary;
        }

        if (isset($colorSchemes[$colorscheme]["secondaryColor"])) {
            $secondary = $colorSchemes[$colorscheme]["secondaryColor"];
            $_SESSION["usersettings.colorScheme.secondaryColor"] = $secondary;
        }

        return;
    }

    public function getPrimaryColor() {

        if (isset($_SESSION["usersettings.colorScheme.primaryColor"])
            && $_SESSION["usersettings.colorScheme.primaryColor"] != '') {
            return $_SESSION["usersettings.colorScheme.primaryColor"];
        }

        $colorSchemes = $this->getAvailableColorSchemes();
        $_SESSION["usersettings.colorScheme.primaryColor"] = $colorSchemes['themeDefault']['primaryColor'];
        return $_SESSION["usersettings.colorScheme.primaryColor"];
    }

    public function getSecondaryColor() {

        if (isset($_SESSION["usersettings.colorScheme.secondaryColor"])
        && $_SESSION["usersettings.colorScheme.secondaryColor"] != '') {
            return $_SESSION["usersettings.colorScheme.secondaryColor"];
        }

        $colorSchemes = $this->getAvailableColorSchemes();
        $_SESSION["usersettings.colorScheme.secondaryColor"] = $colorSchemes['themeDefault']['secondaryColor'];

        return $_SESSION["usersettings.colorScheme.secondaryColor"];
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
        if (! file_exists(ROOT . '/theme/' . $this->getActive() . '/' . static::DEFAULT_INI . '.ini')) {
            error_log("Configuration file for theme " . $this->getActive() . " not found");
            $this->clearCache();
            $this->setActive("default");

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

    public function clearCache(): void
    {
        $this->iniData = [];

        unset($_SESSION["usersettings.colorScheme.primaryColor"]);
        unset($_SESSION["usersettings.colorScheme.secondarycolor"]);
        unset($_SESSION["usersettings.colorMode"]);
        unset($_SESSION["usersettings.colorScheme"]);
        unset($_SESSION["usersettings.themeFont"]);
        unset($_SESSION["usersettings.theme"]);
    }
}
