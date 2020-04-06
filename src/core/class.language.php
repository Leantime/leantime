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

                if($config->language != '' && (!isset($_SESSION['language']) || $_SESSION['language'] == '')) {

                    $this->setLanguage($config->language);

                }elseif(isset($_SESSION['language']) === true && $_SESSION['language'] != '') {

                    $this->setLanguage($_SESSION['language']);

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
            if(file_exists(''.$this->iniFolder.'/'.$this->language.'.ini') === true) {

                $this->ini_array = parse_ini_file(''.$this->iniFolder.'/'.$this->language.'.ini', false, INI_SCANNER_RAW );

            }else{

                //Default to english US
                $this->ini_array = parse_ini_file(''.$this->iniFolder.'/en-US.ini', false, INI_SCANNER_RAW );

            }

            return $this->ini_array;

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

        public function getFormattedDateString($date)
        {

            if(is_null($date) === false && $date != "" && $date != "1969-12-31 00:00:00") {
                return date($this->__("language.dateformat"), strtotime($date));
            }

        }

    }

}