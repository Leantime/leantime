<?php

/**
 * Language class - Internationilsation with ini-Files
 *
 */

namespace leantime\core {

    use Exception;
    use leantime\domain\repositories\reports;
    use leantime\domain\repositories\setting;
    use leantime\base\eventhelpers;

    class language
    {

        use eventhelpers;

        /**
         * @access private
         * @var    string
         */
        private const DEFAULT_LANG_FOLDER = '../src/language/';
        private const CUSTOM_LANG_FOLDER = '../custom/language/';

        /**
         * @access private
         * @var    string default de-DE
         */
        private $language = 'en-US';

        /**
         * @access public
         * @var    array ini-values
         */
        public $ini_array;

        /**
         * @access public
         * @var    array ini-values
         */
        public $ini_array_fallback;

        /**
         * @access public
         * @var    array ini-values
         */
        public $langlist;

        /**
         * debug value. Will highlight untranslated text
         * @access public
         * @var    array ini-values
         */
        private $alert = false;


        /**
         * __construct - Check standard language otherwise get language from browser
         *
         * @return array
         */
        public function __construct()
        {

            $this->config = new config();
            $settingsRepo = new setting();
            $this->themeCore = new theme();
            $this->theme = $this->themeCore->getActive();

            //Get list of available languages
            if (isset($_SESSION['cache.langlist'])){

                $this->langlist = $_SESSION['cache.langlist'];

            } else {

                if (file_exists(static::CUSTOM_LANG_FOLDER.'/languagelist.ini')) {

                    $parsedLangList = parse_ini_file(static::CUSTOM_LANG_FOLDER.'/languagelist.ini');

                }elseif (file_exists(static::DEFAULT_LANG_FOLDER.'languagelist.ini')) {

                    $parsedLangList = parse_ini_file(static::DEFAULT_LANG_FOLDER.'/languagelist.ini');

                }else{

                    throw new Exception("Language list missing");

                }

                $parsedLangList = self::dispatch_filter('languages', $parsedLangList);

                $this->langlist = $_SESSION['cache.langlist'] = $parsedLangList;

            }

            //Get company language
            if(!isset($_SESSION["companysettings.language"])) {

                $language = $settingsRepo->getSetting("companysettings.language");

                if ($language === false) {

                    $language = $this->config->language;
                }

                $_SESSION["companysettings.language"] = $language;

            }else{

                $language = $_SESSION["companysettings.language"];

                $_SESSION["companysettings.language"] = $language;

            }

            //Get user language
            if(!isset($_SESSION["userdata"]["id"])) {

                // This is a login session, we need to ensure the default language (or the user's browser)
                if(isset($this->config->keepTheme) && $this->config->keepTheme) {

                    $language = $_COOKIE['language'] ?? $this->getBrowserLanguage();

                }

            }else{

                // This is not a login session
                if(!isset($_SESSION["usersettings.".$_SESSION["userdata"]["id"].".language"]) ||
                   empty($_SESSION["usersettings.".$_SESSION["userdata"]["id"].".language"])) {

                    // User has a saved language
                    $settingsRepo = new \leantime\domain\repositories\setting();
                    $languageSettings = $settingsRepo->getSetting("usersettings.".$_SESSION["userdata"]["id"].".language");
                    if($languageSettings === false) {

                        if(isset($this->config->keepTheme) && $this->config->keepTheme) {

                            $language = $_COOKIE['language'] ?? $this->getBrowserLanguage();

                        }

                    }else{

                        $language = $languageSettings;

                    }

                }else{

                    $language = $_SESSION["usersettings.".$_SESSION["userdata"]["id"].".language"];

                }

                $_SESSION["usersettings.".$_SESSION["userdata"]["id"].".language"] = $language;
            }
            $_SESSION['usersettings.language'] = $language;

            //Start checking if the user has a language set
            if($this->isValidLanguage($language)) {

                $this->setLanguage($language);

            }elseif($this->isValidLanguage($_SESSION['companysettings.language'])){

                $this->setLanguage($_SESSION['companysettings.language']);

            }else{
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
            if(isset($_SESSION["userdata"]["id"])) {

                $_SESSION["usersettings.".$_SESSION["userdata"]["id"].".language"] = $lang;
            }

            if(isset($this->config->keepTheme) && $this->config->keepTheme) {

                if(!isset($_COOKIE['language']) || $_COOKIE['language'] !== $lang) {

                    setcookie('language', $lang, [ 'expires' => time() + 60 * 60 * 24 * 30,
                                                   'path' => $this->config->appUrlRoot.'/',
                                                   'samesite' => 'Strict' ]);

                }

            }

            $_SESSION['usersettings.language'] = $lang;
            if(isset($_SESSION["userdata"]["id"])) {

                $_SESSION["usersettings.".$_SESSION["userdata"]["id"].".language"] = $lang;
            }

            if(isset($this->config->keepTheme) && $this->config->keepTheme) {

                if(!isset($_COOKIE['language']) || $_COOKIE['language'] !== $lang) {

                    setcookie('language', $lang, [ 'expires' => time() + 60 * 60 * 24 * 30,
                                                   'path' => $this->config->appUrlRoot.'/',
                                                   'samesite' => 'Strict' ]);

                }

            }

            $this->readIni();

        }

        /**
         * getLanguage - set the language (format: de-DE, languageCode-CountryCode)
         *
         * @access public
         * @param  $lang
         * @return array
         */
        public function getCurrentLanguage()
        {

            return $this->language;

        }

        public function isValidLanguage($langCode){

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

            if(isset($_SESSION['cache.language_resources_'.$this->language.'_'.$this->theme]) && $this->config->debug == 0) {
                $this->ini_array = $_SESSION['cache.language_resources_'.$this->language.'_'.$this->theme];
                return $this->ini_array;
            }

            // Default to english US
            if (!file_exists(ROOT.'/'.static::DEFAULT_LANG_FOLDER.'/en-US.ini')) {

                throw new Exception("Cannot find default english language file en-US.ini");

            }
            $mainLanguageArray = parse_ini_file(ROOT.'/'.static::DEFAULT_LANG_FOLDER.'en-US.ini', false, INI_SCANNER_RAW);

            // Overwrite with english from theme
            if (file_exists($this->themeCore->getDir().'/language/en-US.ini')) {

                $ini_overrides = parse_ini_file($this->themeCore->getDir().'/language/en-US.ini', false, INI_SCANNER_RAW);
                if (is_array($ini_overrides)) {
                    foreach ($ini_overrides as $languageKey => $languageValue) {
                        $mainLanguageArray[$languageKey] = $ini_overrides[$languageKey];
                    }
                }

            }

			// Complement english with english customization
            if (file_exists(ROOT.'/'.static::CUSTOM_LANG_FOLDER.'en-US.ini')) {

                $ini_overrides = parse_ini_file(ROOT.'/'.static::CUSTOM_LANG_FOLDER.'en-US.ini', false, INI_SCANNER_RAW);
                if (is_array($ini_overrides)) {
                    foreach ($ini_overrides as $languageKey => $languageValue) {
                        $mainLanguageArray[$languageKey] = $ini_overrides[$languageKey];
                    }
                }

            }

			// Overwrite english language by non-english language
            if (file_exists(ROOT.'/'.static::DEFAULT_LANG_FOLDER.$this->language.'.ini') && $this->language !== 'en-US') {

                $ini_overrides = parse_ini_file(ROOT.'/'.static::DEFAULT_LANG_FOLDER.$this->language.'.ini', false, INI_SCANNER_RAW);
                if (is_array($ini_overrides)) {
                    foreach ($ini_overrides as $languageKey => $languageValue) {
                        $mainLanguageArray[$languageKey] = $ini_overrides[$languageKey];
                    }
                }

            }

            // Overwrite english by non-english from theme
            if (file_exists($this->themeCore->getDir().'/language/'.$this->language.'.ini') && $this->language !== 'en-US') {

                $ini_overrides = parse_ini_file($this->themeCore->getDir().'/language/'.$this->language.'.ini', false, INI_SCANNER_RAW);
                if (is_array($ini_overrides)) {
                    foreach ($ini_overrides as $languageKey => $languageValue) {
                        $mainLanguageArray[$languageKey] = $ini_overrides[$languageKey];
                    }
                }

            }

			// Overwrite with non-engish customizations
            if (file_exists(ROOT.'/'.static::CUSTOM_LANG_FOLDER.$this->language.'.ini') && $this->language !== 'en-US') {

                $ini_overrides = parse_ini_file(ROOT.'/'.static::CUSTOM_LANG_FOLDER.$this->language.'.ini', false, INI_SCANNER_RAW);
                if (is_array($ini_overrides)) {
                    foreach ($ini_overrides as $languageKey => $languageValue) {
                        $mainLanguageArray[$languageKey] = $ini_overrides[$languageKey];
                    }
                }

            }

            // Overwrite english by non-english from theme
            if (file_exists($this->themeCore->getDir().'/language/'.$this->language.'.ini') && $this->language !== 'en-US') {

                $ini_overrides = parse_ini_file($this->themeCore->getDir().'/language/'.$this->language.'.ini', false, INI_SCANNER_RAW);
                if (is_array($ini_overrides)) {
                    foreach ($ini_overrides as $languageKey => $languageValue) {
                        $mainLanguageArray[$languageKey] = $ini_overrides[$languageKey];
                    }
                }

            }

			// Overwrite with non-engish customizations
            if (file_exists(ROOT.'/'.static::CUSTOM_LANG_FOLDER.$this->language.'.ini') && $this->language !== 'en-US') {

                $ini_overrides = parse_ini_file(ROOT.'/'.static::CUSTOM_LANG_FOLDER.$this->language.'.ini', false, INI_SCANNER_RAW);
                if (is_array($ini_overrides)) {
                    foreach ($ini_overrides as $languageKey => $languageValue) {
                        $mainLanguageArray[$languageKey] = $ini_overrides[$languageKey];
                    }
                }

            }

            $this->ini_array = $mainLanguageArray;
            $_SESSION['cache.language_resources_'.$this->language.'_'.$this->theme] = $this->ini_array;

            return $this->ini_array;

        }

        /**
         * getLanguageList - gets the list of possible languages
         *
         * @access public
         * @return array|bool
         */
        public function getLanguageList()
        {

            if (file_exists(static::CUSTOM_LANG_FOLDER.'/languagelist.ini')) {

                $this->langlist = parse_ini_file(static::CUSTOM_LANG_FOLDER.'/languagelist.ini');
                return $this->langlist;

            }

            if (file_exists(static::DEFAULT_LANG_FOLDER.'/languagelist.ini')) {

                $this->langlist = parse_ini_file(static::DEFAULT_LANG_FOLDER.'/languagelist.ini');
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

            $language = $_SERVER['HTTP_ACCEPT_LANGUAGE'];

            if (empty($language)) {

                return $this->language;

            }

            $langCode = explode("-", $language);

            if (isset($this->langlist[$langCode[0]]) === true) {

                return $langCode[0];

            }

            return $this->language;

        }


        public function __(string $index) :string
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
         * @param $date string
         * @return string
         */
        public function getFormattedDateString($date) :string
        {
            if (is_null($date) === false && $date != "" && $date != "1969-12-31 00:00:00" && $date != "0000-00-00 00:00:00") {

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
         * @param $date string
         * @return string
         */
        public function getFormattedTimeString($date)
        {
            if (is_null($date) === false && $date != "" && $date != "1969-12-31 00:00:00" && $date != "0000-00-00 00:00:00") {

                $timestamp = date_create_from_format("!Y-m-d H:i:s", $date);

                if (is_object($timestamp)) {
                    return date($this->__("language.timeformat"), $timestamp->getTimestamp());
                }

            }

            return "";

        }

        /**
         * getISODateString - returns an ISO date string (hours, minutes seconds zeroed out) based on language specific format
         *
         * @access public
         * @param $date string
         * @return string|bool
         */
        public function getISODateString($date)
        {
            if (is_null($date) === false && $date != "" && $date != "1969-12-31 00:00:00" && $date != "0000-00-00 00:00:00") {

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
         * @param $date string
         * @return string|bool
         */
        public function getISODateTimeString($date)
        {
            if (is_null($date) === false && $date != "" && $date != "1969-12-31 00:00:00" && $date != "0000-00-00 00:00:00") {

                $timestamp = date_create_from_format($this->__("language.dateformat") . " " . $this->__("language.timeformat"), $date);

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
         * @param $time string
         * @return string|bool
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

        public function extractTime($dateTime) {

            if (is_null($dateTime) === false && $dateTime != "" && $dateTime != "1969-12-31 00:00:00" && $dateTime != "0000-00-00 00:00:00") {

                $timestamp = date_create_from_format("Y-m-d H:i:00", $dateTime);

                if (is_object($timestamp)) {
                    return date("H:i:00", $timestamp->getTimestamp());
                }

            }

            return false;
        }

    }

}
