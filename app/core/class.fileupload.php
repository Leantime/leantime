<?php

namespace leantime\core;

use leantime\core\eventhelpers;
use Aws\S3\Exception\S3Exception;
use Aws\S3;
use Aws\S3\S3Client;
use Exception;

/**
 * Fileupload class - Data filuploads
 *
 */
class fileupload
{
    use eventhelpers;

    /**
     * @access private
     * @var    string path on the server
     */
    private $path;

    /**
     * @access public
     * @var    int max filesize in kb
     */
    public $max_size = 10000;

    /**
     * @access private
     * @var    string filename in a temporary variable
     */
    private $file_tmp_name;

    /**
     * @access public
     * @var    int
     */
    public $file_size;

    /**
     * @access public
     * @var    string give the file-type (not extension)
     */
    public $file_type;

    /**
     * @access public
     * @var    string - Name of file after renaming and on server
     */
    public $file_name;

    /**
     * @access public
     * @var    string
     */
    public $error = '';

    /**
     * @access public
     * @var    string name of file after by upload
     */
    public $real_name = '';

    /**
     * @access public
     * @var    array parts of the path
     */
    public $path_parts = array();

    /**
     * @access public
     * @var    object configuration object
     */
    public \leantime\core\environment $config;

    /**
     * @var S3Client|string
     */
    public $s3Client = "";

    /**
     * fileupload constructor.
     */
    public function __construct()
    {

        $this->config = \leantime\core\environment::getInstance();
        $this->path = $this->config->userFilePath;

        if ($this->config->useS3 == true) {
            // Instantiate the S3 client with your AWS credentials
            $this->s3Client = new S3Client(
                [
                    'version' => 'latest',
                    'region' => $this->config->s3Region,
                    'endpoint' => $this->config->s3EndPoint,
                    'use_path_style_endpoint' => $this->config->s3UsePathStyleEndpoint,
                    'credentials' => [
                        'key' => $this->config->s3Key,
                        'secret' => $this->config->s3Secret
                    ]
                ]
            );
        } else {
            //Can discuss whether we want to allow local uploads again at some point...
            return false;
        }

        return false;
    }

    /**
     * This function returns the maximum files size that can be uploaded
     * in PHP
     * @returns int File size in bytes
     **/
    public static function getMaximumFileUploadSize()
    {
        return min(self::convertPHPSizeToBytes(ini_get('post_max_size')), self::convertPHPSizeToBytes(ini_get('upload_max_filesize')));
    }

    /**
     * This function transforms the php.ini notation for numbers (like '2M') to an integer (2*1024*1024 in this case)
     *
     * @param string $sSize
     * @return integer The value in bytes
     */
    private static function convertPHPSizeToBytes($sSize)
    {
        //
        $sSuffix = strtoupper(substr($sSize, -1));
        if (!in_array($sSuffix,array('P','T','G','M','K'))){
            return (int)$sSize;
        }
        $iValue = substr($sSize, 0, -1);
        switch ($sSuffix) {
            case 'P':
                $iValue *= 1024;
            // Fallthrough intended
            case 'T':
                $iValue *= 1024;
            // Fallthrough intended
            case 'G':
                $iValue *= 1024;
            // Fallthrough intended
            case 'M':
                $iValue *= 1024;
            // Fallthrough intended
            case 'K':
                $iValue *= 1024;
                break;
        }
        return (int)$iValue;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getAbsolutePath()
    {
        $path = realpath(__DIR__ . "/../../" . $this->path);
        if ($path === false) {
            throw new Exception("Path not valid");
        } else {
            return $path;
        }
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getPublicFilesPath()
    {
        $relative_path = self::dispatch_filter('relative_path', "/../../public/userfiles");

        $path = realpath(__DIR__ . $relative_path);
        if ($path === false) {
            throw new Exception("Path not valid");
        } else {
            return $path;
        }
    }


    /**
     * initFile - init variables of file
     *
     * @access public
     * @param  $file $file from Post
     */
    public function initFile($file)
    {

        $this->file_tmp_name = $file['tmp_name'];
        $this->file_size = $file['size'];
        $this->file_type = $file['type'];
        $this->file_name = $file['name'];
        $this->path_parts = pathinfo($file['name']);
    }

    /**
     * checkFileSize - Checks if filesize is ok
     *
     * @access public
     * @return bool
     */
    public function checkFileSize()
    {

        if ($this->file_size <= $this->max_size * 1024) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * renameFile
     *
     * @param  $name
     * @return string
     */
    public function renameFile($name)
    {

        $this->real_name = $this->file_name;

        if ($name != '') {
            if (isset($this->path_parts['extension'])) {
                $this->file_name = $name . '.' . $this->path_parts['extension'];
            } else {
                $this->file_name = $name;
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * upload - move file from tmp-folder to S3
     *
     * @access public
     * @return bool
     */
    public function upload()
    {

        if ($this->config->useS3 == true) {
            //S3 upload
            return $this->uplodToS3();
        } else {
            //Local upload
            return $this->uploadLocal();
        }
    }

    public function uploadPublic()
    {

        if ($this->config->useS3 == true) {
            try {
                // Upload data.

                if ($this->file_tmp_name == null || $this->file_tmp_name == '') {
                    return false;
                }

                $file = fopen($this->file_tmp_name, "rb");
                // implode all non-empty elements to allow s3FolderName to be empty.
                // otherwise you will get an error as the key starts with a slash
                $fileName = implode('/', array_filter(array($this->config->s3FolderName, $this->file_name)));

                $this->s3Client->upload($this->config->s3Bucket, $fileName, $file, "public-read");
                $url = $this->s3Client->getObjectUrl($this->config->s3Bucket, $fileName);

                return $url;
            } catch (S3Exception $e) {
                error_log($e, 0);
                return false;
            }
        } else {
            try {
                if (move_uploaded_file($this->file_tmp_name, $this->getPublicFilesPath() . "/" . $this->file_name)) {
                    return "/userfiles/" . $this->file_name;
                }
            } catch (Exception $e) {
                error_log($e, 0);
                return false;
            }
        }

        return false;
    }

    private function uplodToS3()
    {

        try {
            // Upload data.
            $file = fopen($this->file_tmp_name, "rb");
            // implode all non-empty elements to allow s3FolderName to be empty.
            // otherwise you will get an error as the key starts with a slash
            $fileName = implode('/', array_filter(array($this->config->s3FolderName, $this->file_name)));

            $this->s3Client->upload($this->config->s3Bucket, $fileName, $file, "authenticated-read");

            return true;
        } catch (S3Exception $e) {
            error_log($e, 0);
            return false;
        }
    }

    private function uploadLocal()
    {

        try {
            if (move_uploaded_file($this->file_tmp_name, $this->getAbsolutePath() . "/" . $this->file_name)) {
                return true;
            }
        } catch (Exception $e) {
            error_log($e, 0);
            return false;
        }

        return false;
    }

    public function displayImageFile($imageName) {

        $mimes = array
        (
            'jpg' => 'image/jpg',
            'jpeg' => 'image/jpg',
            'gif' => 'image/gif',
            'png' => 'image/png'
        );

        $path = realpath(APP_ROOT."/".$this->config->userFilePath."/");

        $fullPath = $path."/".$imageName;

        if (file_exists(realpath($fullPath))) {
            if ($fd = fopen(realpath($fullPath), 'rb')) {
                $path_parts = pathinfo($fullPath);
                $ext = $path_parts["extension"];

                if ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'gif' || $ext == 'png') {
                    header('Content-type: ' . $mimes[$ext]);
                    header('Content-disposition: inline; filename="' . $imageName . '";');

                    $chunkSize = 1024 * 1024;

                    while (!feof($fd)) {
                        $buffer = fread($fd, $chunkSize);
                        echo $buffer;
                    }
                    fclose($fd);
                }
            }
        }

    }
}
