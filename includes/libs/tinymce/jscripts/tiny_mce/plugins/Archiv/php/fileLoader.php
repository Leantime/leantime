<?php
/**
 * Archiv javascript file loader
 * 
 * @id: $Id: fileLoader.php,v 1.3 2009/10/27 20:15:55 wvankuipers Exp $
 * @version 1.0
 * @author Wouter van Kuipers (Archiv@pwnd.nl)
 * @copyright 2008-2009 PWND
 * @license LGPL 
 * @see http://archiv.pwnd.nl
 */
# enable compression
@ob_start("ob_gzhandler");
 
switch($_GET['file']){
	# Javascript files
	case 'javascript':
		header('Content-type: text/javascript');
		readfile('..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'tiny_mce_popup.js')."\r\n\r\n";
		readfile('..' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'flash_detect_min.js')."\r\n\r\n";
		readfile('..' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'jquery' . DIRECTORY_SEPARATOR . 'jquery-1.3.2.min.js')."\r\n\r\n";		
		readfile('..' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'jquery' . DIRECTORY_SEPARATOR . 'jquery-ui-1.7.2.custom.min.js')."\r\n\r\n";
		readfile('..' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'SWFupload' . DIRECTORY_SEPARATOR . 'swfupload.min.js')."\r\n\r\n";		
		readfile('..' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'json2.min.js')."\r\n\r\n";
		readfile('..' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'SWFupload' . DIRECTORY_SEPARATOR . 'handlers.min.js')."\r\n\r\n";		
		readfile('..' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'archiv.min.js');		
		break;
				
	# default 404
	default:
		header("HTTP/1.0 404 Not Found");
		break;
}

?>