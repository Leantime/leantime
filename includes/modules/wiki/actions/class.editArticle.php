<?php

/**
 * editArticle Class - Edit an article
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @package modules
 * @subpackage wiki
 * @license	GNU/GPL, see license.txt
 *
 */

class editArticle extends wiki{

	/**
	 * run - display template and edit data
	 *
	 * @access public
	 *
	 */
	public function run() {

		$tpl 		= new template();

		$user 		= new contacts();
		$helper 	= new helper();

		$msgKey = '';

		if(isset($_GET['id']) === true){
			$id = (int)($_GET['id']);
		}

		$row = $this->getArticle($id);

		if($row['authorId'] == $_SESSION['userdata']['id'] || $_SESSION['userdata']['role'] == 'admin'){
				
			$values = array(
					'id'			=>$row['id'],
					'headline'		=>$row['headline'],
					'text'			=>$row['text'],
					'category'		=>$row['category'],
					'tags'			=>$row['tags'],
					'authorId'		=>$row['authorId'],
					'date'			=>$helper->timestamp2date($row['date'], 2),
					
			);

			if(!empty($row) && $values['headline'] !== null){

				if(isset($_POST['save'])){


					$values = array(
							'id'			=>$id,
							'headline'		=>($_POST['headline']),
							'text'			=>($_POST['text']),
							'category'		=>($_POST['category']),
							'tags'			=>($_POST['tags']),
							'date'			=>($helper->timestamp2date(date("Y-m-d H:i:s"),2))
					);
						
					if($values['headline'] === '') {

						$msgKey = "ERROR_NO_HEADLINE";

					}elseif($values['text'] === ''){

						$msgKey = "ERROR_NO_DESCRIPTION";
							
					}else{

						//Prepare dates for db
						$values['date'] 		= $helper->timestamp2date($values['date'], 4);
						
						//Update Ticket
						$this->updateArticle($values, $id);
							
						//Take the old value to avoid nl character
						$values['text'] 	= $_POST['text'];

						$values['date'] 		= $helper->timestamp2date($values['date'], 2);
						
						$msgKey = "ARTICLE_EDITED";
							
						
						
					}

				}

				//File upload
				if(isset($_POST['upload'])){


					if(htmlspecialchars($_FILES['file']['name']) !== '' ){
							
						$upload = new fileupload();
						$upload->initFile($_FILES['file']);

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

								$msgKey = 'FILE_UPLOADED';

							}else{

								$msgKey = 'ERROR_FILEUPLOAD_'.$upload->error.'';
							}

						}else{
								
							$msgKey = 'ERROR_FILEUPLOAD_'.$upload->error.'';
								
						}

					}else{
							
						$msgKey = 'NO_FILE';

					}

				}
					
				$tpl->assign('role', $_SESSION['userdata']['role']);
				$tpl->assign('files', $this->getFiles($id));
				$tpl->assign('info', $msgKey);
				$tpl->assign('categories', $this->getCategories());
				$tpl->assign('values', $values);
				$tpl->assign('helper', $helper);

				$tpl->display('wiki.editArticle');
					
			}else{

				$tpl->display('general.error');
					
			}

		}else{

			$tpl->display('general.error');

		}

	}

}

?>