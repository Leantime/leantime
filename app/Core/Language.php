<?php

namespace Leantime\Core;

use DateTime;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Date;
use Leantime\Domain\Reports\Repositories\Reports;
use Leantime\Domain\Setting\Repositories\Setting;
use Leantime\Core\Eventhelpers;
use Leantime\Core\ApiRequest;
use Leantime\Domain\Setting\Services\Setting as SettingService;

/**
 * Language class - Internationilsation with ini-Files
 *
 * @package    leantime
 * @subpackage core
 */
class Language
{
    use Eventhelpers;

    /**
     * @var string
     * @static
     * @final
     */
    private const DEFAULT_LANG_FOLDER = APP_ROOT . '/app/Language/';

    /**
     * @var string
     * @static
     * @final
     */
    private const CUSTOM_LANG_FOLDER = APP_ROOT . '/custom/Language/';

    /**
     * @var string
     * @static
     * @final
     */
    private string $language = 'en-US';

    /**
     * @var array
     * @static
     * @final
     */
    public array $ini_array;

    /**
     * @var array
     * @static
     * @final
     */
    public array $ini_array_fallback;

    /**
     * @var array
     * @static
     * @final
     */
    public mixed $langlist;

    /**
     * @var array|bool $alert - debug value. Will highlight untranslated text
     * @static
     * @final
     */
    private array|bool $alert = false;

    /**
     * @var environment $config
     */
    public Environment $config;

    /**
     * @var ApiRequest $apiRequest
     */
    public ApiRequest $apiRequest;


    /**
     * __construct - Check standard language otherwise get language from browser
     *
     * @param Environment $config
     * @param Setting     $settingsRepo
     * @param ApiRequest  $apiRequest
     * @throws BindingResolutionException
     */
    public function __construct(
        Environment $config,
        Setting $settingsRepo,
        ApiRequest $apiRequest,
    ) {

        $this->config = $config;
        $this->apiRequest = $apiRequest;

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
        } else {
            $language = $_SESSION["companysettings.language"];
        }
        $_SESSION["companysettings.language"] = $language;

        //Get user language
        if (!isset($_SESSION["userdata"]["id"])) {

            // This is a not a login session, we need to ensure the default language (or the user's browser)
            $language = $_COOKIE['language'] ?? $this->getBrowserLanguage();

        } else {
            // This is not a login session
            if (
                ! isset($_SESSION["usersettings.language"])
                || empty($_SESSION["usersettings.language"])
            ) {
                // User has a saved language
                $languageSettings = $settingsRepo->getSetting("usersettings." . $_SESSION["userdata"]["id"] . ".language");
                if ($languageSettings === false) {
                    $language = $_COOKIE['language'] ?? $this->getBrowserLanguage();
                } else {
                    $language = $languageSettings;
                }
            } else {
                $language = $_SESSION["usersettings.language"];
            }

            $_SESSION["usersettings.language"] = $language;
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
     * @return void
     * @throws Exception
     */
    public function setLanguage($lang): void
    {
        $this->language = $lang;

        $_SESSION['usersettings.language'] = $lang;
        if (isset($_SESSION["userdata"]["id"])) {
            $_SESSION["usersettings.language"] = $lang;
        }


        if (!isset($_COOKIE['language']) || $_COOKIE['language'] !== $lang) {
            setcookie('language', $lang, [
            'expires' => time() + 60 * 60 * 24 * 30,
                'path' => $this->config->appDir . '/',
                'samesite' => 'Strict',
            ]);
        }


        $_SESSION['usersettings.language'] = $lang;
        if (isset($_SESSION["userdata"]["id"])) {
            $_SESSION["usersettings.language"] = $lang;
        }


        if (!isset($_COOKIE['language']) || $_COOKIE['language'] !== $lang) {
            setcookie('language', $lang, [
            'expires' => time() + 60 * 60 * 24 * 30,
                'path' => $this->config->appDir . '/',
                'samesite' => 'Strict',
            ]);
        }

        $this->readIni();
    }

    /**
     * getLanguage - set the language (format: de-DE, languageCode-CountryCode)
     *
     * @access public
     * @return string
     */
    public function getCurrentLanguage(): string
    {
        return $this->language;
    }

    /**
     * isValidLanguage - check if language is valid
     *
     * @access public
     * @param  $langCode
     * @return bool
     */
    public function isValidLanguage($langCode): bool
    {
        return isset($this->langlist[$langCode]);
    }

    /**
     * readIni - read File and return values
     *
     * @access public
     * @return array
     * @throws Exception
     */
    public function readIni(): array
    {
        if (isset($_SESSION['cache.language_resources_' . $this->language]) && $this->config->debug == 0) {
            $this->ini_array = $_SESSION['cache.language_resources_' . $this->language] = self::dispatch_filter(
                'language_resources',
                $_SESSION['cache.language_resources_' . $this->language],
                [
                    'language' => $this->language,
                ]
            );
            return $this->ini_array;
        }

        // Default to english US
        if (!file_exists(static::DEFAULT_LANG_FOLDER . '/en-US.ini')) {
            throw new Exception("Cannot find default english language file en-US.ini");
        }

        $mainLanguageArray = parse_ini_file(static::DEFAULT_LANG_FOLDER . 'en-US.ini', false, INI_SCANNER_RAW);

        // Complement english with english customization
        $mainLanguageArray = $this->includeOverrides($mainLanguageArray, static::CUSTOM_LANG_FOLDER . 'en-US.ini');

        // Overwrite english language by non-english language
        $mainLanguageArray = $this->includeOverrides($mainLanguageArray, static::DEFAULT_LANG_FOLDER . $this->language . '.ini', true);

        // Overwrite with non-engish customizations
        $mainLanguageArray = $this->includeOverrides($mainLanguageArray, static::CUSTOM_LANG_FOLDER . $this->language . '.ini', true);

        $this->ini_array = $mainLanguageArray;

        $this->ini_array = self::dispatch_filter(
            'language_resources',
            $this->ini_array,
            [
                'language' => $this->language,
            ]
        );

        $_SESSION['cache.language_resources_' . $this->language] = $this->ini_array;

        return $this->ini_array;
    }

    /**
     * includeOverrides - include overrides from ini file
     *
     * @access public
     * @param array  $language
     * @param string $filepath
     * @param bool   $foreignLanguage
     * @return array
     * @throws Exception
     */
    protected function includeOverrides(array $language, string $filepath, bool $foreignLanguage = false): array
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
            $language[$languageKey] = $languageValue;
        }

        return $language;
    }

    /**
     * getLanguageList - gets the list of possible languages
     *
     * @access public
     * @return array|bool
     */
    public function getLanguageList(): bool|array
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
    public function getBrowserLanguage(): string
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
     * @param  bool   $convertValue If true then if a value has a conversion (i.e.: PHP -> JavaScript) then do it, otherwise return PHP value
     * @return string
     */
    public function __(string $index, bool $convertValue = false): string
    {
        if (isset($this->ini_array[$index]) === true) {
            $index = trim($index);

            // @TODO: move the date/time format logic into here and have Api/Controllers/I18n call this for each type?
            $dateTimeIniSettings = [
                'language.dateformat',
                'language.jsdateformat',
                'language.timeformat',
                'language.jstimeformat',
                'language.momentJSDate',
            ];

            $dateTimeFormat = $this->getCustomDateTimeFormat();

            if (in_array($index, $dateTimeIniSettings) && $convertValue) {
                $isMoment = stristr($index, 'momentjs') !== false;
                $isJs = stristr($index, '.js') !== false;
                $isDate = stristr($index, 'date') !== false;

                if ($isJs || $isMoment) {
                    return $this->convertDateFormatToJS($isDate ? $dateTimeFormat['date'] : $dateTimeFormat['time'], $isMoment);
                } else if ($isDate) {
                    return $this->convertDateFormatToJS($dateTimeFormat['date'], false);
                }
            } else if ($index === 'language.dateformat') {
                return $dateTimeFormat['date'];
            } else if ($index === 'language.timeformat') {
                return $dateTimeFormat['time'];
            }

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
     * @param string $date
     * @return string
     */
    public function getFormattedDateString(string|DateTime $date): string
    {
        if (is_null($date) === false && $date != "" && $date != "1969-12-31 00:00:00" && $date != "0000-00-00 00:00:00") {
            if ($this->apiRequest->isApiRequest()) {
                if (is_string($date)) {
                    $date = new DateTime($date);
                }

                return $date->format(DateTime::ATOM);
            }
            $formats = $this->getCustomDateTimeFormat();
            $dateFormat = $formats['date'];

            //If datetime object
            if ($date instanceof DateTime) {
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
     * @param string $date
     * @return string
     */
    public function getFormattedTimeString(?string $date): string
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
     * @param string $date
     * @return false|string
     */
    public function get24HourTimestring(?string $date): false|string
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
        }

        return false;
    }

    /**
     * getISODateString - returns an ISO date string (hours, minutes seconds zeroed out) based on language specific format
     *
     * @access public
     * @param string $date
     * @param string $timeOfDay b beginning, m mid day, e end of day
     * @return string|bool
     */
    public function getISODateString(?string $date, string $timeOfDay = "b"): bool|string
    {
        if (
            is_null($date) === false
            && $date != ""
            && $date != "1969-12-31 00:00:00"
            && $date != "0000-00-00 00:00:00"
        ) {
            $timestamp = date_create_from_format($this->__("language.dateformat"), $date, new \DateTimeZone($_SESSION['usersettings.timezone']));

            if (is_object($timestamp)) {

                switch ($timeOfDay) {
                    case "b":
                        $timestamp->setTime(0, 0, 0);
                        break;
                    case "m":
                        $timestamp->setTime(12, 0, 0);
                        break;
                    case "e":
                        $timestamp->setTime(23, 59, 59);
                        break;
                }

                return date("Y-m-d 00:00:00", $timestamp->getTimestamp());
            }
        }

        return false;
    }

    /**
     * getISODateString - returns an ISO date string (hours, minutes seconds zeroed out) based on language specific format
     *
     * @access public
     * @param string $date
     * @param $time
     * @return string|bool
     */
    public function getISODateTimeString(?string $date, $time): bool|string
    {
        if (is_null($date) === false && $date != "" && $date != "1969-12-31 00:00:00" && $date != "0000-00-00 00:00:00") {
            $timestamp = date_create_from_format($this->__("language.dateformat"), $date, new \DateTimeZone($_SESSION['usersettings.timezone']));

            //Time is coming in as 24hour format with :
            $timeparts = explode(":", $time);
            if (is_array($timeparts) && count($timeparts) >= 2) {
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
     * @param string $time
     * @return string|bool
     */
    public function getISOTimeString(?string $time): bool|string
    {
        if (is_null($time) === false && $time != "" && $time != "1969-12-31 00:00:00" && $time != "0000-00-00 00:00:00") {
            $timestamp = date_create_from_format($this->__("language.timeformat"), $time, new \DateTimeZone($_SESSION['usersettings.timezone']));

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
     * @param string $dateTime
     * @return string|bool
     */
    public function extractTime(?string $dateTime): bool|string
    {
        if (is_null($dateTime) === false && $dateTime != "" && $dateTime != "1969-12-31 00:00:00" && $dateTime != "0000-00-00 00:00:00") {
            $timestamp = date_create_from_format("Y-m-d H:i:00", $dateTime, new \DateTimeZone($_SESSION['usersettings.timezone']));

            if (is_object($timestamp)) {
                return date("H:i:00", $timestamp->getTimestamp());
            }
        }

        return false;
    }

    public function getCustomDateTimeFormat(string $defaultDateKey = 'dateformat', string $defaultTimeKey = 'timeformat'): array
    {

        if(isset($_SESSION['usersettings.language.dateTimeFormat'])
            && isset($_SESSION['userdata'])
            && $_SESSION['usersettings.language.dateTimeFormat']["date"] !== false
            && $_SESSION['usersettings.language.dateTimeFormat']["time"] !== false) {
            return $_SESSION['usersettings.language.dateTimeFormat'];
        }

        $settings = app()->make(SettingService::class);

        $results = ['date' => $this->ini_array['language.' . $defaultDateKey], 'time' => $this->ini_array['language.' . $defaultTimeKey]];

        $userId = isset($_SESSION['userdata']) && isset($_SESSION['userdata']['id']) ? $_SESSION['userdata']['id'] : 0;

        if ($userId) {
            $results['date'] = $settings->getSetting("usersettings." . $userId . ".date_format") !== false ? $settings->getSetting("usersettings." . $userId . ".date_format") : $results['date'];
            $results['time'] = $settings->getSetting("usersettings." . $userId . ".time_format") !== false ? $settings->getSetting("usersettings." . $userId . ".time_format") : $results['time'];
        }

        //Only cache when user is logged in.
        if(isset($_SESSION['userdata'])){
            $_SESSION['usersettings.language.dateTimeFormat'] = $results;
        }

        return $results;
    }

    /**
     * Converts php DateTime format to Javascript Moment format.
     * @link https://stackoverflow.com/a/55173613
     * @param string $phpFormat
     * @return string
     */
    public function convertDateFormatToJS(string $phpFormat, bool $toMoment = true): string
    {
        $momentReplacements = [
            'A' => 'A',      // for the sake of escaping below
            'a' => 'a',      // for the sake of escaping below
            'B' => '',       // Swatch internet time (.beats), no equivalent
            'c' => 'YYYY-MM-DD[T]HH:mm:ssZ', // ISO 8601
            'D' => 'ddd',
            'd' => 'DD',
            'e' => 'zz',     // deprecated since version 1.6.0 of moment.js
            'F' => 'MMMM',
            'G' => 'H',
            'g' => 'h',
            'H' => 'HH',
            'h' => 'hh',
            'I' => '',       // Daylight Saving Time? => moment().isDST();
            'i' => 'mm',
            'j' => 'D',
            'L' => '',       // Leap year? => moment().isLeapYear();
            'l' => 'dddd',
            'M' => 'MMM',
            'm' => 'MM',
            'N' => 'E',
            'n' => 'M',
            'O' => 'ZZ',
            'o' => 'YYYY',
            'P' => 'Z',
            'r' => 'ddd, DD MMM YYYY HH:mm:ss ZZ', // RFC 2822
            'S' => 'o',
            's' => 'ss',
            'T' => 'z',      // deprecated since version 1.6.0 of moment.js
            't' => '',       // days in the month => moment().daysInMonth();
            'U' => 'X',
            'u' => 'SSSSSS', // microseconds
            'v' => 'SSS',    // milliseconds (from PHP 7.0.0)
            'W' => 'W',      // for the sake of escaping below
            'w' => 'e',
            'Y' => 'YYYY',
            'y' => 'YY',
            'Z' => '',       // time zone offset in minutes => moment().zone();
            'z' => 'DDD',
        ];

        // @TODO: Additional format support (this covers the most popular)
        $jsReplacements = [
            'Y' => 'yy',
            'm' => 'mm',
            'd' => 'dd',
            'F' => 'MM',
            'l' => 'DD',
        ];

        return strtr($phpFormat, $toMoment ? $momentReplacements : $jsReplacements);
    }
}
