<?php
	defined( 'RESTRICTED' ) or die( 'Restricted access' );
	$projects = $this->get('projects');
	$objTickets = $this->get('objTickets');
	$values = $this->get('values');
	$helper = $this->get('helper');
	$type = $this->get('type');
?>

<script type="text/javascript">
	/*$(function() {
		$("#dateToFinish, #editTo, #editFrom").datepicker({
			minDate: 0, 
			maxDate: '+1Y', 
			dateFormat: 'dd.mm.yy',
			dayNames: [<?php echo''.$lang['DAYNAMES'].'' ?>],
			dayNamesMin:  [<?php echo''.$lang['DAYNAMES_MIN'].'' ?>],
			monthNames: [<?php echo''.$lang['MONTHS'].'' ?>]
		});
	});*/
		jQuery(document).ready(function($) { 
			$("#datepicker1").datepicker();
			$("#datepicker2").datepicker();
			$("#dateToFinish").datepicker();		
		}); 
</script>

<style type='text/css'>
	.stdform label { width: 105px !important; }
</style>

<div class="pageheader">
            <form action="index.php?act=tickets.showAll" method="post" class="searchbar">
                <input type="text" name="term" placeholder="To search type and hit enter..." />
            </form>
            
            <div class="pageicon"><span class="<?php echo $this->getModulePicture() ?>"></span></div>
            <div class="pagetitle">
                <h5><?php echo $language->lang_echo('TICKETDETAILS'); ?></h5>
                <h1><h1><?php echo $language->lang_echo('EDIT_TICKET'); ?></h1></h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">
            	
				<?php echo $this->displayNotification() ?>

<form method="post" class="stdform" action="">



<div class="row-fluid">
	<div class="span6">




		<div class="widget">
		   <h4 class="widgettitle"><?php echo $language->lang_echo('OVERVIEW'); ?></h4>
		   <div class="widgetcontent" style="min-height: 460px">
		
		<label for="headline"><?php echo $language->lang_echo('HEADLINE'); ?></label> <input
			type="text" name="headline" id="headline"
			value="<?php echo $values['headline']; ?>" /><br />
			
		<label for='type'>Type</label>
			<select id='type' name='type'>
				<?php foreach($type as $types) {
					echo "<option value='".$types."' ";
						if($types === $values['type']) {
							echo "selected='selected'";
						}
					echo ">".$types."</option>";
				} ?>
			</select><br/>
		
		<label for="project"><?php echo $language->lang_echo('PROJECT'); ?></label> <select
			id="project" name="project">
			<optgroup>
		
			<?php foreach($this->get('projects') as $row) {
				$currentClientName = $row['clientName'];
		
				if($currentClientName != $lastClientName){
					echo'</optgroup><optgroup label="'.$currentClientName.'">';
				}
				echo'<option value="'.$row['id'].'" ';
				if($values['projectId'] === $row['id'])echo'selected="selected"';
				echo'>'.$row['name'].'</option>';
		
				$lastClientName = $row['clientName'];
		
			}
			?>
		
			</optgroup>
		</select><br />
		
		<label for="priority"><?php echo $lang['PRIORITY']; ?></label> <select
			id="priority" name="priority">
		
			<?php foreach($objTickets->priority as $key) {
				echo'<option value="'.$key.'"';
				if($key == $values['priority']) echo'selected="selected"';
				echo'>'.$key;
				if ($key == '1') {
					echo ' - lowest';
				} else if ($key == '6') {
					echo ' - highest';
				}
				echo'</option>';
			} ?>
		
		</select><br />
		
		<label for="status"><?php echo $lang['STATUS']; ?></label> 
		<select id="status" name="status">
		
			<?php foreach($objTickets->state as $key => $value) { 
				echo '<option value="'.$key.'"';
				if ($key == $values['status']) echo'selected="selected"';
				echo '>'.$language->lang_echo($value).'</option>';
			} ?>
		
		</select><br />
		
		<label for=""><?php echo $language->lang_echo('PUSHED_TO') ?></label>
		<span class='formwrapper'>
			<div class='checker' id='uniform-undefined'>
				<span>
					<input type="checkbox" name="staging" 
						<?php if($values['staging'] == 1): ?>checked='checked'<?php endif; ?> />
				</span>
			</div><?php echo $language->lang_echo('STAGING') ?><br/>
			<div class='checker' id='uniform-undefined'>
				<span>
					<input type="checkbox" name="production" 
						<?php if($values['production'] == 1): ?>checked='checked'<?php endif; ?> />
				</span>
			</div><?php echo $language->lang_echo('PRODUCTION') ?>	
		</span><br/>
		
		<label for="planHours"><?php echo $lang['PLAN_HOURS']; ?></label> <input
			type="text" name="planHours" id="planHours"
			value="<?php echo $values['planHours']; ?>" /><br />
		<br />
		<label for="dependingTicket"><?php echo $lang['TICKET_DEPENDS_ON']; ?></label>
		<select id="dependingTicketId" name="dependingTicketId">
			<option value="0"><?php echo $lang['NO_DEPENDENCY']; ?></option>
			<?php foreach($objTickets->getAllBySearch('', $values['projectId']) as $row) {
		
				echo'<option value="'.$row['id'].'" ';
				if($values['dependingTicketId'] == $row['id'])echo'selected="selected"';
				echo'>#'.$row['id'].', '.$row['headline'].'</option>';
				
			} ?>
		
		</select><br />
		
		
		
		</div></div>

<div class="widget ">
   <h4 class="widgettitle"><?php echo $language->lang_echo('ASSIGN'); ?></h4>
   <div class="widgetcontent">
   	
	   	<div class="assign-container">
			<?php foreach($this->get('availableUsers') as $row): ?>
				<?php if($_SESSION['userdata']['role'] == "user") { ?>
					
					<?php if(in_array($row['id'],explode(',',$values['editorId'])) || ($row['id'] == $_SESSION['userdata']["id"])){ ?>
						<p class="half">
							<label for="user-<?php echo $row['id'] ?>"><?php echo $row['lastname'] .', '. $row['firstname'] ?></label>
							<input type='checkbox' name='editorId[]' id="user-<?php echo $row['id'] ?>" value='<?php echo $row['id'] ?>' 
								<?php if(in_array($row['id'],explode(',',$values['editorId']))): ?>checked="checked"<?php endif; ?>/>
						</p>
						
					<?php } ?>
					
				<?php } else { ?>
					
					<p class="half">
						<label for="user-<?php echo $row['id'] ?>"><?php echo $row['lastname'] .', '. $row['firstname'] ?></label>
						<input type='checkbox' name='editorId[]' id="user-<?php echo $row['id'] ?>" value='<?php echo $row['id'] ?>' 
							<?php if(in_array($row['id'],explode(',',$values['editorId']))): ?>checked="checked"<?php endif; ?>/>
					</p>
					
				<?php } ?>
			<?php endforeach; ?>
		</div>

	</div>
</div>


</div>
<div class="span6">
	
	<div class="widget ">
   <h4 class="widgettitle"><?php echo $language->lang_echo('TIMELINE'); ?></h4>
   <div class="widgetcontent" style="min-height: 460px">
   	
<label for="dateToFinish"><?php echo $lang['DATE_TO_FINISH']; ?><small
	class="grey"><br />
(dd.mm.yyyy)</small></label> <input type="text" name="dateToFinish"
	id="dateToFinish" value="<?php echo $values['dateToFinish']; ?>" /><br />

	<?php if($this->get('role') !== 'client'){ ?> <br />
<label for="editFrom"><?php echo $lang['EDIT_FROM']; ?></label> <input
	type="text" name="editFrom" id="datepicker1"
	value="<?php echo $values['editFrom']; ?>" /><br />
	
<label for="editTo"><?php echo $lang['EDIT_TO']; ?></label> <input
	type="text" name="editTo" id="datepicker2"
	value="<?php echo $values['editTo']; ?>" /><br />
<br />

	<?php } ?> 
	



</div></div>
	<div class="widget">
   <h4 class="widgettitle"><?php echo $language->lang_echo('TECHNICAL_DETAILS'); ?></h4>
   <div class="widgetcontent">

<label for="os"><?php echo $language->lang_echo('OPERATING_SYSTEM'); ?></label> 
<select name="os" id="os">
	<?php foreach($objTickets->os as $row) {
		echo'<option value="'.$row.'" ';
		if($values['os'] == $row){
			echo'selected="selected"';
		}
		echo'>'.$lang[$row].'</option>';
	} ?>
</select><br />

<label for="browser"><?php echo $language->lang_echo('BROWSER'); ?></label> 
<select name="browser" id="browser">
	<?php foreach($objTickets->browser as $row) {
		echo'<option value="'.$row.'" ';
		if($values['browser'] == $row){
			echo'selected="selected"';
		}
		echo'>'.$lang[$row].'</option>';
	} ?>
</select><br />

<label for="resolution"><?php echo $lang['RESOLUTION']; ?></label> <select
	name="resolution" id="resolution">

	<?php foreach($objTickets->res as $row)
	{
		echo'<option value="'.$row.'" ';
		if($values['resolution'] == $row){
			echo'selected="selected"';
		}
		echo'>'.$lang[$row].'</option>';
	}
	?>

</select><br />

<label for="version"><?php echo $lang['VERSION']; ?></label> <input
	type="text" name="version" id="version"
	value="<?php echo $values['version']; ?>" /><br />

<label for="url"><?php echo $lang['URL']; ?></label> <input type="text"
	name="url" id="url" value="<?php echo $values['url']; ?>" /><br />



</div>
</div>
</div>




















<div class="widget clear">
   <h4 class="widgettitle"><?php echo $language->lang_echo('DESCRIPTION'); ?></h4>
   <div class="widgetcontent">
   	
<textarea id="elm1" name="description" rows="15" cols="80" style="width: 320px" class="tinymce"><?php echo $values['description'] ?></textarea><br/>	



<p class="stdformbutton">
	<input type="submit" name="save" id="save" value="<?php echo $lang['SAVE']; ?>" class="btn btn-primary" />
	<button class="btn btn-primary" name="save" type="submit"><?php echo $lang['SAVE']; ?></button>
	<button class="btn" type="reset">Reset Button</button>
</p>


	</div>
</div>

</form>

</div>
