<?php

/**
 * editAccounts Class - Edit account
 *
 * @author David Bergeron <david@rpimaging.com>
 * @version 1.0
 * @package modules
 * @subpackage projects
 * @license	GNU/GPL, see license.txt
 *
 */

class editAccounts extends accounts {
	
	/**
	 * run - display template and edit data
	 *
	 * @access public
	 *
	 */
	public function run() {

		$tpl = new template();

		if(isset($_SESSION['userdata']['role'])) {
		
			if($_SESSION['userdata']['role'] == 'admin' || $_SESSION['userdata']['role'] == 'employee') {
		
				$id = $_SESSION['userdata']['id'];
			
				$row = $this->getUser($id);
			
				$infoKey = '';
					
				$values = array(
					'lastname'	=>$row['lastname'],
					'firstname'	=>$row['firstname'],
					'phone'		=>$row['phone'],
					'user'		=>$row['user']
				);					
					
				if(isset($_POST['save'])) {
											
					$hasher = new PasswordHash(8,TRUE);
					$mail = new mailer();
								
					$newPassword 	 = $_POST['newPassword'];
					$confirmPassword = $_POST['confirmPassword'];
					$currentPassword = $_POST['currentPassword'];
					
					$values = array(
							'lastname'	=> ($_POST['lastname']),
							'firstname'	=> ($_POST['firstname']),
							'phone'		=> ($_POST['phone']),
							'user'		=> ($_POST['user'])
					);
					
					if($hasher->CheckPassword($currentPassword, $row['password'])){	
				 		if($newPassword===$confirmPassword){				  
			
			
								$newPassword = $hasher->HashPassword($newPassword);
								$this->changePassword($newPassword,$id);
								$infoKey='Your password has been updated.';
								
								$email = $values['user'];
								
								$to[] = $email;
		
								$subject = "Leantime: Account Update";
		
								$mail->setSubject($subject); 
		
								$emailMsg = "Hello! Your password has been changed successfully.
					
									If you have not authorized these changes please contact an administrator immediately.  
									Please do not waste your time replying to the bot.  
					
									Thanks!";
									
					
								$mail->setText($emailMsg);
		
								$mail->sendMail($to);

						}else{
							$infoKey="Passwords do not match";
						}
					}else{		

						$this->editAccount($values, $id);
						$infoKey='Your account has been update.';
						
						$email = $values['user'];
						
						$to[] = $email;
		
						$subject = "Leantime: Account Update";
		
						$mail->setSubject($subject); 
		
						$emailMsg = "Hello! Your account has been updated:
						
						
							Name: ".$values['firstname']." ".$values['lastname']."
					
							Email: ".$values['user']."
									
							Phone: ".$values['phone']."
					
							If you have not authorized these changes contact an admin ASAP.  
							Please do not waste your time replying to the bot.  
					
							Thanks!";
									
						$mail->setText($emailMsg);
		
						$mail->sendMail($to);
						

					}
			
					$tpl->assign('info', $infoKey);
					$tpl->assign('values', $values);
				}
			
				$tpl->assign('account', $this->viewAccount($id));	
			
				$tpl->display('accounts.editAccounts');
					
	 		}else{
				
				$tpl->display('general.error');
			}
		}
	}

}

?>