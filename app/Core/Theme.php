<?php

namespace Leantime\Core;

use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Str;
use Leantime\Core\Configuration\AppSettings;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Events\EventDispatcher;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Setting\Repositories\Setting;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

/**
 * theme - Engine for handling themes
 */
class Theme
{
    use DispatchesEvents;

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
     * @var Setting
     */
    private Setting $settingsRepo;

    /**
     * @var language
     */
    private Language $language;

    /**
     * @var language
     */
    private AppSettings $appSettings;

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
        Environment $config,
        Setting $settingsRepo,
        Language $language,
        AppSettings $appSettings,
    ) {
        $this->config = $config;
        $this->settingsRepo = $settingsRepo;
        $this->iniData = [];
        $this->language = $language;
        $this->appSettings = $appSettings;

    }

    /**
     * Retrieves the available color schemes.
     *
     * @return array The available color schemes.
     */
    public function getAvailableColorSchemes(): array
    {

        $this->readIniData();

        $parsedColorSchemes = $this->colorSchemes;
        $parsedColorSchemes["themeDefault"] = array(
            "name" => "label.themeDefault",
            "primaryColor" => $this->iniData["general"]["primaryColor"] ?? $this->colorSchemes["leantime2_0"]["primaryColor"],
            "secondaryColor" => $this->iniData["general"]["secondaryColor"] ?? $this->colorSchemes["leantime2_0"]["secondaryColor"],
        );

        $primaryColor = $this->settingsRepo->getSetting("companysettings.primarycolor") ?  $this->settingsRepo->getSetting("companysettings.primarycolor") : null;
        $secondaryColor = $this->settingsRepo->getSetting("companysettings.secondarycolor") ?  $this->settingsRepo->getSetting("companysettings.secondarycolor") : null;

        $parsedColorSchemes["companyColors"] = array(
            "name" => "label.companyColors",
            "primaryColor" => $primaryColor ?? $this->config->primarycolor ?? $parsedColorSchemes["themeDefault"]["primaryColor"],
            "secondaryColor" => $secondaryColor ?? $this->config->secondarycolor ?? $parsedColorSchemes["themeDefault"]["secondaryColor"],
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

        if (session()->exists("usersettings.theme") &&  Auth::isLoggedIn()) {
            return session("usersettings.theme");
        }

        // Return user specific theme, if active
        //This is an active logged in session.
        if (Auth::isLoggedIn()) {
            //User is logged in, we don't have a theme yet, check settings
            $theme = $this->settingsRepo->getSetting("usersettings." . session("userdata.id") . ".theme");
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
        if (session()->exists("usersettings.colorMode") &&  Auth::isLoggedIn()) {
            return session("usersettings.colorMode");
        }

        if (Auth::isLoggedIn()) {
            //User is logged in, we don't have a theme yet, check settings
            $colorMode = $this->settingsRepo->getSetting("usersettings." . session("userdata.id") . ".colorMode");
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
        session(["usersettings.colorMode" => 'light']);
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
        if (session()->exists("usersettings.colorScheme") && Auth::isLoggedIn()) {
            $this->setAccentColors(session("usersettings.colorScheme"));
            return session("usersettings.colorScheme");
        }

        if (Auth::isLoggedIn()) {
            //User is logged in, we don't have a theme yet, check settings

            $colorScheme = $this->settingsRepo->getSetting("usersettings." . session("userdata.id") . ".colorScheme");
            if ($colorScheme !== false) {
                $this->setColorScheme($colorScheme);
                return $colorScheme;
            }
        }

        if (isset($_COOKIE['colorScheme'])) {
            $this->setColorScheme($_COOKIE['colorScheme']);
            return $_COOKIE['colorScheme'];
        }

        if(!empty($this->config->primarycolor) && !empty($this->config->secondarycolor)) {
            //Return default
            $this->setColorScheme('companyColors');
            return 'companyColors';
        }else{
            //Return default
            $this->setColorScheme('themeDefault');
            return 'themeDefault';
        }

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
        if (session()->exists("usersettings.themeFont") && Auth::isLoggedIn()) {
            $this->setFont(session("usersettings.themeFont"));
            return session("usersettings.themeFont");
        }

        if (Auth::isLoggedIn()) {

            //User is logged in, we don't have a theme yet, check settings
            $themeFont = $this->settingsRepo->getSetting("usersettings." . session("userdata.id") . ".themeFont");
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
            $id = static::DEFAULT;
        }

        //not a valid theme. Use default
        if (!is_dir(ROOT . '/theme/' . $id) || !file_exists(ROOT . '/theme/' . $id . '/' . static::DEFAULT_INI . '.ini')) {
            $id = static::DEFAULT;
        }

        //Only set if user is logged in
        if (Auth::isLoggedIn()) {
            session(["usersettings.theme" => $id]);
        }

        EventDispatcher::add_filter_listener(
            'leantime.core.http.httpkernel.handle.beforeSendResponse',
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

        //Only store colors in session for logged in users
        if (Auth::isLoggedIn()) {
            session(["usersettings.colorMode" => $colorMode]);
        }

        EventDispatcher::add_filter_listener(
            'leantime.core.http.httpkernel.handle.beforeSendResponse',
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

        if (Auth::isLoggedIn()) {
            session(["usersettings.themeFont" => $font]);
        }

        EventDispatcher::add_filter_listener(
            'leantime.core.http.httpkernel.handle.beforeSendResponse',
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

        if (Auth::isLoggedIn()) {
            session(["usersettings.colorScheme" => $colorScheme]);
            $this->setAccentColors($colorScheme);
        }

        EventDispatcher::add_filter_listener(
            'leantime.core.http.httpkernel.handle.beforeSendResponse',
            fn ($response) => tap($response, fn (Response $response) => $response->headers->setCookie(
                Cookie::create('colorScheme')
                ->withValue($colorScheme)
                ->withExpires(time() + 60 * 60 * 24 * 30)
                ->withPath(Str::finish($this->config->appDir, '/'))
                ->withSameSite('Strict')
            ))
        );
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
            return $this->getUrl() . '/' . $assetType . '/' . $fileName . '.min.' . $assetType . '?v=' . $this->appSettings->appVersion;
        }

        if (file_exists($this->getDir() . '/' . $assetType . '/' . $fileName . '.' . $assetType)) {
            return $this->getUrl() . '/' . $assetType . '/' . $fileName . '.' . $assetType . '?v=' . $this->appSettings->appVersion;
        }

        return false;
    }

    /**
     * Retrieves the name of the theme.
     *
     * First, it checks if the INI data is empty. If it is, the method tries to read the INI data.
     * If an exception occurs during the reading process, it is logged in the error log and the method returns
     * the language translation of the active theme name using the "__" method of the $language object.
     *
     * If the INI data contains a 'name' key, it returns the corresponding value.
     *
     * If none of the above conditions are met, it returns the language translation of the active theme name
     * using the "__" method of the $language object.
     *
     * @return string The name of the theme.
     */
    public function getName(): string
    {

        if (empty($this->iniData)) {
            try {
                $this->readIniData();
            } catch (Exception $e) {
                report($e);
                return $this->language->__("theme." . $this->getActive() . "name");
            }
        }

        if (isset($this->iniData['name'])) {
            return $this->iniData['name'];
        }

        return $this->language->__("theme." . $this->getActive() . "name");
    }

    /**
     * Retrieves the version number from the initialization data or returns an empty string if not available.
     *
     * @return string The version number.
     */
    public function getVersion(): string
    {

        if (empty($this->iniData)) {
            try {
                $this->readIniData();
            } catch (Exception $e) {
                report($e);
                return '';
            }
        }

        if (isset($this->iniData['general']['version'])) {
            return $this->iniData['general']['version'];
        }

        return '';
    }

    /**
     * Retrieves the URL of the company logo from the user's settings or the default logo path.
     *
     * @return string|false The URL of the company logo, or false if the company doesn't have a logo.
     */
    public function getLogoUrl(): string|false
    {

        //Session Logo Path needs to be set here
        //Logo will be in there. Session will be renewed when new logo is updated or theme is changed

        $logoPath = false;
        if (session()->exists("companysettings.logoPath") === false || session("companysettings.logoPath") == '') {

            $logoPath = $this->settingsRepo->getSetting("companysettings.logoPath");

            if (
                $logoPath !== false &&
                (file_exists(ROOT . $logoPath) || str_starts_with($logoPath, "http"))
            ) {
                if (str_starts_with($logoPath, "http")) {
                    session(["companysettings.logoPath" => $logoPath]);
                } else {
                    session(["companysettings.logoPath" => BASE_URL . $logoPath]);
                }

                return session("companysettings.logoPath");
            }

            //If we can't find a logo in the db, the company doesn't have a logo. Stop trying
            session(["companysettings.logoPath" => false]);
        }

        return false;
    }

    /**
     * Sets the accent colors for the specified color scheme.
     *
     * @param string $colorScheme The name of the color scheme.
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
     * Sets the default theme colors in the user's settings.
     *
     * This method sets the primary and secondary colors of the theme to the default values by assigning false to the corresponding session variables.
     */
    public function setThemeDefaultColors()
    {
        //Using default css values
        session(["usersettings.colors.primaryColor" => false]);
        session(["usersettings.colors.secondaryColor" => false]);
    }

    /**
     * Sets the company colors in the user's settings.
     *
     * If the primary color setting is not already set in the user's settings,
     * it retrieves the primary color setting from the company settings. If the
     * primary color setting is not found in the company settings, it sets the
     * primary and secondary colors to the values specified in the application's
     * config file.
     *
     * If the secondary color setting is found in the company settings, it
     * sets the secondary color in the user's settings as well.
     *
     * @return void
     */
    public function setCompanyColors()
    {

        if (! session()->exists("usersettings.colors.primaryColor")) {

            $primaryColor = $this->settingsRepo->getSetting("companysettings.primarycolor");

            if ($primaryColor !== false) {
                session(["usersettings.colors.primaryColor" => $primaryColor]);
                session(["usersettings.colors.secondaryColor" => $primaryColor]);
            } else {
                session(["usersettings.colors.primaryColor" => $this->config->primaryColor]);
                session(["usersettings.colors.secondaryColor" => $this->config->secondaryColor]);
            }

            $secondaryColor = $this->settingsRepo->getSetting("companysettings.secondaryColor");
            if ($secondaryColor !== false) {
                session(["usersettings.colors.secondaryColor" => $secondaryColor]);
            }
        }
    }

    /**
     * Sets the primary and secondary colors for the user's color scheme.
     *
     * @param string $colorscheme The color scheme to set. Should be a valid key in the available color schemes array.
     * @return void
     */
    public function setSchemeColors($colorscheme)
    {

        $colorSchemes = $this->getAvailableColorSchemes();
        if (isset($colorSchemes[$colorscheme]["primaryColor"])) {
            $primary = $colorSchemes[$colorscheme]["primaryColor"];
            session(["usersettings.colors.primaryColor" => $primary]);
        }

        if (isset($colorSchemes[$colorscheme]["secondaryColor"])) {
            $secondary = $colorSchemes[$colorscheme]["secondaryColor"];
            session(["usersettings.colors.secondaryColor" => $secondary]);
        }

        return;
    }

    /**
     * Retrieves the primary color from the user's settings or the default color scheme.
     *
     * @return string The primary color.
     */
    public function getPrimaryColor()
    {

        if (
            session()->exists("usersettings.colors.primaryColor")
            && session("usersettings.colors.primaryColor") != ''
            &&  Auth::isLoggedIn()
        ) {
            return session("usersettings.colors.primaryColor");
        }

        $currentColorScheme = $this->getColorScheme();

        $colorSchemes = $this->getAvailableColorSchemes();

        if (Auth::isLoggedIn()) {
            session(["usersettings.colors.primaryColor" => $colorSchemes[$currentColorScheme]['primaryColor']]);
        }

        return $colorSchemes[$currentColorScheme]['primaryColor'];
    }

    /**
     * getSecondaryColor - Retrieves the secondary color for the current user's color scheme
     *
     * This method returns the secondary color based on the following conditions:
     * - If the secondary color is set in the user's session and is not empty, and the user is logged in,
     *   it will return the value stored in the session.
     * - If the user is logged in, it will set the secondary color from the available color schemes based on the current color scheme in use.
     * - If none of the above conditions are met, it will return the secondary color from the available color schemes based on the current color scheme in use.
     *
     * @access public
     * @return string The secondary color for the current user's color scheme
     */
    public function getSecondaryColor()
    {

        if (
            session()->exists("usersettings.colors.secondaryColor")
            && session("usersettings.colors.secondaryColor") != ''
            &&  Auth::isLoggedIn()
        ) {
            return session("usersettings.colors.secondaryColor");
        }

        $colorSchemes = $this->getAvailableColorSchemes();
        $currentColorScheme = $this->getColorScheme();

        if (Auth::isLoggedIn()) {
            session(["usersettings.colors.secondaryColor" => $colorSchemes[$currentColorScheme]['secondaryColor']]);
        }

        return $colorSchemes[$currentColorScheme]['secondaryColor'];
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
            report("Configuration file for theme " . $this->getActive() . " not found");
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

    public static function clearCache(): void
    {
        session()->forget("usersettings.colors.primaryColor");
        session()->forget("usersettings.colors.secondarycolor");
        session()->forget("usersettings.colorMode");
        session()->forget("usersettings.colorScheme");
        session()->forget("usersettings.themeFont");
        session()->forget("usersettings.theme");
    }
}
