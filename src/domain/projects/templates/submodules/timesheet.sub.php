<?php

$helper = $this->get('helper');
$project = $this->get('project');
if(!$project['hourBudget']) {
	$project['hourBudget'] = 'no';
}
$bookedHours = $this->get('bookedHours');
?>

<!--<div style="float:left;">
	<div id="my_chart">&nbsp;</div>
</div>-->

<form action="<?=BASE_URL ?>/projects/showProject/<?php echo $project['id']; ?>#timesheets" method="post">

<h4>
	<?php echo $bookedHours; ?> hours of <?php echo $project['hourBudget'] ?> estimated hours used.
</h4>
<br/>
<table cellpadding="0" cellspacing="0" width="60%" class="table table-bordered">
	<thead>
		<tr id="toggleBody">
			<th class="head0"><label for="dateFrom"><?php echo $this->__('DATE_FROM') ?></label></th>
			<th class="head1"><label for="dateTo"><?php echo $this->__('DATE_TO') ?></label></th>
			<th class="head0"><label></label></th>
			<th class="head1"><label></label></th>
			<th class="head0">&nbsp;</th>
		</tr>
	</thead>
	<tr id="body">
		<td><input type="text" id="dateFrom" name="dateFrom"
			value="<?php echo $this->get('dateFrom'); ?>" size="7" /></td>
		<td><input type="text" id="dateTo" name="dateTo"
			value="<?php echo $this->get('dateTo'); ?>" size="7" /></td>
		<td>
		<label for="userId"><?php echo $this->__('EMPLOYEE'); ?></label>
		<select name="userId" id="userId" onchange="submit();">
			<option value="all"><?php echo $lang['ALL_EMPLOYEES']; ?></option>

			<?php foreach($this->get('employees') as $row) {
				echo'<option value="'.$row['id'].'"';
				if($row['id'] == $this->get('employeeFilter')) echo' selected="selected" ';
				echo'>'.sprintf( $this->__("text.full_name"), $this->escape($row["firstname"]), $this->escape($row['lastname'])).'</option>';
			}

			?>
		</select>
		<br />
		<label for="kind"><?php echo $this->__('KIND') ?></label>
		<select id="kind" name="kind" onchange="submit();">
			<option value="all"><?php echo $this->__('ALL_KINDS') ?></option>
			<?php foreach($this->get('kind') as $row){
				echo'<option value="'.$row.'"';
				if($row == $this->get('actKind')) echo ' selected="selected"';
				echo'>'.$lang[$row].'</option>';

			}
			?>

		</select> </td>
		<td>
		<label for="invEmpl"><?php echo $this->__('INVOICED') ?></label>
		<input type="checkbox" value="on" name="invEmpl" id="invEmpl" onclick="submit();"
			<?php
			if($this->get('invEmpl') == '1') echo ' checked="checked"';
			?>
		/><br />
		<label for="invEmpl"><?php echo $this->__('INVOICED_COMP'); ?></label>
		<input type="checkbox" value="on" name="invComp" id="invComp" onclick="submit();"
			<?php
			if($this->get('invComp') == '1') echo ' checked="checked"';
			?>
		/>
		</td>
		<td><input type="submit" value="<?php echo $this->__('FILTER') ?>" class="reload" /></td>
	</tr>

</table>

</form>


<table cellpadding="0" cellspacing="0" border="0" class="allTickets table table-bordered"
	id="allTickets">
	<colgroup>
		      	<col class="con0"/>
		        <col class="con1" />
		      	<col class="con0"/>
		        <col class="con1" />
		      	<col class="con0"/>
		        <col class="con1" />
		      	<col class="con0"/>
		        <col class="con1" />
		      	<col class="con0"/>
		        <col class="con1" />
		      	<col class="con0"/>
		        <col class="con1" />
	</colgroup>
	<thead>
		<tr>
			<th><?php echo $this->__('ID'); ?></th>
			<th><?php echo $this->__('DATE'); ?></th>
			<th><?php echo $this->__('HOURS'); ?></th>
			<th><?php echo $this->__('PLANHOURS'); ?></th>
			<th><?php echo $this->__('DIFFERENCE_HOURS'); ?></th>
			<th><?php echo $this->__('TICKET'); ?></th>
			<th><?php echo $this->__('PROJECT'); ?></th>
			<th><?php echo $this->__('EMPLOYEE'); ?></th>
			<th><?php echo $this->__('KIND'); ?></th>
			<th><?php echo $this->__('DESCRIPTION'); ?></th>
			<th><?php echo $this->__('INVOICED'); ?></th>
			<th><?php echo $this->__('INVOICED_COMP'); ?></th>
		</tr>
	</thead>
	<tbody>

	<?php
	$sum = 0;
	foreach($this->get('allTimesheets') as $row) {
		$sum = $sum + $row['hours'];?>
		<tr>
			<td><a href="index.php?act=timesheets.editTime&amp;id=<?php echo $row['id']; ?>"><?php echo $row['id']; ?></a></td>
			<td><?php echo $helper->timestamp2date($row['workDate'], 2); ?></td>
			<td><?php echo $row['hours']; ?></td>
			<td><?php echo $row['planHours']; ?></td>
			<?php $diff = $row['planHours']-$row['hours']; ?>
			<td <?php if($diff<0)echo'class="new" ';?>><?php echo $diff; ?></td>
			<td><a href="index.php?act=tickets.showTicket&amp;id=<?php echo $row['ticketId']; ?>"><?php echo $row['headline']; ?></a></td>
			<td><a href="index.php?act=projects.showProject&amp;id=<?php echo $row['projectId']; ?>"><?php echo $row['name']; ?></a></td>
			<td><?php printf( $this->__('text.full_name'), $this->escape($row['firstname']), $this->escape($row['lastname'])); ?></td>
			<td><?php echo $lang[$row['kind']]; ?></td>
			<td><?php echo $row['description']; ?></td>
			<td><?php if($row['invoicedEmpl'] == '1'){?> <?php echo $helper->timestamp2date($row['invoicedEmplDate'], 2); ?>
			<?php }else{ ?>  <?php } ?></td>
			<td><?php if($row['invoicedComp'] == '1'){?> <?php echo $helper->timestamp2date($row['invoicedCompDate'], 2); ?>
			<?php }else{ ?> <?php } ?></td>
		</tr>
		<?php } ?>
		<?php if(count($this->get('allTimesheets')) === 0): ?>
		<tr>
			<td colspan="8"><?php echo $this->__('NO_RESULTS'); ?></td>
		</tr>
		<?php endif; ?>
	</tbody>
	<tfoot>
		<tr>
			<td colspan="2"><strong><?php echo $this->__('ALL_HOURS') ?>:</strong></td>
			<td colspan="8"><strong><?php echo $sum; ?></strong></td>
			<td colspan="2"></td>
		</tr>
	</tfoot>
</table>


<script type='text/javascript'>

	jQuery(document).ready(function(){
		jQuery('#toggleBody').click(function(){
			jQuery('#body').toggle();
		});
	});

</script>
