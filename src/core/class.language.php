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
        private $iniFolder = '/resources/language/';

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
         * @return
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

            }

        }

        /**
         * setLanguage - set the language (format: de-DE, languageCode-CountryCode)
         *
         * @access public
         * @param  $lang
         * @return void
         */
        public function setLanguage($lang)
        {

            $this->language = $lang;

        }

        /**
         * readIni - read File and return values
         *
         * @access public
         * @return array
         */
        public function readIni()
        {

            if(file_exists(''.$this->iniFolder.''.$this->language.'/'.$this->language.'.ini') === true) {

                $this->ini_array = parse_ini_file(''.$this->iniFolder.''.$this->language.'/'.$this->language.'.ini');

            }else{

                $this->ini_array[0] = 'File not found';

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

                return $this->ini_array[$index];

            } else {

                if($this->alert === true) {

                    return '<span style="color: red; font-weight:bold;">'.$index.'</span>';

                }else{

                    return $index;

                }
            }

        }

    }

}