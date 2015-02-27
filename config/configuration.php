<?php						
	class config
							{
							/* General */
								public $sitename = "";
								public $language = "en";
								
								/* Database */
								public $dbHost="localhost";

								public $dbUser="root"; 
								public $dbPassword=''; 

								public $dbDatabase="ticketsystem"; 
								
								/* Fileupload */
								public $userFilePath= "userdata/";
								public $maxFileSize = "10000";
								
								/* Sessions */
								public $sessionpassword = "53b37easdfasdfasdf8d6e";
								
								/* Email */
								public $email = "support@leantime.com";
								
								/* Admin */
								public $adminUserName = 'admin';
								public $adminUserPassword = 'test';
								public $adminFirstname = 'Admin';
								public $adminLastname = 'Admin';
								public $adminEmail = 'superadmin';
								
								/* Company Styles*/
								public $mainColor = "1b75bb";
								public $logoPath = "/includes/templates/zypro/images/leantime-blueBg.png";
							
							}
?>
