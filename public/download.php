<?php
/**
 * downloads.php - For Handling Downloads.
 * 
 */
define('RESTRICTED', TRUE);
define('ROOT', dirname(__FILE__));

include_once '../config/settings.php';
include_once '../src/core/class.autoload.php';
include_once '../config/configuration.php';

$login = new leantime\core\login(leantime\core\session::getSID());
$config = new leantime\core\config();

 if ($login->logged_in()!==true) {

	exit();
 
 } else {

	if($config->useS3 == true){

		getFileFromS3();

	}else{

		getFileLocally();

	}
 	
 
 }

function getFileLocally(){
	
	$config = new leantime\core\config();
	
	$encName = $_GET['encName'];
 	$realName = $_GET['realName'];
 	$ext = $_GET['ext'];
 	$module = $_GET['module'];
 
	$mimes = array
    (
        'jpg' => 'image/jpg',
        'jpeg' => 'image/jpg',
        'gif' => 'image/gif',
        'png' => 'image/png'
    );
	
  	$path = realpath(__DIR__."/../".$config->userFilePath."/");

  	$fullPath = $path."/".$encName.'.'.$ext;

	if (file_exists(realpath($fullPath))) {

		if ($fd = fopen(realpath($fullPath), 'r')) {

		 	$path_parts = pathinfo($fullPath);
			
			if($ext == 'pdf'){
				header('Content-type: application/pdf');
				header("Content-disposition: attachment; filename=\"".$path_parts["basename"]."\"");
						
			}elseif($ext == 'jpg' || $ext == 'jpeg' || $ext == 'gif' || $ext == 'png'){
							 
   				header('content-type: '. $mimes[$ext]);
    			header('content-disposition: inline; filename="'.$path_parts["basename"].'";');
			
			}else{
				
				header("Content-type: application/octet-stream");
				header("Content-Disposition: filename=\"".$path_parts["basename"]."\"");

			}	
				
			while (!feof($fd)) {
				$buffer = fread($fd, 2048);
				echo $buffer;
			} 
			fclose($fd);					 
		} 

	}else{
        http_response_code(404);
        die();
	}
}

function getFileFromS3(){
		
	// Include the AWS SDK using the Composer autoloader.
	$encName = $_GET['encName'];
 	$realName = $_GET['realName'];
 	$ext = $_GET['ext'];
 	$module = $_GET['module'];
 
	$config = new leantime\core\config();
	
	$mimes = array
    (
        'jpg' => 'image/jpg',
        'jpeg' => 'image/jpg',
        'gif' => 'image/gif',
        'png' => 'image/png'
    );
	
	// Instantiate the client.
		
	$s3Client = new Aws\S3\S3Client([
			    'version'     => 'latest',
			    'region'      => $config->s3Region,
			    'credentials' => [
			        'key'    => $config->s3Key,
			        'secret' => $config->s3Secret
			    ]
			]);
	
	try {


        // Save object to a file.
        $result = $s3Client->getObject(array(
            'Bucket' => $config->s3Bucket,
            'Key'    => $config->s3FolderName."/".$encName.".".$ext,
            'ResponseCacheControl'       => 'No-cache',
            'ResponseExpires'            => gmdate(DATE_RFC2822, time() + 3600),
        ));

        // Display the object in the browser
        header("Content-Type: {$result['ContentType']}");
        header("Content-Disposition: filename=\"".$realName.".".$ext."\"");
        echo $result['Body'];


    } catch (Aws\S3\Exception\S3Exception $e) {
	
	    echo $e->getMessage()."\n";
	
	}
}