<?php defined( 'RESTRICTED' ) or die( 'Restricted access' ); ?>


 <ul class="headmenu">
            	<li class="odd">
                    <a href="/dashboard/show">
						<span class='head-icon iconfa-th-large'></span>
						<span class='headmenu-label'><?php echo $language->lang_echo('DASHBOARD'); ?></span>
					</a>
                </li>
                <li>					
					<?php
					
					$ticketHead = '<span class="count" id="ticketCount">'.$this->get("ticketCount").'</span>
		                    <span class="head-icon iconfa-pushpin"></span>
		                    <span class="headmenu-label">'.$language->lang_echo('NEWTICKETS').'</span>';
					
					?>
	
					<?php echo $this->displayLink('tickets.showAll', $ticketHead, NULL, $this->get("ticketOptions")); ?>

                	<ul class="dropdown-menu newusers">
                        <li class="nav-header"><?php echo $language->lang_echo('NEWTICKETS'); ?></li>';
						$helper = new helper();		
		                <?php foreach($this->get("newTickets") as $tick) {
	                		$name = '<strong>'.$tick['headline'].'</strong><small>Created on: '.$this->get("helper")->timestamp2date($tick['date'], 2).'</small>';
		                	?>
		                	<li><?php echo $this->displayLink('tickets.showTicket', $name ,array('id'=>$tick['id'])) ?></li>
                		<?php } ?>
						<li><?php echo $this->displayLink('tickets.showAll', 'Show more tickets'); ?></li>';
               		</ul>
               		
                </li>
                <li class="odd">

		            <?php $mailHead = '<span class="count">'.$this->get("messageCount").'</span>
		            				   <span class="head-icon head-message"></span>
		            				   <span class="headmenu-label">'.$language->lang_echo('MESSAGES').'</span>'; 
		            ?>
		            				   
					<?php echo $this->displayLink('messages.showAll', $mailHead, NULL, $this->get("mailOptions")); ?>	
					
					<ul class="dropdown-menu">
                    	<li class="nav-header"><?php echo $language->lang_echo('MESSAGES'); ?></li>';
	                    	<?php foreach ($this->get("messageCount") as $msg) { ?>
	                    		
	                    		<li><?php echo $this->displayLink('messages.showAll', '<span class="icon-envelope"></span> '.$language->lang_echo('NEWMESSAGEFROM').' <strong>'.$msg["firstname"].'</strong>',array('id'=>$msg["id"])); ?></li>
	                        
	                        <?php } ?>
                        
                		<li><?php echo $this->displayLink('messages.showAll', $language->lang_echo('SHOWMOREMESSAGES')); ?></li>
                 	</ul>
                 		
                </li>
                <li>
                    <a  href='/calendar/showMyCalendar'>
						<span class='head-icon iconfa-calendar'></span>
						<span class='headmenu-label'><?php echo $language->lang_echo('MYTIMESHEETS'); ?></span>							
					</a>
                </li>
                
                <?php echo $this->displayLink('/timesheets/showMy', '<li class="odd">'.$language->lang_echo('MYTIMESHEETS')).'</li>'; ?>
                
                
            </ul><!--headmenu-->