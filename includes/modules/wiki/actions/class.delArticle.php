<?php

/**
 * delTicket Class - Delete tickets
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @package modules
 * @subpackage tickets
 * @license	GNU/GPL, see license.txt
 *
 */

class delArticle extends wiki{

	/**
	 * run - display template and edit data
	 *
	 * @access public
	 *
	 */
	public function run() {

		$tpl = new template();

		//Only admins
		if($_SESSION['userdata']['role'] == 'admin') {

			if(isset($_GET['id'])){
				$id = (int)($_GET['id']);
			}
				
			$msgKey = '';

			if(isset($_POST['del'])){
					
				$this->deleteAllFiles($id);

				$this->delArticle($id);

				$msgKey = 'ARTICLE_DELETED';
					
			}

			$tpl->assign('info', $msgKey);
			$tpl->assign('article', $this->getArticle($id));
				
			$tpl->display('wiki.delArticle');
				
		}else{
				
			$tpl->display('general.error');

		}
			
	}

}

?>