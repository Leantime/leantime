<?php
namespace leantime\core;

use Aws\S3\Exception\S3Exception;
use Aws\S3;
/**
 * Fileupload class - Data filuploads
 *
 */

class fileupload
{

    /**
     * @access private
     * @var    string path on the server
     */
    private $path;

    /**
     * @access public
     * @var    integer max filesize in kb
     */
    public $max_size = 10000;

    /**
     * @access private
     * @var    string filename in a temporary variable
     */
    private $file_tmp_name;

    /**
     * @access public
     * @var    integer
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
    public $real_name='';

    /**
     * @access public
     * @var    array parts of the path
     */
    public $path_parts=array();

    /**
     * @access public
     * @var    object configuration object
     */
    public $config;

    /**
     * @var \Aws\S3\S3Client|string
     */
    public $s3Client = "";


    /**
     * fileupload constructor.
     */
    function __construct()
    {

        $this->config = new config();
        $this->path = $this->config->userFilePath;
        
        if($this->config->useS3 == true) {
            // Instantiate the S3 client with your AWS credentials
            $this->s3Client = new S3\S3Client(
                [
                'version'     => 'latest',
                'region'      => $this->config->s3Region,
                'endpoint'    => $this->config->s3EndPoint,
                'use_path_style_endpoint' => $this->config->s3UsePathStyleEndpoint,
                'credentials' => [
                 'key'    => $this->config->s3Key,
                 'secret' => $this->config->s3Secret
                ]
                ]
            );

        }else{
            //Can discuss whether we want to allow local uploads again at some point...
            return false;

        }

        return false;

    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getAbsolutePath()
    {
        $path = realpath(__DIR__."/../../".$this->path);
       if($path === false){
           throw new \Exception("Path not valid");
       }else{
           return $path;
       }
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getPublicFilesPath()
    {
        $path = realpath(__DIR__."/../../public/userfiles");
        if($path === false){
            throw new \Exception("Path not valid");
        }else{
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

        $this->file_tmp_name       = $file['tmp_name'];
        $this->file_size           = $file['size'];
        $this->file_type           = $file['type'];
        $this->file_name           = $file['name'];
        $this->path_parts           = pathinfo($file['name']);
    }

    /**
     * checkFileSize - Checks if filesize is ok
     *
     * @access private
     * @return boolean
     */
    public function checkFileSize()
    {

        if($this->file_size <= $this->max_size*1024) {
            return true;
        }else{
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

        if($name != '') {

            if (isset($this->path_parts['extension'])) {
                $this->file_name = $name.'.'.$this->path_parts['extension'];
            } else {
                $this->file_name = $name;
            }

            return true;
                
        }else{

            return false;

        }

    }

    /**
     * upload - move file from tmp-folder to S3
     *
     * @access public
     * @return boolean
     */
    public function upload()
    {

        if($this->config->useS3 == true) {
            //S3 upload
            return $this->uplodToS3();
        }else{

            //Local upload
            return $this->uploadLocal();
        }

    }

    public function uploadPublic()
    {

        if($this->config->useS3 == true) {

            try {
                // Upload data.
                $file = fopen($this->file_tmp_name, "rb");
                // implode all non-empty elements to allow s3FolderName to be empty. 
                // otherwise you will get an error as the key starts with a slash
                $fileName = implode('/', array_filter(array($this->config->s3FolderName, $this->file_name)));

                $this->s3Client->upload($this->config->s3Bucket, $fileName, $file, "public-read");
                $url =  $this->s3Client->getObjectUrl($this->config->s3Bucket, $fileName);

                return $url;

            } catch (S3Exception $e) {

                error_reporting($e->getMessage());
                return false;

            }

        }else{

            try {

                if (move_uploaded_file($this->file_tmp_name, $this->getPublicFilesPath() . "/" . $this->file_name)) {
                    return "/userfiles/".$this->file_name;
                }

            }catch(\Exception $e){

                error_reporting($e->getMessage());
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

            error_reporting($e->getMessage());
            return false;

        }

    }

    private function uploadLocal() {

        try {

            if (move_uploaded_file($this->file_tmp_name, $this->getAbsolutePath() . "/" . $this->file_name)) {
                return true;
            }

        }catch(\Exception $e){

            error_reporting($e->getMessage());
            return false;
        }

        return false;

    }


}

