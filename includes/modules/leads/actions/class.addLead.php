<?php

class addLead extends leads {
	
	public function run() {
		
		$tpl = new template();
		$language = new language();
		$language->setModule('leads');
		$language->readIni();
		
		if(isset($_POST['save'])) {
			if(isset($_POST['name']) && isset($_POST['money']) && isset($_POST['referralSource'])) {
				
				$refValue = '';
				if ($_POST['referralValueOther'] != '') {
					
					$refValue = $_POST['referralValueOther'];
				} else if ($_POST['referralSource'] == 5 && $_POST['referralValueClient'] > 0) {
					
					$refValue = $_POST['referralValueClient'];
				}
				
				$values = array(
					'name' 		=> $_POST['name'],
					'refSource' => $_POST['referralSource'],
					'refValue' 	=> $refValue,
					'potentialMoney' => $_POST['money'],
					'creatorId'	=> $_SESSION['userdata']['id']
				);
				
				$contact = array(
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
				
				if ($this->isLead($values['name']) !== true) {
						
					$leadId = $this->addLead($values);
					$this->addLeadContact($contact, $leadId);
					$tpl->setNotification('EDIT_SUCCESS', 'success');
				} else {
					
					$tpl->setNotification('LEAD_EXISTS', 'error');
				}
				
			} else {
				
				$tpl->setNotification('MISSING_FIELDS', 'error');
			}
		}
		
		$client = new clients();
		$tpl->assign('referralSources',$this->getReferralSources());
		$tpl->assign('clients', $client->getAll());
		$tpl->display('leads.addLead');
		
	}
	
}

?>
