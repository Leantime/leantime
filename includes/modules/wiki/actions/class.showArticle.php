<?php

/**
 * showArticle Class - shwo single Article
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @package modules
 * @subpackage tickets
 * @license	GNU/GPL, see license.txt
 *
 */

class showArticle extends wiki{

	/**
	 * run - display template and edit data
	 *
	 * @access public
	 *
	 */

	public function run() {

		$tpl = new template();

		$msgKey = '';
		if(isset($_GET['id']) === true){

			$id = (int)($_GET['id']);

			$article = $this->getArticle($id);
			$editable = true;
				
			if(!empty($article)){

				$helper = new helper();

				//Add comment
				if(isset($_POST['comment']) === true){

					$values = array(
						'text'		=> ($_POST['text']),
						'date' 		=> date("Y-m-d H:i:s"),
						'userId' 	=> ($_SESSION['userdata']['id']),
						'ticketId' 	=> $id,
						'commentParent' => ($_POST['father'])
					);

					$this->addComment($values);

					$msgKey = 'COMMENT_ADDED';
						

				}



				//Only admins
				if($_SESSION['userdata']['role'] == 'admin') {
						
					
						
					//Delete file
					if(isset($_GET['delFile']) === true){

						$file = $_GET['delFile'];

						$upload = new fileupload();

						$upload->initFile($file);

						//Delete file from server
						$upload->deleteFile($file);

						//Delete file from db
						$this->deleteFile($file);

						$msgKey = 'FILE_DELETED';

					}

					//Delete comment
					if(isset($_GET['delComment']) === true){
							
						$commentId = (int)($_GET['delComment']);

						$this->deleteComment($commentId);

						$msgKey = 'COMMENT_DELETED';
							
					}

				}



				$tpl->assign('info', $msgKey);
				$tpl->assign('role', $_SESSION['userdata']['role']);
				$tpl->assign('article', $article);
				$tpl->assign('objTicket', $this);
				

				$comments = $this->getComments($article['id']);

				$tpl->assign('numComments', count($comments));

				$tpl->assign('comments', $comments);

				$tpl->assign('editable', $editable);
				
				$files = $this->getFiles($article['id']);

				$tpl->assign('files', $files);
				$tpl->assign('numFiles', count($files));


				$tpl->assign('helper', $helper);

				$tpl->display('wiki.showArticle');
					
			}else{

				$tpl->display('general.error');

			}

		}else{
				
			$tpl->display('general.error');

		}

	}

}

?>