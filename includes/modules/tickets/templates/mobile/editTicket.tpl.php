<?php
defined( 'RESTRICTED' ) or die( 'Restricted access' );
$projects = $this->get('projects');
$objTickets = $this->get('objTickets');
$values = $this->get('values');
$helper = $this->get('helper');

?>

<script type="text/javascript">
	$(function() {
		$("#dateToFinish, #editTo, #editFrom").datepicker({
			minDate: 0, 
			maxDate: '+1Y', 
			dateFormat: 'dd.mm.yy',
			dayNames: [<?php echo''.$lang['DAYNAMES'].'' ?>],
			dayNamesMin:  [<?php echo''.$lang['DAYNAMES_MIN'].'' ?>],
			monthNames: [<?php echo''.$lang['MONTHS'].'' ?>]
		});
	});
</script>


<?php if($this->get('info') != ''){ ?>

<div class="fail"><span class="info"><?php echo $lang[$this->get('info')]; ?></span>
</div>

<?php } ?>

<h1><?php echo $lang['EDIT_TICKET']; ?></h1>

<form method="post">
<fieldset><legend><?php echo $lang['TICKETDETAILS']; ?></legend>

<label for="headline"><?php echo $lang['HEADLINE']; ?></label><br /> <input
	type="text" name="headline" id="headline"
	value="<?php echo $values['headline']; ?>" /><br />
<label for="description"><?php echo $lang['DESCRIPTION']; ?></label> <textarea
	rows="10" cols="30" name="description" id="description"><?php echo $values['description']; ?></textarea><br />

<label for="project"><?php echo $lang['PROJECT']; ?></label> <select
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

	<?php
	foreach($objTickets->priority as $key => $value) {
		echo'<option value="'.$key.'"';
		if($key == $values['priority']) echo'selected="selected"';
		echo'>'.$lang[$value].'</option>';
	}
	?>

</select><br />

<label for="status"><?php echo $lang['STATUS']; ?></label> <select
	id="status" name="status">

	<?php
	foreach($objTickets->state as $key => $value) {
		echo'<option value="'.$key.'"';
		if($key == $values['status']) echo'selected="selected"';
		echo'>'.$lang[$value].'</option>';
	}?>

</select><br />

<label for="dateToFinish"><?php echo $lang['DATE_TO_FINISH']; ?><small
	class="grey"><br />
(dd.mm.yyyy)</small></label> <br /><br /><input type="text" name="dateToFinish"
	id="dateToFinish" value="<?php echo $values['dateToFinish']; ?>" /><br />

	<?php if($this->get('role') !== 'client'){ ?> <br />
<label for="editFrom"><?php echo $lang['EDIT_FROM']; ?></label> <br /><input
	type="text" name="editFrom" id="editFrom"
	value="<?php echo $values['editFrom']; ?>" /><br />
<label for="editTo"><?php echo $lang['EDIT_TO']; ?></label> <br /><input
	type="text" name="editTo" id="editTo"
	value="<?php echo $values['editTo']; ?>" /><br />
<br />
<label for="planHours"><?php echo $lang['PLAN_HOURS']; ?></label> <br /><input
	type="text" name="planHours" id="planHours"
	value="<?php echo $values['planHours']; ?>" /><br />
<br />
<label for="dependingTicket"><?php echo $lang['TICKET_DEPENDS_ON']; ?></label><br />
<select id="dependingTicketId" name="dependingTicketId">
	<option value="0"><?php echo $lang['NO_DEPENDENCY']; ?></option>
	<?php
	foreach($objTickets->getAllBySearch('', $values['projectId']) as $row){

		echo'<option value="'.$row['id'].'" ';
		if($values['dependingTicketId'] == $row['id'])echo'selected="selected"';
		echo'>#'.$row['id'].', '.$row['headline'].'</option>';
	}
	?>

</select><br />

<br />
<label for="userId"><?php echo $lang['EMPLOYEE']; ?></label> <br /><select
	id="editorId" name="editorId">
	<option value=""><?php echo ''.$lang['NO_EDITOR'].''; ?></option>
	<optgroup label="<?php echo ''.$lang['AUTHOR'].''; ?>">
		<option value="<?php echo $values['userId']; ?>"
		<?php if($values['editorId'] == $values['userId'])echo'selected="selected"';?>
		><?php echo ''.$values['userLastname'].', '.$values['userFirstname'].''; ?>
	
	</optgroup>
	<optgroup label="<?php echo ''.$lang['EMPLOYEE'].''; ?>">
	<?php foreach($this->get('employees') as $row){
		echo'<option value="'.$row['id'].'" ';
		if($values['editorId'] == $row['id'])echo'selected="selected"';
		echo'>'.$row['lastname'].', '.$row['firstname'].'</option>';
	}?>
	</optgroup>
</select><br />

	<?php } ?> </fieldset>

<fieldset><legend><?php echo $lang['TECHNICAL_DETAILS']; ?></legend> <label
	for="os"><?php echo $lang['OPERATING_SYSTEM']; ?></label> <br /><select
	name="os" id="os">

	<?php foreach($objTickets->os as $row)
	{
		echo'<option value="'.$row.'" ';
		if($values['os'] == $row){
			echo'selected="selected"';
		}
		echo'>'.$lang[$row].'</option>';
	}
	?>

</select><br />

<label for="browser"><?php echo $lang['BROWSER']; ?></label> <br /><select
	name="browser" id="browser">

	<?php
	foreach($objTickets->browser as $row)
	{
		echo'<option value="'.$row.'" ';
		if($values['browser'] == $row){
			echo'selected="selected"';
		}
		echo'>'.$lang[$row].'</option>';
	}
	?>

</select><br />

<label for="resolution"><?php echo $lang['RESOLUTION']; ?></label> <br /><select
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

<label for="version"><?php echo $lang['VERSION']; ?></label><br /> <input
	type="text" name="version" id="version"
	value="<?php echo $values['version']; ?>" /><br />

<label for="url"><?php echo $lang['URL']; ?></label> <br /><input type="text"
	name="url" id="url" value="<?php echo $values['url']; ?>" /><br />

</fieldset>

<fieldset>
<legend><?php echo $lang['SAVE']; ?></legend>
<input type="submit" name="save" id="save"
	value="<?php echo $lang['SAVE']; ?>" class="button" />
</fieldset>

</form>


<form action="" method="post" enctype="multipart/form-data">

<fieldset><legend><?php echo $lang['ATTACHMENTS']; ?></legend> <label
	for="file"><?php echo $lang['UPLOAD']; ?>:</label> <input name="file"
	id="file" type="file" /><br />
<input name="upload" id="upload" value="<?php echo $lang['UPLOAD']; ?>"
	type="submit" class="button" />
<hr />
<br />

	<?php
	$row = '';

	foreach($objTickets->getFiles($values['id']) as $row){

		echo'<a href="userdata/'.$row['encName'].'" target="_blank">'.$row['realName'].'</a><br />';
		printf("<span class=\"grey\">".$lang['UPLOADED_BY_ON']."</span>", $row['firstname'], $row['lastname'], $helper->timestamp2date($row['date'], 2));

		if($this->get('role') == 'admin'){
			echo' | <a href="index.php?act=tickets.showTicket&amp;id='.$values['id'].'&amp;delFile='.$row['encName'].'">'.$lang['DELETE'].'</a>';
		}

		echo'<br /><br />';
	}

	if(count($objTickets->getFiles($values['id'])) == 0){
		echo''.$lang['ERROR_NO_FILES'].'';
	}
	?></fieldset>
</form>

<a href="index.php?act=tickets.showTicket&amp;id=<?php echo $values['id']; ?>" class="link"><?php echo $lang['BACK']; ?></a>
