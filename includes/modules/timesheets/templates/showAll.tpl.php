<?php

defined( 'RESTRICTED' ) or die( 'Restricted access' );
$helper = $this->get('helper');
?>
<script type="text/javascript">
/*
	var checkflag = "false";
	
	function check(field) {
	if (checkflag == "false") {
	  for (i = 0; i < field.length; i++) {
	  field[i].checked = true;}
	  checkflag = "true";
	  return " keine "; }
	else {
	  for (i = 0; i < field.length; i++) {
	  field[i].checked = false; }
	  checkflag = "false";
	  return " alle "; }
	}			
        } 
	); */
    
</script>

<div class="pageheader">
            <form action="index.php?act=tickets.showAll" method="post" class="searchbar">
                <input type="text" name="term" placeholder="To search type and hit enter..." />
            </form>
            
            <div class="pageicon"><span class="<?php echo $this->getModulePicture() ?>"></span></div>
            <div class="pagetitle">
                <h5><?php echo $language->lang_echo('FILTER'); ?></h5>
                <h1><?php echo $language->lang_echo('ALL_TIMES'); ?></h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">


<div id="loader">&nbsp;</div>
<form action="/timesheets/showAll" method="post" id="form" name="form">

<table class='table table-bordered' cellpadding="0" cellspacing="0" width="90%" id="">
	<colgroup>
      	  <col class="con0"/>
          <col class="con1" />
      	  <col class="con0"/>
          <col class="con1" />
      	  <col class="con0"/>
	</colgroup>
	<thead>
		<tr id='toggleBody'>
			<th><label for="dateFrom"><?php echo $language->lang_echo('DATE_FROM'); ?></label></th>
			<th><label for="dateTo"><?php echo $language->lang_echo('DATE_TO'); ?></label></th>
			<th><label></label></th>
			<th><label></label></th>
			<th><br/><span class='caret'></span></th>
		</tr>
	</thead>
	<tbody id='body'>
		<tr>
			<td><input type="text" id="dateFrom" name="dateFrom"
				value="<?php echo $this->get('dateFrom'); ?>" size="7" /></td>
			<td><input type="text" id="dateTo" name="dateTo"
				value="<?php echo $this->get('dateTo'); ?>" size="7" /></td>
			<td>
			
			<label for="projectFilter"><?php echo $lang['PROJECT']; ?></label>
			<select name="projectFilter" id="projectFilter"
				onchange="submit();">
				<option value="0"><?php echo $lang['ALL_PROJECTS']; ?></option>
	
				<?php foreach($this->get('allProjects') as $row) {
					echo'<option value="'.$row['id'].'"';
					if($row['id'] == $this->get('projectFilter')) echo' selected="selected" ';
					echo'>'.$row['name'].'</option>';
				}
	
				?>
			</select>
			<br />
			<label for="userId"><?php echo $lang['EMPLOYEE']; ?></label>
			<select name="userId" id="userId" onchange="submit();">
				<option value="all"><?php echo $lang['ALL_EMPLOYEES']; ?></option>
	
				<?php foreach($this->get('employees') as $row) {
					echo'<option value="'.$row['id'].'"';
					if($row['id'] == $this->get('employeeFilter')) echo' selected="selected" ';
					echo'>'.$row['lastname'].', '.$row['firstname'].'</option>';
				}
	
				?>
			</select>
			<br />
			<label for="kind"><?php echo $lang['KIND']; ?></label>
			<select id="kind" name="kind" onchange="submit();">
				<option value="all"><?php echo $lang['ALL_KINDS']; ?></option>
				<?php foreach($this->get('kind') as $row){
					echo'<option value="'.$row.'"';
					if($row == $this->get('actKind')) echo ' selected="selected"';
					echo'>'.$lang[$row].'</option>';
	
				}
				?>
	
			</select> </td>
			<td>
			<label for="invEmpl"><?php echo $lang['INVOICED']; ?></label>
			<input type="checkbox" value="on" name="invEmpl" id="invEmpl" onclick="submit();" 
				<?php 
				if($this->get('invEmpl') == '1') echo ' checked="checked"';
				?>
			/><br />
			<label for="invEmpl"><?php echo $lang['INVOICED_COMP']; ?></label>
			<input type="checkbox" value="on" name="invComp" id="invComp" onclick="submit();" 
				<?php 
				if($this->get('invComp') == '1') echo ' checked="checked"';
				?>
			/>
			</td>
			<td>
				<input type="submit" value="<?php echo $language->lang_echo('FILTER') ?>" class="reload" />
				<input type="submit" value="<?php echo $language->lang_echo('EXPORT') ?>" name="export" class="reload" />
			</td>
		</tr>
	</tbody>
</table>

<h3><?php echo $lang['OVERVIEW']; ?></h3>

<div class="right">
<!--
<div id="pager"><span class="prev button">&laquo;<?php echo $lang['BACK']; ?></span>

- <input class="pagedisplay" type="text" readonly="readonly" /> - <span
	class="next button"><?php echo $lang['NEXT']; ?> &raquo;</span> <select
	class="pagesize">
	<option value="5">5</option>
	<option value="10" selected="selected">10</option>
	<option value="25">25</option>
	<option value="50">50</option>
	<option value="100">100</option>
</select></div>
-->
</div>

<table class='table table-bordered' cellspacing="0" border="0" class="display" id="">
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
      	  <col class="con0"/>
	</colgroup>
	<thead>
		<tr>
			<th><?php echo $language->lang_echo('ID'); ?></th>
			<th><?php echo $language->lang_echo('DATE'); ?></th>
			<th><?php echo $language->lang_echo('HOURS'); ?></th>
			<th><?php echo $language->lang_echo('BILLABLE_HOURS'); ?></th>
			<th><?php echo $language->lang_echo('PLANHOURS'); ?></th>
			<th><?php echo $language->lang_echo('DIFFERENCE_HOURS'); ?></th>
			<th><?php echo $language->lang_echo('TICKET'); ?></th>
			<th><?php echo $language->lang_echo('PROJECT'); ?></th>
			<th><?php echo $language->lang_echo('EMPLOYEE'); ?></th>
			<th><?php echo $language->lang_echo('KIND'); ?></th>
			<th><?php echo $language->lang_echo('DESCRIPTION'); ?></th>
			<th><?php echo $language->lang_echo('INVOICED'); ?></th>
			<th><?php echo $language->lang_echo('INVOICED_COMP'); ?></th>
		</tr>
		<tr class='filter'>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
		</tr>
	</thead>
	<tbody>

	<?php
	
	$sum = 0;
	$billableSum = 0;
	
	foreach($this->get('allTimesheets') as $row) {
		$sum = $sum + $row['hours'];?>
		<tr>
			<td><a href="index.php?act=timesheets.editTime&amp;id=<?php echo $row['id']; ?>"><?php echo $row['id']; ?></a></td>
			<td><?php echo $helper->timestamp2date($row['workDate'], 2); ?></td>
			<td><?php echo $row['hours']; ?></td>
			<td>
			<?php 
				if ($row['kind'] != 'GENERAL_NOT_BILLABLE' && $row['kind'] != 'BUGFIXING_NOT_BILLABLE') {
					echo $row['hours'];
					$billableSum += $row['hours'];
				}
			?>
			</td>
			<td><?php echo $row['planHours']; ?></td>
			<?php $diff = $row['planHours']-$row['hours']; ?>
			<td <?php if($diff<0)echo'class="new" ';?>><?php echo $diff; ?></td>
			<td><a href="index.php?act=tickets.showTicket&amp;id=<?php echo $row['ticketId']; ?>"><?php echo $row['headline']; ?></a></td>
			<td><a href="index.php?act=projects.showProject&amp;id=<?php echo $row['projectId']; ?>"><?php echo $row['name']; ?></a></td>
			<td><?php echo $row['firstname']; ?>, <?php echo $row['lastname']; ?></td>
			<td><?php echo $row['kind']; ?></td>
			<td><?php echo $row['description']; ?></td>
			<td><?php if($row['invoicedEmpl'] == '1'){?> <?php echo $helper->timestamp2date($row['invoicedEmplDate'], 2); ?>
			<?php }else{ ?> <input type="checkbox" name="invoicedEmpl[]" class="invoicedEmpl"
				value="<?php echo $row['id']; ?>" /> <?php } ?></td>
			<td><?php if($row['invoicedComp'] == '1'){?> <?php echo $helper->timestamp2date($row['invoicedCompDate'], 2); ?>
			<?php }else{ ?> <input type="checkbox" name="invoicedComp[]" class="invoicedComp"
				value="<?php echo $row['id']; ?>" /> <?php } ?></td>
		</tr>
		<?php } ?>
		<?php if(count($this->get('allTimesheets')) === 0){ ?>
		<tr>
			<td colspan="13"><?php echo $lang['NO_RESULTS']; ?></td>
		</tr>

		<?php } ?>
	</tbody>
	<tfoot>
		<tr>
			<td colspan="2"><strong><?php echo $lang['ALL_HOURS']; ?>:</strong></td>
			<td colspan="1"><strong><?php echo $sum; ?></strong></td>
			<td colspan="7"><strong><?php echo $billableSum; ?></strong></td>
			<td>
				<input type="submit" class="button" value="<?php echo $lang['SAVE']; ?>" name="saveInvoice" />
			</td>
			<td><a href="javascript:void(0);" onClick="check($('.invoicedEmpl'))" >↑ Select all</a></td>
			<td><a href="javascript:void(0);" onClick="check($('.invoicedComp'))" >↑ Select all</a></td>
		</tr>
	</tfoot>
</table>

</form>


<script type='text/javascript'>
	jQuery(document).ready(function() {
		jQuery('#toggleBody').click(function() {
			jQuery('#body').toggle();	
		});;
	});
</script>

			</div>
		</div>