<script type="text/javascript">
	$(document).ready(function() 
    	{ 
        	
    	} 
	); 
</script>

<script	src="includes/modules/general/templates/js/tableHandling.js" type="text/javascript"></script>

<?php
defined( 'RESTRICTED' ) or die( 'Restricted access' );
$tickets = $this->get('objTickets');
$helper = $this->get('helper');
?>

<h1><?php echo $lang['MY_TICKETS']; ?></h1>

<div id="loader">&nbsp;</div>


<form action="index.php?act=tickets.showMy" method="post">

<fieldset class="left"><legend><?php echo $lang['OPEN_TICKETS']; ?></legend>



<table cellpadding="0" cellspacing="0" border="0" class="display" id="resultTable">
	<thead>
		<tr>
			<th><?php echo $lang['TICKET_ID']; ?></th>
			<th><?php echo $lang['TITLE']; ?></th>
			<th><?php echo $lang['CLIENT_PROJECT']; ?></th>
			<th><?php echo $lang['DATE_OF_TICKET']; ?></th>
			<th><?php echo $lang['DATE_TO_FINISH']; ?></th>
			<?php if($this->get('role') !== 'client'){ ?> 
				<th><?php echo $lang['EMPLOYEE']; ?></th>
				<th><?php echo $lang['AUTHOR']; ?></th>
			<?php } ?>
			<th><?php echo $lang['STATUS']; ?></th>
			<th><?php echo $lang['PRIORITY']; ?></th>
		</tr>
		<tr class="filter">
			<th></th>
			<th></th>
			<th></th>
			<th></th>
				<?php if($this->get('role') !== 'client'){ ?> 
				<th></th>
				<th></th>
				<?php } ?>
			<th></th>
			<th></th>
			<th></th>
		</tr>
	</thead>
	<tbody>


	<?php foreach($this->get('allOpenTickets') as $row) {?>

		<tr>
			<td><a
				href="?act=tickets.showTicket&amp;id=<?php echo $row['id']; ?>"><?php echo $row['id']; ?></a></td>
			<td><a
				href="?act=tickets.showTicket&amp;id=<?php echo $row['id']; ?>"><?php echo $row['headline']; ?></a></td>
			<td><?php echo $row['clientName']; ?> / <?php echo $row['projectName']; ?></td>
			<td><?php echo $helper->timestamp2date($row['date'], 2); ?></td>
			<td><?php echo $helper->timestamp2date($row['dateToFinish'], 2); ?></td>
			<?php if($this->get('role') !== 'client'){ ?> 
				<td><?php echo $row['editorLastname'] ?> <?php echo $row['editorFirstname'] ?></td>
				<td><?php echo $row['authorLastname'] ?> <?php echo $row['authorFirstname'] ?></td>
			<?php } ?>
			<td><span
				class="<?php echo strtolower($tickets->getStatus($row['status']));?>"><?php echo $lang[$tickets->getStatus($row['status'])]; ?></span></td>
			<td><?php echo $row['priority']; ?></td>
		</tr>
		<?php } ?>

	</tbody>
</table>



</fieldset>
<fieldset><legend><?php echo $lang['DIRECTMENU']; ?></legend>
<p><a href="index.php?act=tickets.newTicket"><?php echo $lang['NEW_TICKET']; ?></a><br />
<a href="index.php?act=users.editOwn"><?php echo $lang['EDIT_MY_DATA']; ?></a>
</p>
</fieldset>

<fieldset class="left"><legend><?php echo $lang['CLOSED_TICKETS']; ?></legend>


<table cellpadding="0" cellspacing="0" border="0" class="display" id="resultTable2">
	<thead>
		<tr>
			<th><?php echo $lang['TICKET_ID']; ?></th>
			<th><?php echo $lang['TITLE']; ?></th>
			<th><?php echo $lang['CLIENT_PROJECT']; ?></th>
			<th><?php echo $lang['DATE_OF_TICKET']; ?></th>
			<th><?php echo $lang['DATE_TO_FINISH']; ?></th>
			<?php if($this->get('role') !== 'client'){ ?> 
				<th><?php echo $lang['EMPLOYEE']; ?></th>
				<th><?php echo $lang['AUTHOR']; ?></th>
			<?php } ?>
			<th><?php echo $lang['STATUS']; ?></th>
			<th><?php echo $lang['PRIORITY']; ?></th>
		</tr>
	
	<tr class="filter">
		<th></th>
		<th></th>
		<th></th>
		<th></th>
		<?php if($this->get('role') !== 'client'){ ?> 
				<th></th>
				<th></th>
				<?php } ?>
		<th></th>
		<th></th>
		<th></th>
	</tr>
	</thead>
	<tbody>


	<?php foreach($this->get('allClosedTickets') as $row) { ?>

		<tr>
			<td><a
				href="?act=tickets.showTicket&amp;id=<?php echo $row['id']; ?>"><?php echo $row['id']; ?></a></td>
			<td><a
				href="?act=tickets.showTicket&amp;id=<?php echo $row['id']; ?>"><?php echo $row['headline']; ?></a></td>
			<td><?php echo $row['clientName']; ?> / <?php echo $row['projectName']; ?></td>
			<td><?php echo $helper->timestamp2date($row['date'], 2); ?></td>
			<td><?php echo $helper->timestamp2date($row['dateToFinish'], 2); ?></td>
			<?php if($this->get('role') !== 'client'){ ?> 
				<td><?php echo $row['editorLastname'] ?> <?php echo $row['editorFirstname'] ?></td>
				<td><?php echo $row['authorLastname'] ?> <?php echo $row['authorFirstname'] ?></td>
			<?php } ?>
			<td><span
				class="<?php echo strtolower($tickets->getStatus($row['status']));?>"><?php echo $lang[$tickets->getStatus($row['status'])]; ?></span></td>
			<td><?php echo $row['priority'] ?></td>
		</tr>
		<?php } ?>

	</tbody>
</table>

</fieldset>



<fieldset><legend><?php echo $lang['MY_PROJECTS']; ?></legend>
<p><?php foreach($this->get('userProjectrelation') as $row){?> <a
	href="index.php?act=projects.showProject&amp;id=<?php echo $row['projectId']; ?>"><?php echo $row['name']; ?></a><br />
		<?php } ?></p>
</fieldset>
</form>
