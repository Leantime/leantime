<?php

class editLead extends leads {
	
	public function run() {
	
		$tpl = new template();
		$id = (int)$_GET['id'];
		
		if ($id > 0) {
			
			$lead = $this->getLead($id);
			$values = array(
				'name' 			=> $lead['name'],
				'potentialMoney'=> $lead['potentialMoney'],
				'actualMoney'	=> $lead['actualMoney'],
				'refSource' 	=> $lead['refSource'],
				'refValue' 		=> $lead['refValue'],
				'status' 		=> $lead['status'],
				'proposal'		=> $lead['proposal']
			);
			
			$clients = new clients();
			$dbClient = $clients->getClient($lead['clientId']);
			$client = array(
					'name'		=> $dbClient['name'],
					'street' 	=> $dbClient['street'],
					'zip' 		=> $dbClient['zip'],
					'city' 		=> $dbClient['city'],
					'state' 	=> $dbClient['state'],
					'country'	=> $dbClient['country'], 
					'phone' 	=> $dbClient['phone'],
					'email' 	=> $dbClient['email'],
					'internet' 	=> $dbClient['internet'],		
			);

			if (isset($_POST['save'])) {	
				if (isset($_FILES['file'])) {
					if (htmlspecialchars($_FILES['file']['name']) !== '' ) {
						
						$file = new files();
						$file->upload($_FILES, 'lead', $id);
					} 
				} 
				
				if (isset($_POST['name']) && isset($_POST['referralSource']) && isset($_POST['money']) && isset($_POST['status'])) {
				
					$refValue = '';
					if($_POST['referralSource'] && $_POST['referralValueOther'] != '') {
						$refValue = $_POST['referralValueOther'];
					} else if ($_POST['referralSource'] == 5 && $_POST['referralValueClient'] > 0) {
						$refValue = $_POST['referralValueClient'];
					}
					
					$values = array(
						'name' 			=> $_POST['name'],
						'potentialMoney'=> $_POST['money'],
						'actualMoney'	=> $_POST['actualMoney'],
						'refSource' 	=> $_POST['referralSource'],
						'refValue' 		=> $refValue,
						'status' 		=> $_POST['status']
					);
					
					$client = array(
						'name'		=> $_POST['clientName'],
						'street' 	=> $_POST['street'],
						'zip' 		=> $_POST['zip'],
						'city' 		=> $_POST['city'],
						'state' 	=> $_POST['state'],
						'country'	=> $_POST['country'], 
						'phone' 	=> $_POST['phone'],
						'email' 	=> $_POST['email'],
						'internet' 	=> $_POST['internet']	
					);
					
					$this->editLead($values, $id);	
					$clients->editClient($client, $lead['clientId']);					
					$tpl->setNotification('EDIT_SUCCESS', 'success');
				} else {
					
					$tpl->setNotification('MISSING_FIELDS', 'error');
				}
			}

			$tpl->assign('client',$client);
			$tpl->assign('lead',$values);			
		} else {
			
			$tpl->display('general.error');
		}
		
		$client = new clients();
		$tpl->assign('status', $this->getStatus());
		$tpl->assign('referralSources',$this->getReferralSources());
		$tpl->assign('clients', $client->getAll());
		$tpl->display('leads.editLead');
	
	}
	
}

?>
