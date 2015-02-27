<?php

/**
 * newCategory Class - Delete tickets
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @package modules
 * @subpackage tickets
 * @license	GNU/GPL, see license.txt
 *
 */

class newCategory extends wiki{

	/**
	 * run - display template and edit data
	 *
	 * @access public
	 *
	 */
	public function run() {

		$tpl = new template();

		$msgKey = '';
		 $catName = '';
		if(isset($_POST['save'])){
					
			if(isset($_POST['catName']) === true && $_POST['catName'] != ''){

				$catName = ($_POST['catName']);
				
			}
			
			$this->addCategory($catName);
			
			$msgKey = 'CATEGORY_ADDED';
					
		}
		$tpl->assign('catName', $catName);
		$tpl->assign('info', $msgKey);
		
				
		$tpl->display('wiki.addCategory');
			
	}

}

?>