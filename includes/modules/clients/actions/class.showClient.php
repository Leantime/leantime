<?php

/**
 * showClient Class - Show one client
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @package modules
 * @subpackage clients
 * @license	GNU/GPL, see license.txt
 *
 */

class showClient extends clients{

	/**
	 * run - display template and edit data
	 *
	 * @access public
	 */
	public function run() {

		$tpl = new template();

		$id = '';
				
		if (isset($_GET['id']) === true)
			$id = (int)($_GET['id']);

		$client = $this->getClient($id);

		if (empty($client) === false){

			$file = new files();
			$project = new projects();

			$msgKey = '';
			if($_SESSION['userdata']['role'] == 'admin')
				$tpl->assign('admin', true);

			if (isset($_POST['upload'])) 
				if (isset($_FILES['file'])) 						
					$msgKey = $file->upload($_FILES,'client',$id);		

			$comment = new comments();

			//Add comment
			if(isset($_POST['comment']) === true){

				$mail = new mailer();
				$values = array(
					'text'		=> $_POST['text'],
					'date' 		=> date("Y-m-d H:i:s"),
					'userId' 	=> ($_SESSION['userdata']['id']),
					'moduleId' 	=> $id,
					'commentParent' => ($_POST['father'])
				);

				$comment->addComment($values, 'client');
			}

			$tpl->assign('userClients', $this->getClientsUsers($id));
			$tpl->assign('comments', $comment->getComments('client', $id));
			$tpl->assign('imgExtensions', array('jpg','jpeg','png','gif','psd','bmp','tif','thm','yuv'));
			$tpl->assign('info', $msgKey);
			$tpl->assign('client', $client);
			$tpl->assign('clientProjects', $project->getClientProjects($id));
			$tpl->assign('files', $file->getFilesByModule('client'));
			//var_dump($file->getFilesByModule('client')); die();

			$tpl->display('clients.showClient');

		} else {

			$tpl->display('general.error');

		}

			
	}

}
?>