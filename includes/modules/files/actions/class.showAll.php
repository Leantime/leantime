<?php

class showAll extends files {
	
	public function run() {
			
		$tpl = new template();
		
		$currentModule = '';
		if (isset($_GET['id'])) 
			$currentModule = $_GET['id'];
		
		if (isset($_POST['upload'])) {
			if (isset($_FILES['file'])) {
				
				$this->upload($_FILES, 'private', 0);
				$tpl->setNotification('FILE_UPLOADED', 'success');
			} else {
					
				$tpl->setNotification('NO_FILES', 'error');
			}
		}
		
		$tpl->assign('folders', $this->getFolders($currentModule));
		$tpl->assign('currentModule', $currentModule);
		$tpl->assign('modules',$this->getModules($_SESSION['userdata']['id']));
		$tpl->assign('imgExtensions', array('jpg','jpeg','png','gif','psd','bmp','tif','thm','yuv'));
		$tpl->assign('files', $this->getFilesByModule($currentModule, NULL, $_SESSION['userdata']['id']));
		$tpl->display('files.showAll');
	}
	
}

?>
