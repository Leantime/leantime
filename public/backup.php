<?php
/**
 * backup.php - For Handling Backup DB.
 *
 */
define('ROOT', dirname(__FILE__));

use Aws\S3\Exception\S3Exception;
use Aws\S3;

include_once '../config/settings.php';
include_once '../src/core/class.autoload.php';
include_once '../config/configuration.php';

$config = new leantime\core\config();


function runBackup($backupFile, $config){

    $backupPath = $config->userFilePath.'/'.$backupFile;
    exec("mysqldump --user={$config->dbUser} --password={$config->dbPassword} --host={$config->dbHost} {$config->dbDatabase} --result-file={$backupPath} 2>&1", $output = array(),$worked);


    switch ($worked) {
        case 0:
            return array('type'=>'success','msg'=> 'The Database ' .$config->dbDatabase .' is save in the path '.getcwd().'/' .$backupPath );
            break;
        case 1:
            return array('type'=>'error','msg'=>'There was an error backup ' .$config->dbDatabase . ' to ' . getcwd() . '/' . $backupPath);
            break;
        case 2:
            return array('type'=>'error','msg'=>'There was an error: Database MySQL: ' . $config->dbDatabase );
            break;
    }

}

function uploadS3($backupFile, $config){
   
    $s3Client = new S3\S3Client(
        [
            'version'     => 'latest',
            'region'      => $config->s3Region,
            'endpoint'    => $config->s3EndPoint,
            'credentials' => [
                'key'    => $config->s3Key,
                'secret' => $config->s3Secret
            ]
        ]
    );

    try {
       
        $result = $s3Client->putObject([
            'Bucket' => $config->s3Bucket,
            'Key'    => $config->s3FolderName . '/backupdb/' . $backupFile,
            'Body'   => fopen($config->userFilePath.'/'.$backupFile, 'r'),
            'ACL'    => 'private'
        ]);
        $URL = $result->get('ObjectURL');
       return $URL;
    } catch (Aws\S3\Exception\S3Exception $e) {
       return "There was an error uploading the file. ".$e->getMessage();
    }

}


if($config->useS3 == true){

    $timezone  = -6; //(GMT -6:00) Central Time
    $date = gmdate("Ymd-Hi", time() + 3600 * ($timezone + date("I")));
    $backupFile = $config->dbDatabase . '_' . $date . '.sql';

    $run = runBackup($backupFile, $config);

    if($run['type']=="success"){
        $S3 = uploadS3($backupFile, $config);
        @unlink($config->userFilePath.'/'.$backupFile);
    }

}else{
    $run = runBackup($backupFile, $config);
    $S3=NULL;
    if($run['type']=="success"){
        @unlink($config->userFilePath.'/'.$backupFile);
    }

}

header('Content-Type: application/json');
echo json_encode(array('backup' => $run['msg'], 's3' => $S3));

