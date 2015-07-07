<?php

class headMenu {

	public function run() {
		
		$login = new login(session::getSID());
		
		if($login->logged_in()===true){
		
			$tpl = new template();
			
			
			//Messages
			$message = new messages();
			$messages = $message->getInbox($_SESSION['userdata']['id'], 5, 0);
			
			$messageCount = count($messages);
				
	        if ($messageCount) {
	       		$mailOptions = array('class'=>'dropdown-toggle', 'data-toggle'=>'dropdown', 'href'=>'#');
			} else {
				$mailOptions = array('class'=>'dropdown-toggle');
			}
			
			$tpl->assign("mailOptions", $mailOptions);
			$tpl->assign("messagecount", $messageCount);	
			$tpl->assign("messages", $messages);
			
			$tpl->assign("helper", new helper());
			
			//Tickets
			$tickets = new tickets();
			$newTickets = $tickets->getUnreadTickets($_SESSION['userdata']['id']);
			$ticketCount = count($newTickets);

			if (count($newTickets)) {
				$ticketOptions = array('class' => 'dropdown-toggle', 'data-toggle' => 'dropdown', 'data-target' => '#');
			} else {
				$ticketOptions = array('class' => 'dropdown-toggle');
			}
			
			if(is_array($newTickets) === true){
				$limitedTicketItems = array_slice($newTickets, 0, 5);
			}else{
				$limitedTicketItems = array();
			}
			
			$tpl->assign("tickeOptions", $ticketOptions);
			$tpl->assign("ticketCount", $ticketCount);
			$tpl->assign("newTickets", $limitedTicketItems);

			$tpl->display("general.headMenu");
			
			
		}
	}
}