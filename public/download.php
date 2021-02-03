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

$login = leantime\core\login::getInstance(leantime\core\session::getSID());
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

	$encName = preg_replace("/[^a-zA-Z0-9]+/", "", $_GET['encName']);
 	$realName = $_GET['realName'];
 	$ext = preg_replace("/[^a-zA-Z0-9]+/", "", $_GET['ext']);
 	$module = preg_replace("/[^a-zA-Z0-9]+/", "", $_GET['module']);

	$mimes = array
    (
        'jpg' => 'image/jpg',
        'jpeg' => 'image/jpg',
        'gif' => 'image/gif',
        'png' => 'image/png'
    );

	//TODO: Replace with ROOT
  	$path = realpath(__DIR__."/../".$config->userFilePath."/");

  	$fullPath = $path."/".$encName.'.'.$ext;

	if (file_exists(realpath($fullPath))) {

		if ($fd = fopen(realpath($fullPath), 'rb')) {

            $path_parts = pathinfo($fullPath);

            if($ext == 'pdf'){
                header('Content-type: application/pdf');
                header("Content-disposition: attachment; filename=\"".$realName.".".$ext."\"");

            }elseif($ext == 'jpg' || $ext == 'jpeg' || $ext == 'gif' || $ext == 'png'){

                header('content-type: '. $mimes[$ext]);
                header('content-disposition: inline; filename="'.$realName.".".$ext.'";');
                header('Cache-Control: max-age=300');

            }else{

                header("Content-type: application/octet-stream");
                header("Content-Disposition: filename=\"".$realName.".".$ext."\"");

            }

            if(ob_get_length() > 0) {
                ob_end_clean();
            }

            $chunkSize = 1024*1024;

            while (!feof($fd)) {
                $buffer = fread($fd, $chunkSize);
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
    $encName = preg_replace("/[^a-zA-Z0-9]+/", "", $_GET['encName']);
    $realName = $_GET['realName'];
    $ext = preg_replace("/[^a-zA-Z0-9]+/", "", $_GET['ext']);
    $module = preg_replace("/[^a-zA-Z0-9]+/", "", $_GET['module']);

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
        'endpoint' => $config->s3EndPoint,
        'use_path_style_endpoint' => $config->s3UsePathStyleEndpoint,
        'credentials' => [
            'key'    => $config->s3Key,
            'secret' => $config->s3Secret
        ]
    ]);

    try {
        // implode all non-empty elements to allow s3FolderName to be empty. 
        // otherwise you will get an error as the key starts with a slash
        $fileName = implode('/', array_filter(array($config->s3FolderName, $encName.".".$ext)));
        $cmd = $s3Client->getCommand('GetObject', [
            'Bucket' => $config->s3Bucket,
            'Key' => $fileName,
            'ResponseContentDisposition' => "filename=".$realName.".".$ext.""
        ]);

        $request = $s3Client->createPresignedRequest($cmd, '5 minutes');
        $presignedUrl = (string)$request->getUri();

        header("Location:".$presignedUrl);

        exit();



    } catch (Aws\S3\Exception\S3Exception $e) {

        echo $e->getMessage()."\n";

    }
}