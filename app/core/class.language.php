<?php

namespace leantime\core;

use Exception;
use leantime\domain\repositories\reports;
use leantime\domain\repositories\setting;
use leantime\core\eventhelpers;

/**
 * Language class - Internationilsation with ini-Files
 *
 * @package    leantime
 * @subpackage core
 */
class language
{
    use eventhelpers;

    /**
     * @var string
     * @static
     * @final
     */
    private const DEFAULT_LANG_FOLDER = APP_ROOT . '/app/language/';

    /**
     * @var string
     * @static
     * @final
     */
    private const CUSTOM_LANG_FOLDER = APP_ROOT . '/app/custom/language/';

    /**
     * @var string
     * @static
     * @final
     */
    private $language = 'en-US';

    /**
     * @var array
     * @static
     * @final
     */
    public $ini_array;

    /**
     * @var array
     * @static
     * @final
     */
    public $ini_array_fallback;

    /**
     * @var array
     * @static
     * @final
     */
    public $langlist;

    /**
     * @var array $alert - debug value. Will highlight untranslated text
     * @static
     * @final
     */
    private $alert = false;

    /**
     * @var environment $config
     */
    public environment $config;

    /**
     * @var theme $themeCore
     */
    public theme $themeCore;

    /**
     * @var string $theme
     */
    public string $theme;

    /**
     * __construct - Check standard language otherwise get language from browser
     *
     * @return self
     */
    public function __construct(
        environment $config,
        setting $settingsRepo,
    ) {

        $this->config = $config;
        $this->themeCore = app()->make(theme::class);
        $this->theme = $this->themeCore->getActive();

        //Get list of available languages
        if (isset($_SESSION['cache.langlist'])) {
            $this->langlist = $_SESSION['cache.langlist'];
        } else {
            if (file_exists(static::CUSTOM_LANG_FOLDER . '/languagelist.ini')) {
                $parsedLangList = parse_ini_file(static::CUSTOM_LANG_FOLDER . '/languagelist.ini', false, INI_SCANNER_RAW);
            } elseif (file_exists(static::DEFAULT_LANG_FOLDER . 'languagelist.ini')) {
                $parsedLangList = parse_ini_file(static::DEFAULT_LANG_FOLDER . '/languagelist.ini', false, INI_SCANNER_RAW);
            } else {
                throw new Exception("Language list missing");
            }

            $parsedLangList = self::dispatch_filter('languages', $parsedLangList);

            $this->langlist = $_SESSION['cache.langlist'] = $parsedLangList;
        }

        //Get company language
        if (!isset($_SESSION["companysettings.language"])) {
            $language = $settingsRepo->getSetting("companysettings.language");

            if ($language === false) {
                $language = $this->config->language;
            }

            $_SESSION["companysettings.language"] = $language;
        } else {
            $language = $_SESSION["companysettings.language"];

            $_SESSION["companysettings.language"] = $language;
        }

        //Get user language
        if (!isset($_SESSION["userdata"]["id"])) {
            // This is a login session, we need to ensure the default language (or the user's browser)
            if (isset($this->config->keepTheme) && $this->config->keepTheme) {
                $language = $_COOKIE['language'] ?? $this->getBrowserLanguage();
            }
        } else {
            // This is not a login session
            if (
                ! isset($_SESSION["usersettings." . $_SESSION["userdata"]["id"] . ".language"])
                || empty($_SESSION["usersettings." . $_SESSION["userdata"]["id"] . ".language"])
            ) {
                // User has a saved language
                $languageSettings = $settingsRepo->getSetting("usersettings." . $_SESSION["userdata"]["id"] . ".language");
                if ($languageSettings === false) {
                    if (isset($this->config->keepTheme) && $this->config->keepTheme) {
                        $language = $_COOKIE['language'] ?? $this->getBrowserLanguage();
                    }
                } else {
                    $language = $languageSettings;
                }
            } else {
                $language = $_SESSION["usersettings." . $_SESSION["userdata"]["id"] . ".language"];
            }

            $_SESSION["usersettings." . $_SESSION["userdata"]["id"] . ".language"] = $language;
        }
        $_SESSION['usersettings.language'] = $language;

        //Start checking if the user has a language set
        if ($this->isValidLanguage($language)) {
            $this->setLanguage($language);
        } elseif ($this->isValidLanguage($_SESSION['companysettings.language'])) {
            $this->setLanguage($_SESSION['companysettings.language']);
        } else {
            $this->setLanguage($this->config->language);
        }
    }

    /**
     * setLanguage - set the language (format: de-DE, languageCode-CountryCode)
     *
     * @access public
     * @param  $lang
     * @return array
     */
    public function setLanguage($lang)
    {
        $this->language = $lang;

        $_SESSION['usersettings.language'] = $lang;
        if (isset($_SESSION["userdata"]["id"])) {
            $_SESSION["usersettings." . $_SESSION["userdata"]["id"] . ".language"] = $lang;
        }

        if (isset($this->config->keepTheme) && $this->config->keepTheme) {
            if (!isset($_COOKIE['language']) || $_COOKIE['language'] !== $lang) {
                setcookie('language', $lang, [
                'expires' => time() + 60 * 60 * 24 * 30,
                    'path' => $this->config->appUrlRoot . '/',
                    'samesite' => 'Strict',
                ]);
            }
        }

        $_SESSION['usersettings.language'] = $lang;
        if (isset($_SESSION["userdata"]["id"])) {
            $_SESSION["usersettings." . $_SESSION["userdata"]["id"] . ".language"] = $lang;
        }

        if (isset($this->config->keepTheme) && $this->config->keepTheme) {
            if (!isset($_COOKIE['language']) || $_COOKIE['language'] !== $lang) {
                setcookie('language', $lang, [
                'expires' => time() + 60 * 60 * 24 * 30,
                    'path' => $this->config->appUrlRoot . '/',
                    'samesite' => 'Strict',
                ]);
            }
        }

        $this->readIni();
    }

    /**
     * getLanguage - set the language (format: de-DE, languageCode-CountryCode)
     *
     * @access public
     * @return array
     */
    public function getCurrentLanguage()
    {
        return $this->language;
    }

    /**
     * isValidLanguage - check if language is valid
     *
     * @access public
     * @param  $langCode
     * @return boolean
     */
    public function isValidLanguage($langCode)
    {
        return isset($this->langlist[$langCode]);
    }

    /**
     * readIni - read File and return values
     *
     * @access public
     * @return array
     */
    public function readIni()
    {
        if (isset($_SESSION['cache.language_resources_' . $this->language . '_' . $this->theme]) && $this->config->debug == 0) {
            $this->ini_array = $_SESSION['cache.language_resources_' . $this->language . '_' . $this->theme] = self::dispatch_filter(
                'language_resources',
                $_SESSION['cache.language_resources_' . $this->language . '_' . $this->theme],
                [
                    'language' => $this->language,
                    'theme' => $this->theme,
                ]
            );
            return $this->ini_array;
        }

        // Default to english US
        if (!file_exists(static::DEFAULT_LANG_FOLDER . '/en-US.ini')) {
            throw new Exception("Cannot find default english language file en-US.ini");
        }

        $mainLanguageArray = parse_ini_file(static::DEFAULT_LANG_FOLDER . 'en-US.ini', false, INI_SCANNER_RAW);

        // Overwrite with english from theme
        $mainLanguageArray = $this->includeOverrides($mainLanguageArray, $this->themeCore->getDir() . '/language/en-US.ini');

        // Complement english with english customization
        $mainLanguageArray = $this->includeOverrides($mainLanguageArray, static::CUSTOM_LANG_FOLDER . 'en-US.ini');

        // Overwrite english language by non-english language
        $mainLanguageArray = $this->includeOverrides($mainLanguageArray, static::DEFAULT_LANG_FOLDER . $this->language . '.ini', true);

        // Overwrite english by non-english from theme
        $mainLanguageArray = $this->includeOverrides($mainLanguageArray, $this->themeCore->getDir() . '/language/' . $this->language . '.ini', true);

        // Overwrite with non-engish customizations
        $mainLanguageArray = $this->includeOverrides($mainLanguageArray, static::CUSTOM_LANG_FOLDER . $this->language . '.ini', true);

        $this->ini_array = $mainLanguageArray;

        $this->ini_array = self::dispatch_filter(
            'language_resources',
            $this->ini_array,
            [
                'language' => $this->language,
                'theme' => $this->theme,
            ]
        );

        $_SESSION['cache.language_resources_' . $this->language . '_' . $this->theme] = $this->ini_array;

        return $this->ini_array;
    }

    /**
     * includeOverrides - include overrides from ini file
     *
     * @access public
     * @param  array  $language
     * @param  string $filepath
     * @param  bool   $foreignLanguage
     * @return array
     */
    protected function includeOverrides(array $language, string $filepath, bool $foreignLanguage = false)
    {
        if ($foreignLanguage && $this->language == 'en-US') {
            return $language;
        }

        if (! file_exists($filepath)) {
            return $language;
        }

        $ini_overrides = parse_ini_file($filepath, false, INI_SCANNER_RAW);

        if (! is_array($ini_overrides)) {
            throw new Exception("Could not parse ini file $filepath");
        }

        foreach ($ini_overrides as $languageKey => $languageValue) {
            $language[$languageKey] = $ini_overrides[$languageKey];
        }

        return $language;
    }

    /**
     * getLanguageList - gets the list of possible languages
     *
     * @access public
     * @return array|boolean
     */
    public function getLanguageList()
    {
        if (file_exists(static::CUSTOM_LANG_FOLDER . '/languagelist.ini')) {
            $this->langlist = parse_ini_file(static::CUSTOM_LANG_FOLDER . '/languagelist.ini', false, INI_SCANNER_RAW);
            return $this->langlist;
        }

        if (file_exists(static::DEFAULT_LANG_FOLDER . '/languagelist.ini')) {
            $this->langlist = parse_ini_file(static::DEFAULT_LANG_FOLDER . '/languagelist.ini', false, INI_SCANNER_RAW);
            return $this->langlist;
        }

        return false;
    }

    /**
     * getBrowserLanguage - gets the language that is setted in the browser
     *
     * @access public
     * @return string
     */
    public function getBrowserLanguage()
    {
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $language = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        }

        if (empty($language)) {
            return $this->language;
        }

        $langCode = explode("-", $language);

        if (isset($this->langlist[$langCode[0]]) === true) {
            return $langCode[0];
        }

        return $this->language;
    }

    /**
     * __ - returns a language specific string
     *
     * @access public
     * @param  string $index
     * @return string
     */
    public function __(string $index): string
    {
        if (isset($this->ini_array[$index]) === true) {
            $index = trim($index);
            return (string) $this->ini_array[$index];
        } else {
            if ($this->alert === true) {
                return '<span style="color: red; font-weight:bold;">' . $index . '</span>';
            } else {
                return $index;
            }
        }
    }

    /**
     * getFormattedDateString - returns a language specific formatted date string
     *
     * @access public
     * @param  string $date
     * @return string
     */
    public function getFormattedDateString($date): string
    {
        if (is_null($date) === false && $date != "" && $date != "1969-12-31 00:00:00" && $date != "0000-00-00 00:00:00") {
            //If datetime object
            if ($date instanceof \DateTime) {
                return $date->format($this->__("language.dateformat"));
            }

            //If length of string is 10 we only have a date(Y-m-d), otherwise it comes from the db with second strings.
            if (strlen($date) == 10) {
                $timestamp = date_create_from_format("!Y-m-d", $date);
            } else {
                $timestamp = date_create_from_format("!Y-m-d H:i:s", $date);
            }

            if (is_object($timestamp)) {
                return date($this->__("language.dateformat"), $timestamp->getTimestamp());
            }
        }

        return "";
    }

    /**
     * getFormattedTimeString - returns a language specific formatted time string
     *
     * @access public
     * @param  string $date
     * @return string
     */
    public function getFormattedTimeString($date)
    {
        if (
            is_null($date) === false
            && $date != ""
            && $date != "1969-12-31 00:00:00"
            && $date != "0000-00-00 00:00:00"
        ) {
            $timestamp = date_create_from_format("!Y-m-d H:i:s", $date);

            if (is_object($timestamp)) {
                return date($this->__("language.timeformat"), $timestamp->getTimestamp());
            }
        }

        return "";
    }

    /**
     * getFormattedDateTimeString - returns a language specific formatted date time string
     *
     * @access public
     * @param  string $date
     * @return string
     */
    public function get24HourTimestring($date)
    {
        if (
            is_null($date) === false
            && $date != ""
            && $date != "1969-12-31 00:00:00"
            && $date != "0000-00-00 00:00:00"
        ) {
            $timePart = explode(" ", $date);

            if (is_array($timePart) && count($timePart) == 2) {
                return $timePart[1];
            }

            return false;
        }
    }

    /**
     * getISODateString - returns an ISO date string (hours, minutes seconds zeroed out) based on language specific format
     *
     * @access public
     * @param  string $date
     * @return string|boolean
     */
    public function getISODateString($date)
    {
        if (
            is_null($date) === false
            && $date != ""
            && $date != "1969-12-31 00:00:00"
            && $date != "0000-00-00 00:00:00"
        ) {
            $timestamp = date_create_from_format($this->__("language.dateformat"), $date);

            if (is_object($timestamp)) {
                return date("Y-m-d 00:00:00", $timestamp->getTimestamp());
            }
        }

        return false;
    }

    /**
     * getISODateString - returns an ISO date string (hours, minutes seconds zeroed out) based on language specific format
     *
     * @access public
     * @param  string $date
     * @return string|boolean
     */
    public function getISODateTimeString($date, $time)
    {
        if (is_null($date) === false && $date != "" && $date != "1969-12-31 00:00:00" && $date != "0000-00-00 00:00:00") {
            $timestamp = date_create_from_format($this->__("language.dateformat"), $date);

            //Time is coming in as 24hour format with :
            $timeparts = explode(":", $time);
            if (is_array($timeparts) && count($timeparts) == 2) {
                $timestamp->setTime($timeparts[0], $timeparts[1]);
            }

            if (is_object($timestamp)) {
                return date("Y-m-d H:i:00", $timestamp->getTimestamp());
            }
        }

        return false;
    }

    /**
     * getISOTimeString - returns an ISO time string (hours, minutes seconds zeroed out) based on language specific format
     *
     * @access public
     * @param  string $time
     * @return string|boolean
     */
    public function getISOTimeString($time)
    {
        if (is_null($time) === false && $time != "" && $time != "1969-12-31 00:00:00" && $time != "0000-00-00 00:00:00") {
            $timestamp = date_create_from_format($this->__("language.timeformat"), $time);

            if (is_object($timestamp)) {
                return date("H:i:00", $timestamp->getTimestamp());
            }
        }

        return false;
    }

    /**
     * extractTime - returns an ISO time string (hours, minutes seconds zeroed out) based on language specific format
     *
     * @access public
     * @param  string $dateTime
     * @return string|boolean
     */
    public function extractTime($dateTime)
    {
        if (is_null($dateTime) === false && $dateTime != "" && $dateTime != "1969-12-31 00:00:00" && $dateTime != "0000-00-00 00:00:00") {
            $timestamp = date_create_from_format("Y-m-d H:i:00", $dateTime);

            if (is_object($timestamp)) {
                return date("H:i:00", $timestamp->getTimestamp());
            }
        }

        return false;
    }
}
