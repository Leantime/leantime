<?php

/**
 * Helper class - helping functions
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @license	GNU/GPL, see license.txt
 *
 */

class helper {

	/**
	 *
	 * @return
	 */
	public function __construct() {

	}


	
	/**
	 * isInRheinlandPfalz - check if plz is in Rheinland-Pfalz
	 *
	 * @access public
	 * @param $email
	 * @return integer number of results
	 */
	function isInRheinlandPfalz($plz) {
		$myReturn=false;
		
		if ( ($plz == (51598))||
		     (($plz >= 53401)&&($plz <= 53579))||
		     (($plz >= 53614)&&($plz <= 53619))||
		     (($plz >= 54181)&&($plz <= 55239))||  
		     (($plz >= 55253)&&($plz <= 56869))||  
		     (($plz >= 57501)&&($plz <= 57648))||  
		     (($plz ==65326 ))||  
		     (($plz ==65391 ))||  
		     (($plz >=65558 )&&($plz <= 65582))||  
		     (($plz >= 65621)&&($plz <= 65626))||  
		     (($plz == 65629 ))||  
		     (($plz >= 66461)&&($plz <=66509 ))||  
		     (($plz >= 66841 )&&($plz <= 67829))
		    ){
			$myReturn=true;
		}
				
		/*		
		53401-53579 OK
		53614-53619 OK
		54181-55239 OK
		55253-56869 OK
		57501-57648 OK
		65326-65326 OK
		65391-65391 OK
		65558-65582 OK
		65621-65626 OK
		65629-65629 OK
		66461-66509 OK
		66841-67829 OK
		*/	
		
		return $myReturn;
	}
	
	/**
	 * decimalToCurrencyFormat
	 *
	 * @access public
	 * @param $decimal
	
	 * @return $myCurrencyAmount string
	 */
	
	public function decimalToCurrencyFormat($amount){
		
		
		$amount = trim($amount);	// ggf. Leerzeichen entfernen
		$amount = round($amount,2);
		if( preg_match( '/^(\-)?(\d+)(?:\.(\d{0,2})?)?$/', $amount, $x ) ) {
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
		return number_format( $myCurrencyAmount, 2, ',', '.');
		
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
	 * currencyFormatToDecimal
	 *
	 * @access public
	 * @param $amount decimal
	
	 * @return $myDecimalAmount decimal
	 */
	
	public function currencyFormatToDecimal($amount){
		$amount = trim($amount);	// ggf. Leerzeichen entfernen
	
		if( preg_match( '/^(\-)?(\d+(?:\.\d{3})*)(?:,(\d{0,2})?)?$/', $amount, $x ) ) {
			// Korrekt formatiertes currencyFormat mit und ohne Dezimalteil
			$vz = $x[1];
			$vk = str_replace( '.', '', $x[2] );
			$nk = isset($x[3]) ? $x[3] : '0';
		}
		else {
			// Kein g�ltiger Eingabewert
			return '0.00';
		}
	
		$myDecimalAmount = "$vz$vk.$nk";
		return number_format( $myDecimalAmount, 2, '.', '');
	}
	
	
	/**
	
	Die Funktion isSCurrencyFormat soll validieren, ob ein korrekter Betrag in W�hrung eingegeben wurde
	
	0,5 wird dabei genauso akzeptiert wie 0,50 (50 Cent)
	Nicht aktzeptiert wird zB 1.0 oder 1.00,0 etc
	Bei positivem Test wird true, ansonsten false zur�ckgegeben
	
	**/
	
	
	
	/**
	 * isSCurrencyFormat
	 *
	 * @access public
	 * @param $amount decimal
	
	 * @return $myReturn boolean
	 */
	
	public function isSCurrencyFormat($amount){
		$amount = trim($amount);	// ggf. Leerzeichen entfernen
		
		if( preg_match( '/^\-?\d{1,3}(\.\d{3})*(,\d{0,2}?)?$/', $amount ) ) {
			// Korrekt formatiertes currencyFormat mit und ohne Dezimalteil
			return true;
		}
		return false;
	}
	
	
	
	/**
	 * decimalToCurrencyFormat
	 *
	 * @access public
	 * @param $decimal
	
	 * @return $myCurrencyAmount string
	 */
	
	public function decimalToCurrencyFormatALT($amount){
		$amount = trim($amount);	// ggf. Leerzeichen entfernen
		return $amount;
		if( preg_match( '/^(\d+)(?:\.(\d{0,2})?)?$/', $amount, $x ) ) {
			// Ganzzahl oder Dezimalwert mit bis zu 2 Nachkommastellen
			$vk = $x[1];
			$nk = isset($x[2]) ? $x[2] : 0;
		}
		else {
			// Kein g�ltiger Eingabewert
			return '0,00';
		}
	
		$myCurrencyAmount = "$vk.$nk";
		return number_format( $myCurrencyAmount, 2, ',', '.');
	}
	
	/**
	 * currencyFormatToDecimal
	 *
	 * @access public
	 * @param $amount decimal
	
	 * @return $myDecimalAmount decimal
	 */
	
	public function currencyFormatToDecimalALT($amount){
		$amount = trim($amount);	// ggf. Leerzeichen entfernen
	
		if( preg_match( '/^(\d+(?:\.\d{3})*)(?:,(\d{0,2})?)?$/', $amount, $x ) ) {
			// Korrekt formatiertes currencyFormat mit und ohne Dezimalteil
			// Tausenderseparator darf fehlen
			$vk = str_replace( '.', '', $x[1] );
			$nk = isset($x[2]) ? $x[2] : 0;
		}
		else {
			// Kein g�ltiger Eingabewert
			return '0.00';
		}
	
		$myDecimalAmount = "$vk.$nk";
		return number_format( $myDecimalAmount, 2, '.', '');
	}
	
	
	/**
	 * isSCurrencyFormat
	 *
	 * @access public
	 * @param $amount decimal
	
	 * @return $myReturn boolean
	 */
	
	public function isSCurrencyFormatALT($amount){
		$amount = trim($amount);	// ggf. Leerzeichen entfernen
		
		if( preg_match( '/^\d+(\.\d{3})*(,\d{0,2}?)?$/', $amount ) ) {
			// Korrekt formatiertes currencyFormat mit und ohne Dezimalteil
			// Tausenderseparator darf fehlen (sonst ^\d+ in ^\d{1,3} �ndern...)
			return true;
		}
		return false;
	}
	
	
		
	/**
	 * date2timestamp -  takes date(M/D/Y) and returns date(Y-M-D)
	 *
	 * @param $date 
	 * @return string
	 * 
	 **/
	 
	public function date2timestamp($date, $time = false) {
			
		$dateArr = explode('/', $date);
		$return = date('Y-m-d', strtotime($date));
		
		if ($time!=false)
			$return = date('Y-m-d H:i:s',strtotime($date.' '.$time));
		
		return $return;
	}

	
	/**
	 * timestamp2date - transforms a datetimestring to a readable format and back
	 *
	 * @access public
	 * @param $timestamp
	 * @param $mode
	 * @return string
	 */
	public function timestamp2date($timestamp, $mode){

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

		if($timestamp == ''){
				
			return '0000-00-00 00:00:00';

		}elseif($timestamp == '0000-00-00 00:00:00'){
		
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
	 * @param $arr
	 * @return comma separated list(string)
	 */
	
 		function getMultipleValues( $arr) {
			$myLIST="";
			if(is_array($arr) === true){
				$sizearr = sizeof($arr);
				
			 	for($i=0; $i<$sizearr; $i++){
			  	
			 		$myLIST=$myLIST.$arr[$i];
			  	
				 	if ($i>=0){
					   	if ($i<$sizearr-1 ){
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
	 * @param $arr
	 * @return comma separated list(string)
	 */
	function checkRadioPost($postIndex){
		
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
	 * @param $email
	 * @return integer number of results
	 */
	function validateDatum($datum) {

		return preg_match('/^([0-9])(([-0-9._])*([0-9]))*\.([0-9])' .'(([0-9-])*([0-9]))+' . '(\.([0-9])([-0-9_-])?([0-9])+)+$/i', $datum);

	}
	
	/**
	 * validatePasswordStrength - validateif a password has 
	 * - at least 8 Character, but not mroe than 20
	 * - a numer
	 * - at least one letter
	 * - one capital letter
	 * 
	 *
	 * @access public
	 * @param $email
	 * @return integer number of results
	 */
	function validatePasswordStrength($pwd){
		
		return preg_match("#.*^(?=.{8,20})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).*$#", $pwd);
	
	}
	
	
	/**
	 * validateUhrzeit - validate whether Uhrzeit is in format hh:mm
	 *
	 * @access public
	 * @param $email
	 * @return integer number of results
	 */
	function validateUhrzeit($uhrzeit) {

		//return preg_match('/^([0-9])(([-0-9._])*([0-9]))*\.([0-9])' .'(([0-9-])*([0-9]))+' . '(\.([0-9])([-0-9_-])?([0-9])+)+$/i', $uhrzeit);
		return preg_match('/^([:0-9]{5})$/i', $uhrzeit);
			
	}
		
/**
	* date_mysql2german
	* wandelt ein MySQL-DATE (ISO-Date)
	* in ein traditionelles deutsches Datum um.
	*/
	function date_mysql2german($datum) {
		if($datum != ''){
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
	function date_german2mysql($datum) {
		
		if ($datum!=''){
			list($tag, $monat, $jahr) = explode(".", $datum);

			return sprintf("%04d-%02d-%02d", $jahr, $monat, $tag);
		}else{
			return NULL;
		}
	} 
	

	/**
	 * validatePLZ - validate whether PLZ is in numeric format and has 5 digits
	 *
	 * @access public
	 * @param $plz
	 * @return integer number of results
	 */
	function validatePLZ($plz) {
		
		return preg_match('/^([0-9]{5})$/i', $plz);
		

	}
	
	

	/**
	 * validateEmail - validate whether email is in format string@string.string
	 *
	 * @access public
	 * @param $email
	 * @return boolean
	 */
	function validateEmail($email) {
		
		/*if (preg_match("/^([a-z0-9])(([-a-z0-9._])*([a-z0-9]))*\@([a-z0-9])' . '(([a-z0-9-])*([a-z0-9]))+' . '(\.([a-z0-9])([-a-z0-9_-])?([a-z0-9])+)+$/i", $email) == 0) {
				
			return false;

		}else{
				
			return true;

		}*/
		
		return preg_match('/^([a-z0-9])(([-a-z0-9._])*([a-z0-9]))*\@([a-z0-9])' .'(([a-z0-9-])*([a-z0-9]))+' . '(\.([a-z0-9])([-a-z0-9_-])?([a-z0-9])+)+$/i', $email);

	}
	
	public function text_split($string, $length = 700, $etc = '...',
		                                  $break_words = false)
		{
		    if ($length == 0)
		        return '';
		
		    if (strlen($string) > $length) {
		        $length -= strlen($etc);    
		        if (!$break_words)
		        $string = preg_replace('/\s+?(\S+)?$/', '', substr($string, 0, $length+1));
		      
		        return substr($string, 0, $length).$etc;
		    } else
		        return $string;
		}

		
	/**
	 * validateTime - validate a time string if its in hh:mm
	 *
	 * @access public
	 * @param $time
	 * @return boolean
	 */
	public function validateTime($time){

			if($time != ''){
				$times = explode(':', $time);
				
				if(is_array($times) === true && count($times) == 2){
					
					$hours = $times[0];
					
					$minutes = $times[1];
					
					if($minutes >= 60){
						
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
	 * @param $time, $mode
	 * @return boolean
	 */
	public function time2Seconds($time, $mode=1) {
		
		//Mode 1: From hh:mm to seconds
		//Mode 2: From seconds to hh:mm 
		
		if($time == '') $time = '00:00';
		
		if($mode == 1){
			
			if($this->validateTime($time) === true){
				
				$time = explode(':', $time);
				
				$hours = $time[0];
				
				$minutes = $time[1];
				
				$seconds = ($hours * 3600) + ($minutes * 60);
				
				return $seconds;
				
			}else{
				
				return 0;
				
			}
			
			
		}elseif($mode == 2){
			
			if($time<0) {
				$time = $time * -1;
				$sign = '- ';
			}else{
				$sign = '';
			}
			
			$hours = floor($time / 3600);

			$minutes = ($time % 3600) / 60;
			
			return ''.$sign.''.str_pad($hours, 2 ,'0', STR_PAD_LEFT).':'.str_pad($minutes, 2 ,'0', STR_PAD_LEFT).'';
			
		}
		
		
		
	}
	
	/**
	 * validatePositiveInteger - validate whether $string is in numeric format and has 5 digits
	 * @access public
	 * @param $string
	 * @return integer number of results
	 */
	function validatePositiveInteger($string) {
		$string=trim($string);
	
		if ((is_numeric($string))){
			//check against modulo
			if ( ($string % 1 )==0){
				return true;
			}else{				
				return false;
			}
		
		}else{
				
				return false;
		}
		
		
		

	}
	
	
	
	/**
	 * validatePercentagePosAndNeg - validate whether  is a decimal and positve and not exceeds 100
	 *
	 * @access public
	 * @param $percent
	 * @return integer number of results
	 */
	function validatePercentagePosAndNeg($percent) {
		
		$myreturn = false;
		
		
		if (is_numeric($percent)) 
 		{
					$myreturn = true;
 		}
	
		return $myreturn;
	}
	
	
	
	/**
	 * validatePercentage - validate whether  is a decimal and positve and not exceeds 100
	 *
	 * @access public
	 * @param $percent
	 * @return integer number of results
	 */
	function validatePercentage($percent) {
		
		$myreturn = false;
		
		
		if (is_numeric($percent)) 
 		{
			if (($percent>=0)) 
 			{		
 				if (($percent<=100)) 
 				{		
 					$myreturn = true;
 				}
 			}
		
	    }
	
		return $myreturn;
	}
	
	
	/**
	 * validateBearbeitungsgebuehr  
	 *
	 * @access public
	 * @param $amount
	 * @return boolean
	 */
	function validateBearbeitungsgebuehr($amount) {
		
		$myreturn = false;
	
		if (is_numeric($amount)) {
 		
			if (($amount>=0)) 
 			{	
 				if (($amount<1000000)) 
 				{	
 					$myreturn = true;
 				}
 			}
			
	    }else{
	    	$myreturn = false;
	    }
	
		return $myreturn;
	}
	
	
	/**
	 * validatePositiveMoneyAmount - validate whether  is a decimal and positive 
	 *
	 * @access public
	 * @param $amount
	 * @return integer number of results
	 */
	function validatePositiveMoneyAmount($amount) {
		
		$myreturn = false;
	
		if (is_numeric($amount)) {
 		
			if (($amount>0)) 
 			{	
 				if (($amount<1000000)) 
 				{	
 					$myreturn = true;
 				}
 			}
			
	    }else{
	    	$myreturn = false;
	    }
	
		return $myreturn;
	}
	

	
	 /**
	 * validatePositiveOrNegativeMoneyAmount - validate whether  is a decimal and positive or negative
	 *
	 * @access public
	 * @param $amount
	 * @return integer number of results
	 */
	function validatePositiveOrNegativeMoneyAmount($amount) {
		
		$myreturn = false;
	
		if (is_numeric($amount)) {
 		
			if (($amount>0)||($amount<0)) 
 			{	
 					$myreturn = true;
 				
 			}
			
	    }else{
	    	$myreturn = false;
	    }
	
		return $myreturn;
	}

	
	
	 /**
	 * validateMatchAnzahlPositionen
	 *
	 * @access public
	 * @param $amount
	 * @return boolean false of missing Positionen compared with entries made
	 */
	function validateMatchAnzahlPositionen($values) {
		
		$myreturn = true;

		for($i = 1; $i <= $values['numPositionen']; $i++){	
						
				$column1='positionen_column1_row'. $i;
				$myColumn1= $values[$column1];
				eval("\$myColumn1 = \"$myColumn1\";");
						
				$column2='positionen_column2_row'. $i;
				$myColumn2= $values[$column2];
				eval("\$myColumn2 = \"$myColumn2\";");
				
				$column3='positionen_column3_row'. $i;
				$myColumn3= $values[$column3];
				eval("\$myColumn3 = \"$myColumn3\";");
				
					/*echo 'numPositionen:' . $values['numPositionen']. "<hr>";
					echo "myColumn1;" . $myColumn1 . "<hr>";
					echo "myColumn2;" . $myColumn2 . "<hr>";
					echo "myColumn3;" . $myColumn3 . "<hr>";
					*/
				
				
				if ($myColumn1==''|| $myColumn2=='' || $myColumn3==''){
					
					$myreturn = false;
					
					break;
				
				}
			
						 
			}		
	    
	   
		return $myreturn;
	}
	
	

	/**
	 * validateMatchAnzahlPositionenFahrzeuge
	 *
	 * @access public
	 * @param $amount
	 * @return boolean false of missing Positionen compared with entries made
	 */
	function validateMatchAnzahlPositionenFahrzeuge($values) {
		
		$myreturn = true;

		for($i = 1; $i <= $values['numPositionen']; $i++){	
						
				$column1='positionen_column1_row'. $i;
				$myColumn1= $values[$column1];
				eval("\$myColumn1 = \"$myColumn1\";");
						
				$column2='positionen_column2_row'. $i;
				$myColumn2= $values[$column2];
				eval("\$myColumn2 = \"$myColumn2\";");
				
			
				
					/*echo 'numPositionen:' . $values['numPositionen']. "<hr>";
					echo "myColumn1;" . $myColumn1 . "<hr>";
					echo "myColumn2;" . $myColumn2 . "<hr>";
					
					*/
				
				
				if ($myColumn1==''|| $myColumn2=='' ){
					
					$myreturn = false;
					
					break;
				
				}
			
						 
			}		
	    
	   
		return $myreturn;
	}
	
	
	 /**
	 * validateMatchAnzahlPositionenFahrraeder
	 *
	 * @access public
	 * @param $amount
	 * @return boolean false of missing Positionen compared with entries made
	 */
	function validateMatchAnzahlPositionenFahrraeder($values) {
		
		$myreturn = true;

		for($i = 1; $i <= $values['numPositionen']; $i++){	
						
				$column1='positionen_column1_row'. $i;
				$myColumn1= $values[$column1];
				eval("\$myColumn1 = \"$myColumn1\";");
						
				$column2='positionen_column2_row'. $i;
				$myColumn2= $values[$column2];
				eval("\$myColumn2 = \"$myColumn2\";");
				
				$column3='positionen_column3_row'. $i;
				$myColumn3= $values[$column3];
				eval("\$myColumn3 = \"$myColumn3\";");
					
				$column5='positionen_column5_row'. $i;
				$myColumn5= $values[$column5];
				eval("\$myColumn5 = \"$myColumn5\";");
				
				$column6='positionen_column6_row'. $i;
				$myColumn6= $values[$column6];
				eval("\$myColumn6 = \"$myColumn6\";");
				
				
				$column7='positionen_column7_row'. $i;
				$myColumn7= $values[$column7];
				eval("\$myColumn7 = \"$myColumn7\";");
				
				$column8='positionen_column8_row'. $i;
				$myColumn8= $values[$column8];
				eval("\$myColumn8 = \"$myColumn8\";");
				
				
				if ($myColumn1==''|| $myColumn2=='' || $myColumn3==''|| $myColumn5=='' || $myColumn6==''|| $myColumn7=='' || $myColumn8==''){
					
					$myreturn = false;
					
					break;
				
				}
			
						 
			}		
	    
	    
		return $myreturn;
	}
	
	 /**
	 * validateMatchAnzahlPositionenPersonen
	 *
	 * @access public
	 * @param $amount
	 * @return boolean false of missing Positionen compared with entries made
	 */
	function validateMatchAnzahlPositionenPersonen($values) {
		
		$myreturn = true;
		
		//for debug only
		//print_r($values);
		//echo '<hr>';
		//echo "numPositionen" . $values['numPositionen'] . "<hr>";
		
			for($i = 1; $i <= $values['numPositionen']; $i++){	
						
				$column1='positionen_column1_row'. $i;
				$myColumn1= $values[$column1];
				eval("\$myColumn1 = \"$myColumn1\";");
						
				$column2='positionen_column2_row'. $i;
				$myColumn2= $values[$column2];
				eval("\$myColumn2 = \"$myColumn2\";");
				
				$column3='positionen_column3_row'. $i;
				$myColumn3= $values[$column3];
				eval("\$myColumn3 = \"$myColumn3\";");
					
					//for debug only
					//echo "myColumn1" . $myColumn1;
					//echo "strlenmyColumn1" . strlen($myColumn1);
					
				if ($myColumn1==''){
					
					$myreturn = false;
					
					break;
				
				}
			
						 
			}		
	    
	    
		return $myreturn;
	}
	
	
	/**
	 * validatePositionenDatum
	 *
	 * @access public
	 * @param $array
	 * @return boolean false 
	 */
	function validatePositionenDatum($values) {
		
		$myreturn = true;
		
		
			for($i = 1; $i <= $values['numPositionen']; $i++){	
						
				$column2='positionen_column2_row'. $i;
				$myColumn2= $values[$column2];
				eval("\$myColumn2 = \"$myColumn2\";");
				
				if ($this->validateDatum($myColumn2)==false){
					
					$myreturn = false;
					
					break;
				
				}
			
						 
			}		
	    
	    
		return $myreturn;
	}
	
	
/**
	 * validatePositionenVersicherungsSummen
	 *
	 * @access public
	 * @param $array
	 * @return boolean false 
	 */
	function validatePositionenVersicherungsSummen($values) {
		
		$myreturn = true;
		
		
			for($i = 1; $i <= $values['numPositionen']; $i++){	
						
				$column3='positionen_column3_row'. $i;
				$myColumn3= $values[$column3];
				eval("\$myColumn3 = \"$myColumn3\";");
				
				
				
				
				if ($this->validatePositiveMoneyAmount($myColumn3)==false){
					
					$myreturn = false;
					
					break;
				
				}
			
						 
			}		
	    
	    
		return $myreturn;
	}
	
	/**
	 * validateSchadenRegulierungsZahlungen - validate whether Uhrzeit is in format hh:mm
	 *
	 * @access public
	 * @param $values
	 * @return boolean
	 */
	function validateSchadenRegulierungsZahlungen($values) {
				
		$myReturn=true;
		$anzahlZahlungsPositionen=0;
		
		/*
		 1st check: Consistency, all or no field in row is filled
		*/
		$i=1;
		while ($i<16){
			
			if (($values['zahlung_col1_row'.$i])!=''){
				$myVal="1";
			}else{
				$myVal="0";
			}

			if (($values['zahlung_col2_row'.$i])!=''){
				$myVal=$myVal . "1";
			}else{
				$myVal=$myVal . "0";
			}
			
			if (($values['zahlung_col3_row'.$i])!=''){
				$myVal=$myVal . "1";
			}else{
				$myVal=$myVal . "0";
			}
			
			//We dont accept filled rows after empty rows
			if ($i>1){			
				if ($myPreviousVal=="000" && $myVal!="000"){
					
					$myReturn=false;
					break;
				}
			}
			//we dont accept half filled rows
			if ($myVal!="000" && $myVal!="111"){
				
				$myReturn=false;
				break;
			} 
			
			$myPreviousVal=$myVal;
			
		  $i++;	
		}
		
		
		/*
		
		 2nd check: 1st field in row must be psoitive MoneyAmount, 2nd field in row must be Date
		
		*/
		$i=1;
		while ($i<16){
			
			if (($values['zahlung_col1_row'.$i])!=''){
				if($this->validatePositiveMoneyAmount($values['zahlung_col1_row'.$i]) == false){
					
					echo $i;
					$myReturn=false;
					break;
				}
			}
			if (($values['zahlung_col2_row'.$i])!=''){
				if($this->validateDatum($values['zahlung_col2_row'.$i]) == false){
					
					$myReturn=false;
					break;
				}
			}		
		  $i++;	
		}
		
		return $myReturn;
			
	}

	//Findet ein Bild im HTML Text und gibt den src zurück
	public function str_img_src($html) {
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
	
	
	public function generatePassword($length=9, $strength=0) {
		$vowels = 'aeuy';
		$consonants = 'bdghjmnpqrstvz';
		if ($strength & 1) {
			$consonants .= 'BDGHJLMNPQRSTVWXZ';
		}
		if ($strength & 2) {
			$vowels .= "AEUY";
		}
		if ($strength & 4) {
			$consonants .= '23456789';
		}
		if ($strength & 8) {
			$consonants .= '@#$%';
		}
	 
		$password = '';
		$alt = time() % 2;
		for ($i = 0; $i < $length; $i++) {
			if ($alt == 1) {
				$password .= $consonants[(rand() % strlen($consonants))];
				$alt = 0;
			} else {
				$password .= $vowels[(rand() % strlen($vowels))];
				$alt = 1;
			}
		}
		return $password;
	}		
	
		
		
}

?>
