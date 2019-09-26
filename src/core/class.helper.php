<?php
namespace leantime\core {

    /**
     * Helper class - helping functions
     *
     * @author  Marcel Folaron <marcel.folaron@gmail.com>
     * @version 1.0
     * @license GNU/GPL, see license.txt
     */

    class helper
    {

        /**
         *
         * @return
         */
        public function __construct()
        {

        }

        /**
         * decimalToCurrencyFormat
         *
         * @access public
         * @param  $decimal

         * @return $myCurrencyAmount string
         */

        public function decimalToCurrencyFormat($amount)
        {


            $amount = trim($amount);    // ggf. Leerzeichen entfernen
            $amount = round($amount, 2);
            if(preg_match('/^(\-)?(\d+)(?:\.(\d{0,2})?)?$/', $amount, $x) ) {
                // Ganzzahl oder Dezimalwert mit bis zu 2 Nachkommastellen
                $vz = $x[1];
                $vk = $x[2];
                $nk = isset($x[3]) ? $x[3] : '0';
            }
            else {
                // Kein g�ltiger Eingabewert
                return '0,00';
            }

            $myCurrencyAmount = "$vz$vk.$nk";
            return number_format($myCurrencyAmount, 2, ',', '.');

            //return $amount;
        }


        /**
        Die Funktion currencyFormatToDecimal soll den eingegeben Betrag in decimal umwandeln

        den Betrag 1 in 1,00 umwandeln
        den Betrag 1.5 und 1,50 umwandeln
        den Betrag 1.0 in 1,00 umwandeln
        den Betrag 1000 in 1.000,00 um wandeln usw.
         **/


        /**
         * date2timestamp -  takes date(M/D/Y) and returns date(Y-M-D)
         *
         * @param  $date
         * @return string
         **/

        public function date2timestamp($date, $time = false)
        {

            if($date == ""){
                return "";
            }

            $dateArr = explode('/', $date);
            $return = date('Y-m-d', strtotime($date));

            if ($time!=false) {
                $return = date('Y-m-d H:i:s', strtotime($date.' '.$time));
            }

            return $return;
        }


        /**
         * timestamp2date - transforms a datetimestring to a readable format and back
         *
         * @access public
         * @param  $timestamp
         * @param  $mode
         * @return string
         */
        public function timestamp2date($timestamp, $mode)
        {

            // mode = 1: time (18:20)
            // mode = 2: date (02.06.2005)
            // mode = 3: everything (18:20 2.6.05)
            // mode = 4: back to datetime-string (2005-06-02 hh:mm:ss) aus dd.mm.yyyy und momentane uhrzeit
            // mode = 5: Gives Seconds from Timestampt
            // string positioning
            // 0 1 2 3 4 5 6 7 8 9 10 11 12 13 14 15 16 17
            // 0 0 0 0 - 0 0 - 0 0 0  0  :  0  0  :  0  0
            // m m / d d / y y y y
            // hh : mm
            // d d . m m . y y y y
            // hh : mm dd . mm . yyyy

            if($timestamp == '') {

                return '';
            }elseif($timestamp == '1969-12-31 00:00:00') {

                return '';

            }elseif($timestamp == '0000-00-00 00:00:00') {

                return '';

            }else{

                $config = new config();

                $lang = $config->language;

                if($lang == 'de') {
                    if ($mode == 1) {

                        return( substr($timestamp,  11,  2)  .  ':' .  substr($timestamp,  14,  2) );

                    }

                    if ($mode == 2) {

                        return( substr($timestamp,  8,  2)  .  '.' .  substr($timestamp,  5,  2) . '.' . substr($timestamp,  0, 4) );

                    }

                    if ($mode == 3) {

                        return( substr($timestamp,  11,  2)  .  ':'
                            .  substr($timestamp,  14,  2) . '  '
                            .  substr($timestamp,  8,  2)  .  '.'
                            .  substr($timestamp,  5,  2) . '.'
                            . substr($timestamp,  2, 2) );

                    }

                    if ($mode == 4) {

                        return( substr($timestamp,  6,  4)  .  '-'
                            .  substr($timestamp,  3,  2) . '-'
                            .  substr($timestamp,  0, 2) . ' 00:00:00');

                    }


                    if($mode == 5) {

                        return mktime(0, 0, 0, substr($timestamp,  5,  2), substr($timestamp,  8,  2), substr($timestamp,  0, 4));

                    }

                }elseif($lang == 'en') {

                    if ($mode == 1) {

                        return( substr($timestamp,  11,  2)  .  ':' .  substr($timestamp,  14,  2) );

                    }

                    if ($mode == 2) {

                        return( substr($timestamp,  5,  2)   .  '/' . substr($timestamp,  8,  2) . '/' . substr($timestamp,  0, 4) );

                    }

                    if ($mode == 3) {

                        return( substr($timestamp,  11,  2)  .  ':'
                            .  substr($timestamp,  14,  2) . '  '
                            .  substr($timestamp,  5,  2)  .  '/'
                            .  substr($timestamp,  8,  2) . '/'
                            . substr($timestamp,  2, 2) );

                    }

                    if ($mode == 4) {

                        return( substr($timestamp,  6,  4)  .  '-'
                            . substr($timestamp,  0, 2)  . '-'
                            .  substr($timestamp,  3,  2) . ' 00:00:00');

                    }


                    if($mode == 5) {

                        return mktime(0, 0, 0, substr($timestamp,  5,  2), substr($timestamp,  8,  2), substr($timestamp,  0, 4));

                    }

                }



            }

        }

        /**
         * getMultipleValues - transforms array in list (used in the context of multiple select listboxes)
         *
         * @access public
         * @param  $arr
         * @return comma separated list(string)
         */

        function getMultipleValues( $arr)
        {
            $myLIST="";
            if(is_array($arr) === true) {
                $sizearr = sizeof($arr);

                for($i=0; $i<$sizearr; $i++){

                    $myLIST=$myLIST.$arr[$i];

                    if ($i>=0) {
                        if ($i<$sizearr-1 ) {
                            $myLIST=$myLIST.",";
                        }
                    }
                }
            }

            return $myLIST;
        }

        /**
         * checks whether a checkbox or radio button was clicked and gives the value back otherwise 0
         *
         * @access public
         * @param  $arr
         * @return comma separated list(string)
         */
        function checkRadioPost($postIndex)
        {

            if(isset($_POST[$postIndex]) === true) {
                return $_POST[$postIndex];
            }else{
                return 0;
            }

        }

        /**
         * validateDatum - validate whether  datum is in format string.string.string
         *
         * @access public
         * @param  $email
         * @return integer number of results
         */
        function validateDatum($datum)
        {

            return preg_match('/^([0-9])(([-0-9._])*([0-9]))*\.([0-9])' .'(([0-9-])*([0-9]))+' . '(\.([0-9])([-0-9_-])?([0-9])+)+$/i', $datum);

        }

        /**
         * validatePasswordStrength - validateif a password has
         * - at least 8 Character, but not mroe than 20
         * - a numer
         * - at least one letter
         * - one capital letter
         *
         * @access public
         * @param  $email
         * @return integer number of results
         */
        function validatePasswordStrength($pwd)
        {

            return preg_match("#.*^(?=.{8,20})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).*$#", $pwd);

        }


        /**
         * validateUhrzeit - validate whether Uhrzeit is in format hh:mm
         *
         * @access public
         * @param  $email
         * @return integer number of results
         */
        function validateUhrzeit($uhrzeit)
        {

            //return preg_match('/^([0-9])(([-0-9._])*([0-9]))*\.([0-9])' .'(([0-9-])*([0-9]))+' . '(\.([0-9])([-0-9_-])?([0-9])+)+$/i', $uhrzeit);
            return preg_match('/^([:0-9]{5})$/i', $uhrzeit);

        }

        /**
         * date_mysql2german
         * wandelt ein MySQL-DATE (ISO-Date)
         * in ein traditionelles deutsches Datum um.
         */
        function date_mysql2german($datum)
        {
            if($datum != '') {
                list($jahr, $monat, $tag) = explode("-", $datum);

                return sprintf("%02d.%02d.%04d", $tag, $monat, $jahr);
            }else{
                return;
            }
        }


        /**
         * date_german2mysql
         * wandelt ein traditionelles deutsches Datum
         * nach MySQL (ISO-Date).
         */
        function date_german2mysql($datum)
        {

            if ($datum!='') {
                list($tag, $monat, $jahr) = explode(".", $datum);

                return sprintf("%04d-%02d-%02d", $jahr, $monat, $tag);
            }else{
                return null;
            }
        }






        /**
         * validateEmail - validate whether email is in format string@string.string
         *
         * @access public
         * @param  $email
         * @return boolean
         */
        function validateEmail($email)
        {

            return preg_match('/^([a-z0-9])(([-a-z0-9._])*([a-z0-9]))*\@([a-z0-9])' .'(([a-z0-9-])*([a-z0-9]))+' . '(\.([a-z0-9])([-a-z0-9_-])?([a-z0-9])+)+$/i', $email);

        }

        public function text_split($string, $length = 700, $etc = '...',
            $break_words = false
        ) {
            if ($length == 0) {
                return '';
            }

            if (strlen($string) > $length) {
                $length -= strlen($etc);
                if (!$break_words) {
                    $string = preg_replace('/\s+?(\S+)?$/', '', substr($string, 0, $length+1));
                }

                return substr($string, 0, $length).$etc;
            } else {
                return $string;
            }
        }


        /**
         * validateTime - validate a time string if its in hh:mm
         *
         * @access public
         * @param  $time
         * @return boolean
         */
        public function validateTime($time)
        {

            if($time != '') {
                $times = explode(':', $time);

                if(is_array($times) === true && count($times) == 2) {

                    $hours = $times[0];

                    $minutes = $times[1];

                    if($minutes >= 60) {

                        return false;

                    }else{

                        return true;

                    }

                }else{

                    return false;

                }

            }else{

                return true;

            }
        }

        /**
         * time2Seconds - converts a timestring from hh:mm to seconds or back
         *
         * @access public
         * @param  $time, $mode
         * @return boolean
         */
        public function time2Seconds($time, $mode=1)
        {

            //Mode 1: From hh:mm to seconds
            //Mode 2: From seconds to hh:mm

            if($time == '') { $time = '00:00';
            }

            if($mode == 1) {

                if($this->validateTime($time) === true) {

                    $time = explode(':', $time);

                    $hours = $time[0];

                    $minutes = $time[1];

                    $seconds = ($hours * 3600) + ($minutes * 60);

                    return $seconds;

                }else{

                    return 0;

                }


            }elseif($mode == 2) {

                if($time<0) {
                    $time = $time * -1;
                    $sign = '- ';
                }else{
                    $sign = '';
                }

                $hours = floor($time / 3600);

                $minutes = ($time % 3600) / 60;

                return ''.$sign.''.str_pad($hours, 2, '0', STR_PAD_LEFT).':'.str_pad($minutes, 2, '0', STR_PAD_LEFT).'';

            }



        }

        /**
         * validatePositiveInteger - validate whether $string is in numeric format and has 5 digits
         *
         * @access public
         * @param  $string
         * @return integer number of results
         */
        function validatePositiveInteger($string)
        {
            $string=trim($string);

            if ((is_numeric($string))) {
                //check against modulo
                if (($string % 1 )==0) {
                    return true;
                }else{
                    return false;
                }

            }else{

                return false;
            }




        }


        //Findet ein Bild im HTML Text und gibt den src zurück
        public function str_img_src($html)
        {
            if (stripos($html, '<img') !== false) {
                $imgsrc_regex = '#<\s*img [^\>]*src\s*=\s*(["\'])(.*?)\1#im';
                preg_match($imgsrc_regex, $html, $matches);
                unset($imgsrc_regex);
                unset($html);
                if (is_array($matches) && !empty($matches)) {
                    return $matches[2];
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }






    }
}
