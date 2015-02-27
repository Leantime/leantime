<?php

/**
 * Fileupload class - Data filuploads
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @license	GNU/GPL, see license.txt
 *
 */

class fileupload {

	/**
	 * @access private
	 * @var string path on the server
	 */
	private $path;

	/**
	 * @access public
	 * @var integer max filesize in kb
	 */
	public $max_size = 10000;

	/**
	 * @access private
	 * @var string filename in a temporary variable
	 */
	private $file_tmp_name;

	/**
	 * @access public
	 * @var integer
	 */
	public $file_size;

	/**
	 * @access public
	 * @var string give the file-type (not extension)
	 */
	public $file_type;

	/**
	 * @access public
	 * @var string - Name of file after renaming and on server
	 */
	public $file_name;

	/**
	 * @access public
	 * @var string
	 */
	public $error = '';

	/**
	 * @access public
	 * @var string name of file after by upload
	 */
	public $real_name='';

	/**
	 * @access public
	 * @var object configuration object
	 */
	public $config;

	/**
	 * __construct - get configuration and set path
	 *
	 * @return object
	 */
	function __construct() {

		$this->config = new config();

		$this->setPath($this->config->userFilePath);

		$this->checkUploadPermission();

	}

	public function getPath() {
		
		return $this->path;
	}

	public function setPath($path){
		
		$this->path = $path;
		
		if($this->checkUploadPermission()){
				
			return true;
		
		}else{
			
			return false;
		
		}
		
	}
	/**
	 * initFile - init variables of file
	 *
	 * @access public
	 * @param $file get file from Post
	 */
	public function initFile($file) {

		$this->file_tmp_name       = $file['tmp_name'];
		$this->file_size           = $file['size'];
		$this->file_type           = $file['type'];
		$this->file_name           = $file['name'];
		$this->path_parts		   = pathinfo($file['name']);
	}

	/**
	 * checkUploadPermission - Check the write Permission of Folder (777)
	 *
	 * @access private
	 * @return boolean|string
	 */
	private function checkUploadPermission(){

		if((is_dir($this->path)!=false) && ($this->path !='')){

			if(substr(decoct( fileperms($this->path) ), 2)==777){
					
				if(strtolower(ini_get('file_uploads'))=='on' || ini_get('file_uploads')==1){

					return true;

				}else{
						
					$this->error = 'NO_UPLOAD_PERMISSION';
					return false;

				}
					
			}else{

				$this->error = 'NO_PERMISSION';
				return false;
			}

		}else{
				
			$this->error = 'NO_PATH';
			return false;
		}

	}

	/**
	 * checkFileSize - Checks if filesize is ok
	 *
	 * @access private
	 * @return boolean
	 */
	public function checkFileSize() {

		if($this->file_size <= $this->max_size*1024){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * renameFile
	 *
	 * @param $name
	 * @return string
	 */
	public function renameFile($name) {

		$this->real_name = $this->file_name;

		if($name != ''){

			$this->file_name = $name.'.'.$this->path_parts['extension'];
				
			return true;
				
		}else{

			return false;

		}

	}

	/**
	 * upload - move file from tmp-folder to path
	 *
	 * @access public
	 * @return boolean
	 */
	public function upload() {

		if(move_uploaded_file($this->file_tmp_name, $this->path.$this->file_name)){

			return true;

		} else {

			return false;
		}

	}

	/**
	 * deleteFile - delete file from server
	 *
	 * @access public
	 * @param $file
	 * @return boolean
	 */
	public function deleteFile($file) {

		if(file_exists($this->path.$file)==true){

			if(unlink($this->path.$file)==true){

				return true;

			}else{

				return false;
					
			}
				
		}else{

			return false;

		}
	}

}

?>