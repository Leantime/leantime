<script type="text/javascript">
	$(document).ready(function() 
    	{ $('#tabs').tabs();

        	$('#comments').pager('div');

        	toggleCommentBoxes(0);
			
    	} 

	); 

	function toggleCommentBoxes(id){
		
		$('.commentBox').hide('fast',function(){

			$('.commentBox textarea').remove(); 

			$('#comment'+id+'').prepend('<textarea rows="5" cols="30" name="text"></textarea>');
			
				
				
				
		}); 

		$('#comment'+id+'').show('slow');		

		
	}
</script>


			<?php
			defined( 'RESTRICTED' ) or die( 'Restricted access' );
			$ticket = $this->get('ticket');
			$objTicket = $this->get('objTicket');
			$helper = $this->get('helper');
			$state = $this->get('state');

			?>

			<?php if($this->get('info') != ''){ ?>

<div class="fail"><span class="info"><?php echo $lang[$this->get('info')]; ?></span>
</div>

			<?php } ?>

<h1><?php echo ''.$lang['TICKET'].' #'.$ticket['id'].''; ?></h1>

<div id="tabs">
<ul>
	<li><a href="#ticketdetails"><?php echo$lang['TICKETDETAILS']; ?></a></li>
	<li><a href="#technicalDetails"><?php echo$lang['TECHNICAL_DETAILS']; ?></a></li>
	<li><a href="#attachments"><?php echo$lang['ATTACHMENTS']; ?> (<?php echo $this->get('numFiles'); ?>)</a></li>
	<li><a href="#commentList"><?php echo$lang['COMMENTS']; ?> (<?php echo $this->get('numComments'); ?>)</a></li>
	<?php if($this->get('role') === 'admin' || $this->get('role') === 'employee') {?>
	<li><a href="#timesheet"><?php echo$lang['TIMESHEET']; ?></a></li>
	<?php } ?>
</ul>

<div id="ticketdetails">

<p><strong><?php echo $ticket['headline']; ?></strong><br />
<br />
<?php if($ticket['dependingTicketId'] != 0){ ?> <?php echo ''.$lang['TICKET_DEPENDS_ON'].'<a href="index.php?act=tickets.showTicket&id='.$ticket['dependingTicketId'].'">#'.$ticket['dependingTicketId'].'</a>'; ?>
<br />
<?php } ?>

<strong><?php echo $lang['TICKET_ID']; ?>:</strong> #<?php echo $ticket['id']; ?><br />
<strong><?php echo $lang['PROJECT']; ?>:</strong> <?php echo $ticket['projectName']; ?><br />
<strong><?php echo $lang['CLIENT']; ?>:</strong> <?php echo $ticket['clientName']; ?><br />
<strong><?php echo $lang['DATE_OF_TICKET']; ?>:</strong> <?php echo $helper->timestamp2date($ticket['date'], 2); ?><br />
<strong><?php echo $lang['DATE_TO_FINISH']; ?>:</strong> <?php echo $helper->timestamp2date($ticket['dateToFinish'], 2); ?><br />
<strong><?php echo $lang['DATE_FROM']; ?>:</strong><?php echo $helper->timestamp2date($ticket['editFrom'], 2); ?><br />
<strong><?php echo $lang['DATE_TO']; ?>:</strong> <?php echo $helper->timestamp2date($ticket['editTo'], 2); ?><br />






<strong><?php echo $lang['PLAN_HOURS']; ?>:</strong> <?php echo $ticket['planHours']; ?><br />
<strong><?php echo $lang['STATUS']; ?>:</strong> <span
				class="<?php echo strtolower($state[$ticket['status']]); ?>"><?php echo $lang[$state[$ticket['status']]]; ?></span><br />
<strong><?php echo $lang['PRIORITY']; ?>:</strong> <?php echo $lang[$objTicket->getPriority($ticket['priority'])]; ?><br />

	

<br />
			<?php echo $ticket['description']; ?><br />
<br />
</p>
</div>

<div id="technicalDetails">
<p><strong><?php echo $lang['OPERATING_SYSTEM']; ?>:</strong> <?php echo  $lang[$ticket['os']]; ?><br />
<strong><?php echo $lang['BROWSER']; ?>:</strong> <?php echo  $lang[$ticket['browser']]; ?><br />
<strong><?php echo $lang['RESOLUTION']; ?>:</strong> <?php echo  $lang[$ticket['resolution']]; ?><br />
<strong><?php echo $lang['VERSION']; ?>:</strong> <?php echo  $ticket['version']; ?><br />
<strong><?php echo $lang['URL']; ?>:</strong> <?php echo $ticket['url']; ?><br />
</p>

</div>

<div id="attachments"><?php 

$row = '';

foreach($this->get('files') as $row){?> <a
	href="userdata/<?php echo $row['encName']; ?>" target="_blank"><?php echo $row['realName']; ?></a><br />
<?php printf("<span class=\"grey\">".$lang['UPLOADED_BY_ON']."</span>", $row['firstname'], $row['lastname'], $helper->timestamp2date($row['date'], 2)); ?>

<?php if($this->get('role') === 'admin'){ ?> | <a
	href="index.php?act=tickets.showTicket&amp;id=<?php echo $ticket['id']; ?>&amp;delFile=<?php echo $row['encName']; ?>"><?php echo $lang['DELETE']; ?></a>
<?php } ?> <br />
<hr />
<br />
<?php } ?> <?php if(count($objTicket->getFiles($ticket['id'])) == 0){ ?>
<?php echo $lang['ERROR_NO_FILES']; ?> <?php } ?></div>

<div id="commentList">

<form method="post" accept-charset="utf-8"
	action="index.php?act=tickets.showTicket&amp;id=<?php echo $ticket['id']; ?>#commentList">

<a href="javascript:void(0);" onclick="toggleCommentBoxes(0)">Kommentieren</a>	
<br /><br />
	<span style="display:none;" id="comment0" class="commentBox">
		<textarea rows="5" cols="30" name="text"></textarea><br />
		<input type="submit" value="<?php echo $lang['SUBMIT']; ?>"
			name="comment" class="button" />
			<input type="hidden" name="father" id="father"/>
		
		<br />
</span>
<hr />


<div id="comments">
<div><?php 
$i = 1;
$k = 1;
$oldCommentParent = '';

$openSpan = 0;

foreach($this->get('comments') as $row){?>


	
	<?php 
	
		$tabs = $row['level'] * 20; 
		
		
		
	?>
	
	<span style="display:block; padding-left:10px; margin-left:<?php echo $tabs;?>px; <?php if($tabs > 1) echo'background:#e1e1e1;'?> border-bottom:1px solid #fff;">
	
		<br />
	

		<p><?php echo nl2br($row['text']); ?></p>
		<br />
		
		<?php printf("<small class=\"grey\">".$lang['WRITTEN_ON_BY']."</small>", $helper->timestamp2date($row['date'], 2), $helper->timestamp2date($row['date'], 1), $row['firstname'], $row['lastname']); ?>
		<br />
		<?php if($this->get('role') === 'admin'){ ?>  <a
			href="index.php?act=tickets.showTicket&amp;id=<?php echo $ticket['id']; ?>&amp;delComment=<?php echo $row['id']; ?>#commentList"><?php echo $lang['DELETE']; ?></a>
		<?php } ?>
		
		| <a href="javascript:void(0);" onclick="toggleCommentBoxes(<?php echo $k; ?>)">Kommentieren</a>
		<br /><br /><hr />
		<span style="display:none;" id="comment<?php echo$k;?>" class="commentBox">
			<textarea rows="5" cols="30" name="text"></textarea><br />
			<input type="submit" value="<?php echo $lang['SUBMIT']; ?>"
				name="comment" class="button" onclick="$('#father').val('<?php echo $row['id']; ?>')" />
		</span>
		
		<br/>
		
		
	</span>

	
	
		
		
	<?php $oldCommentParent = $row['commentParent']; ?>
	
	<?php if($i == '5'){ ?></div>
	<div><?php $i=0;
	}

	$i++;
	$k++;
}

if(count($this->get('comments')) == 0){?> <?php echo $lang['ERROR_NO_COMMENTS']; ?>
<?php } ?></div>

<br /><br />

</div>

</div>











<?php if($this->get('role') === 'admin' || $this->get('role') === 'employee') {?>

<script type="text/javascript">
			$(document).ready(function() 
		    	{ 
				$("#date").datepicker({
					
					dateFormat: 'dd.mm.yy',
					dayNames: [<?php echo''.$lang['DAYNAMES'].'' ?>],
					dayNamesMin:  [<?php echo''.$lang['DAYNAMES_MIN'].'' ?>],
					monthNames: [<?php echo''.$lang['MONTHS'].'' ?>]
				});
					
		    	} 
			); 
		</script>

<div id="timesheet"><?php $values = $this->get('values'); ?>

		<p><?php echo $lang['PLAN_HOURS']; ?>: <?php echo $ticket['planHours']; ?><br />
		<?php echo $lang['BOOKED_HOURS']; ?>: <?php echo $this->get('timesheetsAllHours'); ?><br />
		</p>
		<form method="post"
			action="index.php?act=tickets.showTicket&amp;id=<?php echo $ticket['id']; ?>#timesheet">

		<br />
		<label for="kind"><?php echo $lang['KIND']?></label><br /> <select id="kind"
			name="kind">
			<?php foreach($this->get('kind') as $row){
				echo'<option value="'.$row.'"';
				if($row == $values['kind']) echo ' selected="selected"';
				echo'>'.$lang[$row].'</option>';

			}
			?>

		</select><br />
		<label for="date"><?php echo $lang['DATE']?></label> <br /><input
			type="text" id="date" name="date"
			value="<?php echo $values['date'] ?>" size="7" /> <br />
		<label for="hours"><?php echo $lang['HOURS']?></label><br /> <input
			type="text" id="hours" name="hours"
			value="<?php echo $values['hours'] ?>" size="7" /> <br />
		<label for="description"><?php echo $lang['DESCRIPTION']?></label> <br /><textarea
			rows="5" cols="30" id="description" name="description"><?php echo $values['description']; ?></textarea><br />
		<br /><input type="submit" value="<?php echo $lang['SAVE']; ?>"
			name="saveTimes" class="button" /></form>
		
		
<script
	type="text/javascript"
	src="includes/libs/open-flash-chart/js/json/json2.js"></script>
<script
	type="text/javascript"
	src="includes/libs/open-flash-chart/js/swfobject.js"></script>
<script type="text/javascript">
		swfobject.embedSWF("includes/libs/open-flash-chart/open-flash-chart.swf", "my_chart", "350", "200", "9.0.0");
		</script>

<script type="text/javascript">
		
			function ofc_ready()
			{
			 	//Do sth if ofc is ready  
			}
			
			function open_flash_chart_data()
			{
				//Do sth if ofc gets data    
			    return JSON.stringify(data);
			}
			
			function findSWF(movieName) {
			  if (navigator.appName.indexOf("Microsoft")!= -1) {
			    return window[movieName];
			  } else {
			    return document[movieName];
			  }
			}
			<?php $chart = $this->get('chart'); ?>
			var data = <?php echo $chart->toPrettyString(); ?>;
		
		</script>


		<br />


		<br />
		<br />
		<div id="my_chart"></div>


</div>
		<?php } ?>

<div class="footerEdit">
<hr />

<p><?php echo $lang['AUTHOR']; ?>: <?php echo ''.$ticket['userFirstname'].' '.$ticket['userLastname'].''; ?><br />
		<?php if($ticket['editorFirstname'] == '' && $ticket['editorLastname'] == ''){ ?>
		<?php echo $lang['TICKET_RELATES_TO_NOBODY']; ?> <?php }else{ ?> <?php printf($lang['TICKET_RELATES_TO'], $ticket['editorFirstname'], $ticket['editorLastname']);?>
		<?php } ?> <br />
		</p>
</div>
</div>
<br /><br />
	<?php if($this->get('editable') === true){ ?> 
	<a href="index.php?act=tickets.editTicket&id=<?php echo $ticket['id']; ?>" class="link"><?php echo $lang['EDIT']; ?></a>
	<?php } ?> 
	
	<?php if($this->get('role') === 'admin'){ ?> <a
	href="index.php?act=tickets.delTicket&id=<?php echo $ticket['id']; ?>" class="link"><?php echo $lang['DELETE']; ?></a>
	<?php } ?>
	
	<a href="index.php?act=tickets.showAll" class="link"><?php echo $lang['BACK']; ?></a><br />
	
