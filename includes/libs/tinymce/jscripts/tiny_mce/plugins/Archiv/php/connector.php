<?php
/**
 * Archiv backend connector file
 * 
 * @id: $Id: connector.php,v 1.5 2009/10/27 20:15:55 wvankuipers Exp $
 * @version 1.0
 * @author Wouter van Kuipers (Archiv@pwnd.nl)
 * @copyright 2008-2009 PWND
 * @license LGPL 
 * @see http://archiv.pwnd.nl
 */

# Error reportings
error_reporting(1);

class Archiv{
	var $data; 					# all post/get/file data set by function getParameters	
	var $settings = array(); 	# holds all the configuration settings	
	var $language = array();	# holds all the language lines		
	
	/**
	 * Archiv constructor
	 * @return void
	 */
	function Archiv(){
		# get the parameters
		$this->loadParameters();
		
		# get settings
		if(!empty($this->data['settings_file'])){
			$this->loadSettings($this->data['settings_file']);
		}
		else{
			$this->output(array('message'=>'No config file defined!'));
		}
		
		$this->loadLanguageFile();
			
		if(isset($this->data['get'])){
			switch($this->data['get']){
				case "settings":
					$this->getSettings();
					break;
							
				case "dirList":
					$this->getDirectoryList();
					break;
					
				case "dirContent":
					$this->getDirectoryContent();
					break;
					
				default:
					break;				
			}
		}
		elseif(isset($this->data['doAction'])){
			switch($this->data['doAction']){			
				case "addDirectory":
					$this->addDirectory();
					break;
					
				case "addFile":
					$this->addFile();
					break;
					
				case "deleteFile":
					$this->deleteFile();
					break;
					
				case "deleteDirectory":
					$this->deleteDirectory();
					break;
					
				default:
					break;				
			}
		}
	}
	
	/**
	 * Get all the parameters from post data
	 * @todo escape input
	 * @return void 
	 */
	function loadParameters(){
		$this->data = $_POST;	
	}
	
	/**
	 * Load the settings from a given settings file
	 * @param string $settingsFile the location of the settings file
	 * @return void
	 */
	function loadSettings($settingsFile){
		if(is_file($settingsFile) && is_readable($settingsFile)){
			require_once($settingsFile);
			$this->settings = $s;
		}
		else{
			$this->output(array('message'=>'Could not read config file!'));
		}
	}
	
	/**
	 * Get all the language lines
	 * @return void 
	 */
	function loadLanguageFile(){
		if(is_file('..'. DIRECTORY_SEPARATOR . 'langs'. DIRECTORY_SEPARATOR . $this->settings['language'].".php")){
			require_once('..'. DIRECTORY_SEPARATOR . 'langs'. DIRECTORY_SEPARATOR . $this->settings['language'].".php");
			$this->language = $lang;
		}
		else{
			$this->output(array('message'=>'Cannot include language file `..'. DIRECTORY_SEPARATOR . 'langs'. DIRECTORY_SEPARATOR . $this->settings['language'].".php"."`"));
		}		
	}
	
	/**
	 * Output the settings to the browser
	 * @return void
	 */
	function getSettings(){ 
		$settings = array('Archiv_path'					=>$this->settings['upload_uri'],
					   	 	'Archiv_files'				=>$this->settings['selectable_files'],
					   	 	'Archiv_image_files'		=>$this->settings['selectable_images'],
					   	 	'Archiv_file_size_limit'	=>$this->settings['size_limit'],
					   	 	'Archiv_file_upload_limit'	=>$this->settings['upload_limit'],
							'Archiv_debug'				=>($this->settings['debug'] === true) ? 'true' : 'false',
							);
	
		$this->output($settings);
	}

	/**
	 * Add a directory to the current selected directory or to the root if no directory selected
	 * @return void
	 */
	function addDirectory(){
		$file = !empty($this->data['dirName']) ? $this->data['dirName'] : null;
		$path = $this->getFullPath($this->data['dirRoot']); 
		
		if( is_dir($path) && is_writable($path)){
			if(!is_dir($path . $file)){
				if(mkdir($path . $file)){
					$this->output(array('message'=>"ok"));		
				}
				else{
					$this->output(array('message'=>$this->language['ErrorCreatingDirectory']));
				}
			}
			else{
				$this->output(array('message'=>$this->language['ErrorDirectoryAlreadyExists']));
			}
		}
		else{
			$this->output(array('message'=>$this->language['ErrorUnableToReadPath']." `".$path."`"));		
		}
	}
	
	/**
	 * Function to upload a file to the current selected directory or to the root if no directory selected
	 * @return true on success else false
	 */
	function addFile(){
		# Check the upload
		if (!isset($_FILES["Filedata"]) || !is_uploaded_file($_FILES["Filedata"]["tmp_name"]) || $_FILES["Filedata"]["error"] != 0) {
			header("HTTP/1.1 500 Internal Server Error");
			$this->output(array('message'=>$this->language['ErrorInvalidUpload']));
		}
	
		# set filename and complete path
		$path = $this->getFullPath($this->data['path']);
		$file['name'] = $path . $_FILES["Filedata"]['name'];
		
		# check if file exists
		if(file_exists($file['name'])){
			$this->output(array('message'=>$this->language['ErrorFileAlreadyExists']));
		}
		
		#move file to upload dir
		$move = move_uploaded_file($_FILES["Filedata"]['tmp_name'], $file['name']);

		#get the info of the image
		list($width, $height) = @getimagesize($file['name']);
		$mime_type = $this->returnMIMEType($file['name']);

		#check if it is a picture
		if(in_array($mime_type,$this->settings['allowed_image_mime']) && $this->data['browser']=="images"){
			#picture
			$img['p_width']  = $width;
			$img['p_height'] = $height;
			
			#thumb
			$img['i_width']  = $img['p_width'];
			$img['i_height'] = $img['p_height'];
	
			#scale the image if needed
			if(($img['p_width'] > $this->settings['max_image_size']) || ($img['p_height'] > $this->settings['max_image_size'])){				
				if($img['p_width'] > $img['p_height']){
					$image_width  = $this->settings['max_image_size'];
					$image_height = round(($this->settings['max_image_size']/$img['i_width'])*$img['i_height']);
				}
				else{
					$image_width  = round(($this->settings['max_image_size']/$img['i_height'])*$img['i_width']);	
					$image_height = $this->settings['max_image_size'];
				}		
				
				$img['p_width']  = $image_width; 
				$img['p_height'] = $image_height;
			}
	
			# resize the thumb
			if($img['i_width'] > $img['i_height']){
				$image_small_width  = $this->settings['max_image_thumb_size'];
				$image_small_height = round(($this->settings['max_image_thumb_size']/$img['i_width'])*$img['i_height']);	
			}
			else{
				$image_small_width  = round(($this->settings['max_image_thumb_size']/$img['i_height'])*$img['i_width']);
				$image_small_height = $this->settings['max_image_thumb_size'];					
			}
	
			$img['i_width']  = $image_small_width;
			$img['i_height'] = $image_small_height;			
			
			switch($mime_type){
				case "image/jpg":
					$this->create_jpg($file['name'], $width, $height, $img['p_width'], $img['p_height'], $img['i_width'], $img['i_height']);
					break;
					
				case "image/png":
					$this->create_png($file['name'], $width, $height, $img['p_width'], $img['p_height'], $img['i_width'], $img['i_height']);
					break;
					
				case "image/gif":
					$this->create_gif($file['name'], $width, $height, $img['p_width'], $img['p_height'], $img['i_width'], $img['i_height']);
					break;
					
				default:
					break;				
			}			
		}
		# else check if the file has the right mime-type
		elseif(!in_array($mime_type,$this->settings['allowed_file_mime']) && $this->data['browser'] == "files" ||
				!in_array($mime_type,$this->settings['allowed_image_mime']) && $this->data['browser']=="images"){
			$unlink = unlink($file['name']);
			$this->output(array('message'=>$this->language['ErrorWrongMimeType'].' `'.$mime_type.'`'));
		}
		
		$this->output(array('message'=>"ok"));
	}
	
	/**
	 * Create a resized image from a jpg file
	 * @param string 	$file 			the location of the image
	 * @param int 		$width 			the width of the origional image
	 * @param int 		$height 		the height of the origional image
	 * @param int 		$pic_new_width 	the new width for the image
	 * @param int 		$pic_new_height	the new height for the image
	 * @param int 		$thumb_width 	the width for the thumb image
	 * @param int 		$thumb_height	the height for the thumb image
	 * @return void
	 */
	function create_jpg($file, $width, $height, $pic_new_width, $pic_new_height, $thumb_width, $thumb_height){
		#fetch the extention from the file
		$file_path 	= substr($file,0,strrpos($file,DIRECTORY_SEPARATOR));
		$file_name 	= substr($file,strlen($file_path)+1, (strrpos($file,".")-(strlen($file_path)+1)));
		$file_ext	= substr($file,strrpos($file,"."));

		#create the picture
		$pic  			= imagecreatetruecolor($pic_new_width, $pic_new_height);
		$source 		= imagecreatefromjpeg($file);
		$imgcpyrsmpld 	= imagecopyresampled( $pic, $source, 0, 0, 0, 0, $pic_new_width, $pic_new_height, $width, $height );
		$imagejpeg 		= imagejpeg($pic, $file_path.DIRECTORY_SEPARATOR.$file_name.$file_ext, 100);
		$imagedestroy 	= imagedestroy($pic);
		
		list($width, $height) = @getimagesize($file);

		#create the thumb
		$thumb  		= imagecreatetruecolor($thumb_width, $thumb_height);
		$source2 		= imagecreatefromjpeg( $file );
		$imgcpyrsmpld2 	= imagecopyresampled( $thumb, $source2, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height );		
		$imagejpeg2	 	= imagejpeg($thumb, $file_path.DIRECTORY_SEPARATOR.$file_name.'_thumb'.$file_ext, 100);
		$imagedestroy2 	= imagedestroy($thumb);
	}
	
	/**
	 * Create a resized image from a gif file
	 * @param string 	$file 			the location of the image
	 * @param int 		$width 			the width of the origional image
	 * @param int 		$height 		the height of the origional image
	 * @param int 		$pic_new_width 	the new width for the image
	 * @param int 		$pic_new_height	the new height for the image
	 * @param int 		$thumb_width 	the width for the thumb image
	 * @param int 		$thumb_height	the height for the thumb image
	 * @return void
	 */
	function create_gif($file, $width, $height, $pic_new_width, $pic_new_height, $thumb_width, $thumb_height){
		#fetch the extention from the file
		$file_path 	= substr($file,0,strrpos($file,DIRECTORY_SEPARATOR));
		$file_name 	= substr($file,strlen($file_path)+1, (strrpos($file,".")-(strlen($file_path)+1)));
		$file_ext	= substr($file,strrpos($file,"."));

		#create the picture
		$pic  			= imagecreatetruecolor($pic_new_width, $pic_new_height);
		$source 		= imagecreatefromgif($file);
		$imgcpyrsmpld 	= imagecopyresampled( $pic, $source, 0, 0, 0, 0, $pic_new_width, $pic_new_height, $width, $height );
		$imagegif 		= imagegif($pic, $file_path.DIRECTORY_SEPARATOR.$file_name.$file_ext);
		$imagedestroy 	= imagedestroy($pic);
		
		list($width, $height) = @getimagesize($file);

		#create the thumb
		$thumb  		= imagecreatetruecolor($thumb_width, $thumb_height);
		$source2 		= imagecreatefromgif( $file );
		$imgcpyrsmpld2 	= imagecopyresampled( $thumb, $source2, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height );		
		$imagegif2 		= imagegif($thumb, $file_path.DIRECTORY_SEPARATOR.$file_name.'_thumb'.$file_ext);
		$imagedestroy2 	= imagedestroy($thumb);
	}
	
	/**
	 * Create a resized image from a png file
	 * @param string 	$file 			the location of the image
	 * @param int 		$width 			the width of the origional image
	 * @param int 		$height 		the height of the origional image
	 * @param int 		$pic_new_width 	the new width for the image
	 * @param int 		$pic_new_height	the new height for the image
	 * @param int 		$thumb_width 	the width for the thumb image
	 * @param int 		$thumb_height	the height for the thumb image
	 * @return void
	 */
	function create_png($file,  $width, $height, $pic_new_width, $pic_new_height, $thumb_width, $thumb_height){
		#fetch the extention from the file
		$file_path 	= substr($file,0,strrpos($file,DIRECTORY_SEPARATOR));
		$file_name 	= substr($file,strlen($file_path)+1, (strrpos($file,".")-(strlen($file_path)+1)));
		$file_ext	= substr($file,strrpos($file,"."));

		#create the picture
		$pic  			= imagecreatetruecolor($pic_new_width, $pic_new_height);
		$source 		= imagecreatefrompng($file);
		$imgcpyrsmpld 	= imagecopyresampled( $pic, $source, 0, 0, 0, 0, $pic_new_width, $pic_new_height, $width, $height );
		
		if(version_compare(PHP_VERSION, '5.1.2', '<')){
			$imagepng 	= imagepng($pic, $file_path.DIRECTORY_SEPARATOR.$file_name.$file_ext);
		}
		else{
			$imagepng 	= imagepng($pic, $file_path.DIRECTORY_SEPARATOR.$file_name.$file_ext, 0);
		}		
		
		$imagedestroy 	= imagedestroy($pic);
		
		list($width, $height) = @getimagesize($file);

		#create the thumb
		$thumb  			= imagecreatetruecolor($thumb_width, $thumb_height);
		$source2 			= imagecreatefrompng( $file );		
		$imgcpyrsmpld2 		= imagecopyresampled( $thumb, $source2, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height );
		
		if(version_compare(PHP_VERSION, '5.1.2', '<')){
			$imagepng2 		= imagepng($thumb, $file_path.DIRECTORY_SEPARATOR.$file_name.'_thumb'.$file_ext);
		}
		else{
			$imagepng2 		= imagepng($thumb, $file_path.DIRECTORY_SEPARATOR.$file_name.'_thumb'.$file_ext, 0);
		}
		
		$imagedestroy2 		= imagedestroy($thumb);
	}
	
	/**
     * Return file MIME type
     * @param string $filename the location of the file
     * @return string $image file type
     */ 
	function returnMIMEType($filename)
    {
        preg_match("|\.([a-z0-9]{2,4})$|i", $filename, $fileSuffix);

        switch(strtolower($fileSuffix[1]))
        {
            case "js" :
                return "application/x-javascript";

            case "json" :
                return "application/json";

            case "jpg" :
            case "jpeg" :
            case "jpe" :
                return "image/jpg";

            case "png" :
            case "gif" :
            case "bmp" :
            case "tiff" :
                return "image/".strtolower($fileSuffix[1]);

            case "css" :
                return "text/css";

            case "xml" :
                return "application/xml";

            case "doc" :
            case "docx" :
                return "application/msword";

            case "xls" :
            case "xlt" :
            case "xlm" :
            case "xld" :
            case "xla" :
            case "xlc" :
            case "xlw" :
            case "xll" :
                return "application/vnd.ms-excel";

            case "ppt" :
            case "pps" :
                return "application/vnd.ms-powerpoint";

            case "rtf" :
                return "application/rtf";

            case "pdf" :
                return "application/pdf";

            case "html" :
            case "htm" :
            case "php" :
                return "text/html";

            case "txt" :
                return "text/plain";

            case "mpeg" :
            case "mpg" :
            case "mpe" :
                return "video/mpeg";

            case "mp3" :
                return "audio/mpeg3";

            case "wav" :
                return "audio/wav";

            case "aiff" :
            case "aif" :
                return "audio/aiff";

            case "avi" :
                return "video/msvideo";

            case "wmv" :
                return "video/x-ms-wmv";

            case "mov" :
                return "video/quicktime";

            case "zip" :
                return "application/zip";

            case "tar" :
                return "application/x-tar";

            case "swf" :
                return "application/x-shockwave-flash";

            default :
            if(function_exists("mime_content_type"))
            {
                $fileSuffix = mime_content_type($filename);
            }

            return "unknown/" . trim($fileSuffix[0], ".");
        }
    }
	
	/**
	 * Delete a given file
	 * @return void
	 */
	function deleteFile(){
		$file = isset($this->data['fileName']) ? $this->data['fileName'] : null;
		$path = $this->getFullPath($this->data['fileRoot']);
		
		if(!is_dir($path . $file) && is_file($path . $file)){
			if(unlink($path . $file)){				
				#check for a thumb
				$file_name 	= substr($file,0,strrpos($file,"."));
				$file_ext	= substr($file,strrpos($file,"."));
				
				if(is_file($path . $file_name."_thumb".$file_ext)){
					if(!unlink($path . $file_name."_thumb".$file_ext)){
						$this->output(array('message'=>$this->language['ErrorRemoveingThumb']));
					}
				}
			
				$this->output(array('message'=>"ok"));
			}
			else{
				$this->output(array('message'=>$this->language['ErrorRemoveingFile']));

			}
		}
		else{
			$this->output(array('message'=>$this->language['ErrorNoFile']." `".$path  .$file."`!"));
		}		
	}
	
	/**
	 * Delete a given directory
	 * @return void
	 */
	function deleteDirectory(){
		$path = $this->getFullPath($this->data['fileRoot']);
		
		if(is_dir($path)){		
			#empty the dir
			$this->removeDirectoryContentRecursive($path);
		
			if(@rmdir($path)){
				$this->output(array('message'=>"ok"));
			}
			else{
				$this->output(array('message'=>$this->language['ErrorRemovingDirectory']));
			}
		}
		else{
			$this->output(array('message'=>$this->language['ErrorNoDirectory']." `".$path."`!"));
		}		
	}
	
	/**
	 * Remove all the direcorys and files from the given directory recursivly
	 * @param string $path path to the directory
	 * @return null
	 */
	function removeDirectoryContentRecursive($path){
		if ($dh = opendir($path)) {
	        while (($file = readdir($dh)) !== false) {
	        	if($file != "." && $file != ".."){
	        		if(is_file($path . $file))
		           		unlink($path . $file);
					elseif(is_dir($path . $file)){
						$this->removeDirectoryContentRecursive($path . $file . DIRECTORY_SEPARATOR);
						rmdir($path . $file);
					}
	        	}	        	
	        }
	        closedir($dh);
	    }
	}
	
	/**
	 * Get the dir listning from the root
	 * @return void
	 */
	function getDirectoryList(){		
		$ndirs = array();
		$path  = $this->getFullPath(); 
		$dirs  = $this->readDirectoryList($path, 1);
		$root  = null;	
		
		$this->output(array("dirlist"=>$dirs));
		
		if(is_array($dirs)){		
			$nr = 0;
			foreach($dirs as $dir){			
				if(is_array($dir)){
					$ndirs[$nr-1] = array("0"=>$root,"1"=>$dir);
				}
				else{
					$root = $dir;
					$ndirs[$nr] = array("0"=>$root);
				}
				$nr++;
			}
			
			$this->output(array("dirlist"=>$ndirs));
		}
		else{
			$this->output(array("dirlist"=>array()));
		}
	}
	
	/**
	 * Reads dir list
	 * @param string $dir location of a dir
	 * @param int $hd current dir if 1 else a subdir
	 * @return array of dirs
	 */
	function readDirectoryList($dir, $hd = 1)
    {
		static $i = 0;
	   
     	static $h_dir;
      	$files = Array();
      
	 	if(false !== ($handle = @opendir($dir))){
	  		$h_dir = ($hd == 0) ? $h_dir : $dir;

			while (false !== ($file = readdir($handle)))
	       	{
		    	if ($file != "." && $file != ".." && $file != "" && $file{0} != "." && is_dir($dir . $file))
		    	{
		    		$val = $file;
	
		           	if (is_dir($dir . $file))
		          	{
				   		if($this->readDirectoryList($dir . $file . DIRECTORY_SEPARATOR, 0) != false)
		              	$val = array($file, $this->readDirectoryList($dir . $file . DIRECTORY_SEPARATOR, 0));
		           	}
					
				   	$files[] = $val;
		       	}
		   	}
		   	closedir($handle);
	
	       	return !empty($files) ? $files : false;
	  	}
	  	return false;       
    }
	
	/**
	 * Get the content of a dir
	 * @return void
	 */
	function getDirectoryContent(){
		$dircontent = array();
		
		$this->data['directoryRoot'] = str_replace('/',DIRECTORY_SEPARATOR,$this->data['directoryRoot']);
		$path = $this->getFullPath($this->data['directoryRoot']);
		
		# get a array of the files in this directory
		$files = $this->readDirectoryContent($path);		
		
		if(is_array($files)){
			foreach($files as $file){
				$dirName = (substr($this->data['directoryRoot'], 1) != '') ? substr($this->data['directoryRoot'], 1) . DIRECTORY_SEPARATOR : '';
				$dircontent[] = $this->readFilePropertys($dirName . $file);
			}							
		}
		
		$this->output(array("dircontent"=>$dircontent));	
	}
	
	/**
	 * Read the content of a dir (recursive!)
	 * @param string $dir location of the current directory
	 * @return array of files that are in the dir
	 */
	function readDirectoryContent($dir){
	   $i = 0;
       $files = Array();
     
       if(is_dir($dir)){
	       $handle = opendir($dir);
	
	       while (false !== ($file = readdir($handle)))
	       {
		       if ($file != "." && $file != ".." && $file != "" && $file{0} != "." && is_file($dir . $file) && strpos($file,"_thumb.") === false)
		       {
		       		# if we are in image mode make sure we are only displaying images!
      				if($this->data['browser'] == "images"){
		       	   		$parts = @getimagesize($dir . $file);
		       	   		if(in_array($parts['mime'], $this->settings['allowed_image_mime']))
		       	   			$files[$i++] = $file;
      				}
      				else{
      					$files[$i++] = $file;
      				}		          	
		       }
		   }
		   closedir($handle);	
	       return $files;
       }       
       return false;
	}
	
	/**
	 * Read the property of a file
	 * @param string $file location of the file 
	 * @return array of fileproperty's 
	 */
	function readFilePropertys($file){
		$name 		= basename($file);
		$short_name = (strlen($name) > 15 ) ? substr($name, 0, 12)."..." : $name;
		$pathinfo 	= pathinfo($this->getFullPath() .$file);		
		$thumb		= $pathinfo['filename'] . "_thumb." . $pathinfo['extension'];
		$type 		= $this->returnMIMEType($file);
		$fileSize 	= array_reduce ( array (" B", " KB", " MB"), create_function ( '$a,$b', 'return is_numeric($a)?($a>=1024?$a/1024:number_format($a,2).$b):$a;' ), filesize($this->getFullPath() . $file));
		$imageSize 	= "-";
		
		list($width, $height) = @getimagesize($this->getFullPath() . $file);

		if(isset($width) && isset($height)){						
			$imageSize 	= $width." x ".$height." px";
		}
		
		$dir = dirname($file) != '.' && dirname($file) != DIRECTORY_SEPARATOR ? dirname($file) . DIRECTORY_SEPARATOR : '';
		
		if(!is_file($this->getFullPath() . $dir . $thumb)){
			$thumb = 'img/fileBackground.jpg';
		}
		else{
			$dir 	= ($dir != '') ? str_replace(DIRECTORY_SEPARATOR, '/', $dir) . '/' : '';
			$thumb 	= $this->settings['upload_uri'] . $dir . $thumb;
		}
	
		return array("short_name"	=> $short_name,
					 "name"			=> $name,
					 "thumb"		=> $thumb,
					 "type"			=> $type,
					 "fileSize"		=> $fileSize,
					 "imageSize"	=> $imageSize,
					);
	}
	
	/**
	 * Return the full path to the upload directory
	 * @param string $subdir [optional] subdirectory to add to the path
	 * @return the full path to the upload directory
	 */
	function getFullPath($subdir = null){
		$path = '';
		# if root return uploadpath
		if($subdir == '/' || empty($subdir)){
			return $this->settings['upload_path'];
		}
		else{
			# strip first /
			$subdir = substr($subdir, 1);
			# replace / with directory seperator
			$subdir = str_replace('/', DIRECTORY_SEPARATOR, $subdir);
			return $this->settings['upload_path'] . $subdir . DIRECTORY_SEPARATOR;
		}
	}
	
	/**
	 * Send JSON output to the browser
	 * @param array $data data to encode 
	 * @return void
	 */
	function output($data){
		header('Content-type: application/x-json');
		die(json_encode($data));
	}
}

$Archiv = new Archiv;
?>