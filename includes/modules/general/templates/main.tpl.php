<?php 
	
//Get Application
$config = new config(); 
$setting = new setting();
$siteName = $config->sitename;

$main->includeAction('general.header');
		
$loginInfo = "";
$sitelogo = "";
$headerOutput = ob_get_clean();
ob_start();
		
if($login->logged_in()===true){
			 
	$user = new users();
	$tpl = new template();
				
	$loginInfo = 
				'<img src="'.$user->getProfilePicture($_SESSION['userdata']['id']).'" alt="" />
				<div class="userinfo">
				 	<h5>'.$_SESSION['userdata']['name'].' <small>- '.$_SESSION['userdata']['mail'].'</small></h5>
					<ul>
						<li><a href="/index.php?act=users.editOwn">Edit Profile</a></li>
                        <li><a href="/index.php?logout=1">Logout</a></li>
                 	</ul>
                 </div>';
				
				$message = new messages();
				$messages = $message->getInbox($_SESSION['userdata']['id'], 5, 0);
				
	            if (count($messages)) {
	            	$mailOptions = array('class'=>'dropdown-toggle', 'data-toggle'=>'dropdown', 'href'=>'#');
				} else {
					$mailOptions = array('class'=>'dropdown-toggle');
				}
								
	            $mailHead = '<span class="count">'.count($messages).'</span>
	                        <span class="head-icon head-message"></span>
	                        <span class="headmenu-label">Messages</span>';
							
				$mail = $tpl->displayLink('messages.showAll', $mailHead, NULL, $mailOptions);
							
                $mail .= '<ul class="dropdown-menu">
                        	<li class="nav-header">Messages</li>';
							if(count($messages)) {
	                        	foreach ($messages as $msg) { 
	                        		$mail .= '<li><a href="/index.php?act=messages.showAll&id='.$msg["id"].'"><span class="icon-envelope"></span> New message from <strong>'.$msg["firstname"].'</strong></a></li>';
	                        	}
                        	} 
                        
                 $mail .= '<li>'.$tpl->displayLink('messages.showAll', 'Show more messages').'</li>
                 		</ul>';		
				
				$tickets = new tickets();
				$newTickets = $tickets->getUnreadTickets($_SESSION['userdata']['id'],5);
				$count = count($tickets->getUnreadTickets($_SESSION['userdata']['id']));

				if (count($newTickets)) {
					$ticketOptions = array('class' => 'dropdown-toggle', 'data-toggle' => 'dropdown', 'data-target' => '#');
				} else {
					$ticketOptions = array('class' => 'dropdown-toggle');
				}

				$ticketHead .= '<span class="count" id="ticketCount">'.$count.'</span>
	                    <span class="head-icon iconfa-pushpin"></span>
	                    <span class="headmenu-label">New Tickets</span>';
						
				$ticket = $tpl->displayLink('tickets.showAll', $ticketHead, NULL, $ticketOptions);

				$ticket .= '
                    <ul class="dropdown-menu newusers">
                        <li class="nav-header">New Tickets</li>';
				$helper = new helper();		
                foreach($newTickets as $tick) {
                	$name = '<strong>'.$tick['headline'].'</strong><small>Created on: '.
                			$helper->timestamp2date($tick['date'], 2)
                		.'</small>';
                	$ticket .= '
                		<li>
                			'.
                			$tpl->displayLink('tickets.showTicket', $name ,array('id'=>$tick['id']))
							.'
						</li>
                	';
                }
				$ticket .= '<li>'.$tpl->displayLink('tickets.showAll', 'Show more tickets').'</li>';
               	$ticket .= '</ul>';
												
				if($_SESSION['userdata']['role'] != "user") {
				$statistics .= "<a  href='/timesheets/showMy'>
								<span class='head-icon iconfa-table'></span>
								<span class='headmenu-label'>Timesheet</span>							
							</a>";
							}else{
								$statistics .= "";
							}
					
				$dashboard = "<a href='/dashboard/show'>
								<span class='head-icon iconfa-th-large'></span>
								<span class='headmenu-label'>Dashboard</span>
							</a>";
							
				$calendar = "<a  href='/calendar/showMyCalendar'>
								<span class='head-icon iconfa-calendar'></span>
								<span class='headmenu-label'>Calendar</span>							
							</a>";
					
		}


$title = 'Support System';

	$publicMenuContent = ob_get_clean();
	ob_start();
		
	if ($login->logged_in()===true){
		
		$main->includeAction('general.menu');
		$menuContent = ob_get_clean();
		ob_start();
		
	} else {
				
		$menuContent = '';
	}
	


//Build Main Content
if($login->logged_in()===true){
	
	$main->run();
	$mainContent = ob_get_clean();
	
}else {
		
	$main->run('general.login'); 
	$mainContent = ob_get_clean();
	
}
	

ob_start();

//Get info text
$infoText = "";
$infoText = $login->error;

//Build footer and replace template placeholders

	$main->includeAction('general.footer');
	$footerContent = ob_get_clean();
	ob_start();


	if($login->logged_in()===false){
		$content = file_get_contents('includes/templates/'.TEMPLATE.'/login.php');
	}else{
		$content = file_get_contents('includes/templates/'.TEMPLATE.'/content.php');
	}
	
	
	if($login->logged_in()===true){
		
		//Replace Placeholder with Content
		$content = str_replace('<!--###HEADER###-->', $headerOutput, $content);
		$content = str_replace('<!--###TITLE###-->', $title, $content);
		$content = str_replace('<!--###DASHBOARD###-->', $dashboard, $content);
		$content = str_replace('<!--###TICKETS###-->', $ticket, $content);
		$content = str_replace('<!--###MAIL###-->', $mail, $content);
		$content = str_replace('<!--###CALENDAR###-->', $calendar, $content);
		
		$content = str_replace('<!--###STATISTICS###-->', $statistics, $content);
		$content = str_replace('<!--###LOGININFO###-->', $loginInfo, $content);
		$content = str_replace('<!--###SITENAME###-->', $siteName, $content);
		$content = str_replace('<!--###SITELOGO###-->', $sitelogo, $content);
		$content = str_replace('<!--###MENU###-->', $menuContent, $content);
		$content = str_replace('<!--###PUBLICMENU###-->', $publicMenuContent, $content);
		$content = str_replace('<!--###CONTENT###-->', $mainContent, $content);
		$content = str_replace('<!--###LOGINBOX###-->', '', $content);
		
		$content = str_replace('<!--###FOOTER###-->', $footerContent, $content);

		$content = str_replace('<!--###MAINCOLOR###-->', $config->mainColor, $content);
		$content = str_replace('<!--###LOGOPATH###-->', $config->logoPath, $content);
	
	}else{
		
		//Replace Placeholder with Content
		$content = str_replace('<!--###HEADER###-->', $headerOutput, $content);
		$content = str_replace('<!--###TITLE###-->', $title, $content);
		$content = str_replace('<!--###LOGININFO###-->', $loginInfo, $content);
		$content = str_replace('<!--###SITENAME###-->', $siteName, $content);
		$content = str_replace('<!--###SITELOGO###-->', $sitelogo, $content);
		$content = str_replace('<!--###MENU###-->', $menuContent, $content);
		$content = str_replace('<!--###PUBLICMENU###-->', $publicMenuContent, $content);
		$content = str_replace('<!--###CONTENT###-->', $mainContent, $content);
		$content = str_replace('<!--###LOGINBOX###-->', $loginContent, $content);
		$content = str_replace('<!--###INFO###-->', $infoText, $content);
		$content = str_replace('<!--###FOOTER###-->', $footerContent, $content);
		$content = str_replace('<!--###MAINCOLOR###-->', $config->mainColor, $content);
		$content = str_replace('<!--###LOGOPATH###-->', $config->logoPath, $content);
		
	}	

	//echo Final Content
	echo $content;
	
	
?>