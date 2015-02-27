<?php
/**
 * Archiv settings file
 * 
 * @version 0.1
 * @author Wouter van Kuipers Archiv@pwnd.nl
 * @copyright 2008-2009 pwnd.nl
 * @license LGPL
 * @see http://archiv.pwnd.nl
 *
 */ 

# Error reporting
error_reporting(-1);

# Main settings vars
$s = array(
	# full path to upload files to and show files from
	'upload_path'			=> $_SERVER['DOCUMENT_ROOT'] . '/userdata/images/',
	# full URI to upload directory
	'upload_uri'			=> 'http://' . $_SERVER['HTTP_HOST'] .'/userdata/images/',
	# Selectable file types, seperated by a ; (* for all) example *.txt;*.doc;*.pdf
	'selectable_files'		=> '*',
	# Selectable image types, seperated by a ; (* for all) example *.gif;*.png;*.jpg
	'selectable_images'		=> '*',
	# Limit of the maximal file size a single file can have (in kB)
	'size_limit'			=> '0',
	# Maximal files that can be uploaded in a single upload run
	'upload_limit'			=> '0',
	# Enables debug mode
	'debug'					=> false,
	# Maximal image width/ height (in px)
	'max_image_size'		=> '1000',
	# Maximal image thumb width/ height (in px)
	'max_image_thumb_size'	=> '100',
	# Allowed file mime types
	'allowed_file_mime'		=> array('application/x-javascript',
										'application/json',
										'image/jpg',
										'image/png',
										'image/gif',
										'image/bmp',
										'image/tiff'),
	# Allowed image mime types
	'allowed_image_mime'	=> array('image/jpg', 					
										'image/jpeg', 
										'image/png', 
										'image/gif'),
	# Language to run the plugin in
	'language'				=> 'de'	
);
?>