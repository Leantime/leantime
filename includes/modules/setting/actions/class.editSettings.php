<?php

class editSettings{

	public function run() {

		$tpl = new template();

		$file = 'config/configuration.php';

		if(file_exists($file) === true){
			
		
			if(substr(decoct( fileperms($file) ), 2) != 777){
	
				$tpl->assign('info', 'configuration.php nicht beschreibbar');
	
			}
	
			
	
				$config = new config();
	
				$values = array(
					'sitename' 		=> $config->sitename,
					'dbHost' 		=> $config->dbHost,
					'dbUser' 		=> $config->dbUser,
					'dbDatabase' 	=> $config->dbDatabase,
					'dbPassword' 	=> $config->dbPassword,
					'userFilePath' 	=> $config->userFilePath,
					'maxFileSize'	=> $config->maxFileSize,
					'email'			=> $config->email,
					'adminUserName'			=> $config->adminUserName,
					'adminUserPassword'			=> $config->adminUserPassword,
					'adminFirstname'			=> $config->adminFirstname,
					'adminLastname'			=> $config->adminLastname,
					'adminEmail'			=> $config->adminEmail,
					'sessionpassword' => $config->sessionpassword
				);
	
				if(isset($_POST['save'])){
						
					$fileContent = '<?php
							class config
							{
								/* General */
								public $sitename = "'.htmlspecialchars($_POST['sitename']).'";
								/* Database */
								public $dbHost="'.htmlspecialchars($_POST['host']).'";
								public $dbUser="'.htmlspecialchars($_POST['username']).'";
								public $dbPassword="'.htmlspecialchars($_POST['password']).'";
								public $dbDatabase="'.htmlspecialchars($_POST['database']).'";
								
								public $adminUserName = "'.htmlspecialchars($_POST['adminUserName']).'";
								public $adminUserPassword = "'.htmlspecialchars($_POST['adminUserPassword']).'";
								public $adminFirstname = "'.htmlspecialchars($_POST['adminFirstname']).'";
								public $adminLastname = "'.htmlspecialchars($_POST['adminLastname']).'";
								public $adminEmail = "'.htmlspecialchars($_POST['adminEmail']).'";
								
								/* Fileupload */
								public $userFilePath="'.htmlspecialchars($_POST['path']).'";
								public $maxFileSize = "'.htmlspecialchars($_POST['filesize']).'";
								
								/* Sessions */
								public $sessionpassword = "'.$values['sessionpassword'].'";
								
								/* Email */
								public $email = "'.htmlspecialchars($_POST['email']).'";
							
							}
							?>';	
	
					if(substr(decoct( fileperms($file) ), 2) == 777){
	
						$fp = fopen($file,"w+");
							
						fputs($fp, $fileContent);
							
						fclose ($fp);
	
						$values = array(
							'sitename'=> htmlspecialchars($_POST['sitename']),
							'dbHost' => htmlspecialchars($_POST['host']),
							'dbUser' =>htmlspecialchars($_POST['username']),
							'dbPassword' => htmlspecialchars($_POST['password']),
							'dbDatabase' => htmlspecialchars($_POST['database']),
							'userFilePath' =>htmlspecialchars($_POST['path']),
							'maxFileSize'	=> htmlspecialchars($_POST['filesize']),
							'email'			=> htmlspecialchars($_POST['email']),
							'adminUserName'			=> htmlspecialchars($_POST['adminUserName']),
							'adminUserPassword'			=> htmlspecialchars($_POST['adminUserPassword']),
							'adminFirstname'			=> htmlspecialchars($_POST['adminFirstname']),
							'adminLastname'			=> htmlspecialchars($_POST['adminLastname']),
							'adminEmail'			=> htmlspecialchars($_POST['adminEmail']),
							'sessionpassword' => $values['sessionpassword']
						);
							
						$tpl->assign('info', 'Einstellungen gespeichert');
							
					}else{
							
						$tpl->assign('info', 'configuration.php nicht beschreibbar');
	
					}
	
				}
	
				$tpl->assign('values', $values);
	
				$tpl->display('setting.editSettings');
			}else{
				$tpl->display('general.error');	
			}
		
			
	}

}
?>