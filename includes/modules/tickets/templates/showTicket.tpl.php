<?php

defined( 'RESTRICTED' ) or die( 'Restricted access' );
$ticket = $this->get('ticket');
$objTicket = $this->get('objTicket');
$helper = $this->get('helper');
$state = $this->get('state');
$statePlain = $this->get('statePlain');
$userId = $this->get('userId');
$unreadCount = $this->get('unreadCount');
$tickets = new tickets();
?>
<div class="pageheader">
            <form action="index.php?act=tickets.showAll" method="post" class="searchbar">
                <input type="text" name="term" placeholder="To search type and hit enter..." />
            </form>
            
            <div class="pageicon"><span class="<?php echo $this->getModulePicture() ?>"></span></div>
            <div class="pagetitle">
                <h5><?php echo $language->lang_echo('TICKET_DETAILS') ?></h5>
                <h1><?php echo ''.$language->lang_echo('TICKET').' #'.$ticket['id'].'  |  '.$ticket['headline']; ?></h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">
            	
            	
            	
<script type="text/javascript">
 function changeStatus(id){
		    	var state = new Array('label-success','label-warning','label-info','label-important','label-inverse');
		
				var statePlain = new Array('Finished','Problem','Unapproved','New','Seen');
		
				var newStatus = jQuery("#status-select-"+id+" option:selected").val();
				
				jQuery.ajax({ 
					url: '/index.php?act=general.ajaxRequest&module=tickets.showAll&export=true', 
					type: 'post',
					data: { ticketId: id, newStatus: newStatus},
					success: function(msg){
						
						jQuery("#status-"+id).show();
						
						jQuery("#status-"+id).attr("class", "f-left "+state[newStatus]);
						jQuery("#status-"+id).html(statePlain[newStatus]);
						
						jQuery("#status-spinner-"+id).show();
						
						jQuery("#status-select-"+id).hide();
						
						jQuery(".maincontentinner").prepend("<div class='alert alert-success'><button data-dismiss='alert' class='close' type='button'>×</button>"+msg+"</div>");
					}
				});
		
			}
			
			
	jQuery(document).ready(function() 
    	{ 
    		
    		
    		jQuery("#ticketCount").html(<?php echo $unreadCount; ?>);
    		jQuery('.tabbedwidget').tabs();
    		
    		// Transform upload file
			jQuery('.uniform-file').uniform();
			
			
			
	
	
	
	
	
		}

	); 
	
	

	jQuery(window).load(function() {
			jQuery(window).resize();
	});


</script>

<?php echo $this->displayNotification(); ?>

<?php if(!isset($_POST['punchIn']) && $tickets->isClocked($_SESSION['userdata']['id'])!=true) { ?>
<!--
<form action='' method='POST'>
    	<input class='f-right button' type='submit' value='Punch in' name='punchIn' style='padding: 3px;' />
</form>
-->
<?php } else { ?>

<form action='' method='POST'>
		<!--<label class="f-right">Description:</label><br class='clear' />
		<textarea name='description' class="f-right"></textarea><br class='clear'/>-->
        <input class='f-right button' type='submit' value='Punch Out' name='punchOut' style='color: red; padding: 3px;' />
</form>

<?php } ?>

<div class="tabbedwidget tab-primary">
	
	<ul class=''>
		<li><a href="#ticketdetails"><?php echo $this->displaySubmoduleTitle('tickets-ticketDetails') ?></a></li>
		<!--<li><a href="#technicalDetails"><?php echo $this->displaySubmoduleTitle('tickets-technicalDetails') ?></a></li>-->
		<li><a href="#files"><?php echo $this->displaySubmoduleTitle('tickets-attachments') ?> (<?php echo $this->get('numFiles'); ?>)</a></li>
		<li><a href="#comment"><?php echo $this->displaySubmoduleTitle('comments-generalComment') ?> (<?php echo $this->get('numComments'); ?>)</a></li>
		<?php if ($this->displaySubmoduleTitle('tickets-timesheet') != ''): ?>
			<li><a href="#timesheet">Time Tracking</a></li>
		<?php endif; ?>
		
		<?php if ($this->displayLink('tickets.editTicket','x') !== false || $this->displayLink('tickets.delTicket','x') !== false): ?>
			<div class="btn-group">
		    	<button data-toggle="dropdown" class="btn btn-primary dropdown-toggle"><?php echo $language->lang_echo('ACTION') ?>  <span class="caret"></span></button>
		        <ul class="dropdown-menu">		
					<li><?php echo $this->displayLink('tickets.editTicket',$language->lang_echo('EDIT'),array('id'=>$ticket['id']), array('class' => 'dropdown-list')) ?></li>
					<li><?php echo $this->displayLink('tickets.delTicket',$language->lang_echo('DELETE'),array('id'=>$ticket['id']), array('class' => 'dropdown-list')) ?></li>				
				</ul>
			</div>
		<?php endif; ?>
	</ul>
	
	<div id="ticketdetails">
		<?php $this->displaySubmodule('tickets-ticketDetails') ?>
		<?php $this->displaySubmodule('tickets-technicalDetails') ?>
	</div>
	
	<div id="files">
		<?php $this->displaySubmodule('tickets-attachments') ?>
	</div>
	
	<div id="comment">
		<?php $this->displaySubmodule('comments-generalComment') ?>
	</div>
	
	<div id="timesheet">
		<?php $this->displaySubmodule('tickets-timesheet') ?>
	</div>
	
	

</div>

<br /><br />

</div>
</div>
