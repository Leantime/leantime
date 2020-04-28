<?php

/**
 * Language class - Internationilsation with ini-Files
 *
 */

namespace leantime\core {

    class language
    {

        /**
         * @access private
         * @var    string
         */
        private $iniFolder = '../resources/language/';

        /**
         * @access private
         * @var    string default de-DE
         */
        private $language = 'de';

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
        function __construct()
        {

            $config = new config();

            if(file_exists(''.$this->iniFolder.'languagelist.ini') === true) {

                $this->langlist = parse_ini_file(''.$this->iniFolder.'languagelist.ini');

                if($config->language != '' && (!isset($_SESSION['companysettings.language']) || $_SESSION['companysettings.language'] == '')) {

                    $this->setLanguage($config->language);

                }elseif(isset($_SESSION['companysettings.language']) === true && $_SESSION['companysettings.language'] != '') {

                    $this->setLanguage($_SESSION['companysettings.language']);

                }else{

                    $browserLang = $this->getBrowserLanguage();
                    $this->setLanguage($browserLang);

                }

            }else{

                throw new \Exception("Language list missing");
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

            $this->readIni();

        }

        /**
         * readIni - read File and return values
         *
         * @access public
         * @return array
         */
        public function readIni()
        {

            //Todo: Add cache

            //Default to english US
            $mainLanguageArray = parse_ini_file(''.$this->iniFolder.'/en-US.ini', false, INI_SCANNER_RAW );

            if(file_exists(''.$this->iniFolder.'/'.$this->language.'.ini') === true) {

                $ini_overrides = parse_ini_file(''.$this->iniFolder.'/'.$this->language.'.ini', false, INI_SCANNER_RAW );

                if(is_array($ini_overrides) == true) {

                    foreach ($mainLanguageArray as $languageKey => $languageValue) {

                        if (array_key_exists($languageKey, $ini_overrides)) {
                            $mainLanguageArray[$languageKey] = $ini_overrides[$languageKey];
                        }

                    }
                }
            }

            $this->ini_array = $mainLanguageArray;
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

            if(file_exists(''.$this->iniFolder.'languagelist.ini') === true) {

                $this->langlist = parse_ini_file('' . $this->iniFolder . 'languagelist.ini');
                return $this->langlist;

            }else{

                return false;

            }

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

            if(isset($this->langlist[$langCode[0]]) === true) {

                return $langCode[0];

            }

        }


        public function __($index)
        {

            if (isset($this->ini_array[$index]) === true) {

                $index = trim($index);

                return $this->ini_array[$index];

            } else {

                if($this->alert === true) {

                    return '<span style="color: red; font-weight:bold;">'.$index.'</span>';

                }else{

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
        public function getFormattedDateString($date)
        {
            if(is_null($date) === false && $date != "" && $date != "1969-12-31 00:00:00" && $date != "0000-00-00 00:00:00") {

                //If length of string is 10 we only have a date(Y-m-d), otherwise it comes from the db with second strings.
                if(strlen($date) == 10){
                    $timestamp = date_create_from_format("!Y-m-d", $date);
                }else {
                    $timestamp = date_create_from_format("!Y-m-d H:i:s", $date);
                }

                if(is_object($timestamp)) {
                    return date($this->__("language.dateformat"), $timestamp->getTimestamp());
                }

            }

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
            if(is_null($date) === false && $date != "" && $date != "1969-12-31 00:00:00" && $date != "0000-00-00 00:00:00") {

                $timestamp = date_create_from_format("!Y-m-d H:i:s", $date);

                if(is_object($timestamp)) {
                    return date($this->__("language.timeformat"), $timestamp->getTimestamp());
                }

            }

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
            if(is_null($date) === false && $date != "" && $date != "1969-12-31 00:00:00" && $date != "0000-00-00 00:00:00") {

                $timestamp = date_create_from_format($this->__("language.dateformat"), $date);

                if(is_object($timestamp)) {
                    return date("Y-m-d 00:00:00", $timestamp->getTimestamp());
                }

            }

            return false;

        }

    }

}