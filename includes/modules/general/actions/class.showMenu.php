<?php

/**
 * menu Class displays the menu
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @package modules
 * @subpackage general
 * @license	GNU/GPL, see license.txt
 *
 */

class showMenu {

	/**
	 * run - display template and edit data
	 *
	 * @access public
	 *
	 */
	public function run() {

		$tpl = new template();

		$tpl->display('general.showMenu');

	}

}
?>