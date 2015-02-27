<?php
/**
 * downloads.php - For Handling Downloads.
 * 
 */
 session_start();
 $encName = $_GET['encName'];
 $realName = $_GET['realName'];
 $ext = $_GET['ext'];
 $module = $_GET['module'];
// include_once 'config/settings.php';
// include_once 'core/class.autoload.php';
// include_once 'config/configuration.php';
 /*$login = new login(session::getSID());
 if ($login->logged_in()!==true) {
	$login->showLogin();
 } else {*/
  	$path = $_SERVER['DOCUMENT_ROOT']."/userdata/".$module."/";
  	$fullPath = $path.$realName.'.'.$ext;
	if (file_exists($path.$encName.'.'.$ext)) {
	  	rename($path.$encName.'.'.$ext,$path.$realName.'.'.$ext);
		if ($fd = fopen($fullPath, 'r')) {			
		 	$path_parts = pathinfo($fullPath);	
			switch ($ext) {
				case 'pdf':
						header('Content-type: application/pdf');
						header("Content-disposition: attachment; filename=\"".$path_parts["basename"]."\"");
					break;
				default:
						header("Content-type: application/octet-stream");
						header("Content-Disposition: filename=\"".$path_parts["basename"]."\"");
					break;
			}		
			while (!feof($fd)) {
				$buffer = fread($fd, 2048);
				echo $buffer;
			} 
			fclose($fd);					 
		} 
		rename($path.$realName.'.'.$ext,$path.$encName.'.'.$ext);		
	}

?>
