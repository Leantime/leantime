<?php

/**
 * newArticle Class - Add a new article
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @package modules
 * @subpackage tickets
 * @license	GNU/GPL, see license.txt
 *
 */

class newArticle extends wiki{

	/**
	 * run - display template and edit data
	 *
	 * @access public
	 *
	 */
	public function run() {

		$tpl 		= new template();

		$helper		= new helper();

		$files		= array();
		$msgKey		= '';
		
		$values = array(
							
				'headline'		=>'',
				'text'			=>'',
				'category'		=>'',
				'tags'			=>'',
				'date'			=>'',
				'authorId'		=>''
				);
		
		if(isset($_POST['save']) === true){
			
				$values = array(
							
							'headline'		=>($_POST['headline']),
							'text'			=>($_POST['text']),
							'category'		=>($_POST['category']),
							'tags'			=>($_POST['tags']),
							'date'			=>($helper->timestamp2date(date("Y-m-d H:i:s"),2)),
							'authorId'		=>$_SESSION['userdata']['id']
					);
						
					if($values['headline'] === '') {

						$msgKey = "ERROR_NO_HEADLINE";

					}elseif($values['text'] === ''){

						$msgKey = "ERROR_NO_DESCRIPTION";
							
					}else{

						//Prepare dates for db
						$values['date'] 		= $helper->timestamp2date($values['date'], 4);
						
						//Update Ticket
						$this->addArticle($values);
							
						//Take the old value to avoid nl character
						$values['text'] 	= $_POST['text'];

						$values['date'] 		= $helper->timestamp2date($values['date'], 2);
						
						$msgKey = "ARTICLE_EDITED";
							
						
						
					}

				

					if(htmlspecialchars($_FILES['file']['name']) !== '' ){
							
						$upload = new fileupload();
						$upload->initFile($_FILES['file']);
						
						//Get the id of last insert
						$id = mysql_insert_id();
						
						$tpl->assign('info', $upload->error);

						if($upload->error == '') {

							//Hashname on server for securty reasons
							$newname = md5($id.time());

							$upload->renameFile($newname);

							if($upload->upload() === true){

								$fileValues = array(
										'encName'		=>($upload->file_name),
										'realName'		=>($upload->real_name),
										'date'			=>date("Y-m-d H:i:s"),
										'articleId'		=>($id),
										'userId'		=>($_SESSION['userdata']['id'])
								);

								$this->addFile($fileValues);

								$files = $this->getFiles($id);

							}else{

								$msgKey = 'ERROR_FILEUPLOAD_'.$upload->error.'';
							}

						}else{
								
							$msgKey = 'ERROR_FILEUPLOAD_'.$upload->error.'';
								
						}

					}

				}
					
				$tpl->assign('role', $_SESSION['userdata']['role']);
				$tpl->assign('files', $files);
				$tpl->assign('info', $msgKey);
				$tpl->assign('categories', $this->getCategories());
				$tpl->assign('values', $values);
				$tpl->assign('helper', $helper);

				$tpl->display('wiki.newArticle');
	}

}
?>