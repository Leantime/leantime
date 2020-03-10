<?php
defined( 'RESTRICTED' ) or die( 'Restricted access' );


$helper = $this->get('helper');
?>
<script type="text/javascript">

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
	
	$(document).ready(function() 
    	{ 

			$("#dateFrom, #dateTo").datepicker({

                dateFormat: <?php echo $this->__('language.dateFormat') ?>,
                dayNames: [<?php echo''.$this->__('language.dayNames').'' ?>],
                dayNamesMin:  [<?php echo''.$this->__('language.dayNamesMin').'' ?>],
                monthNames: [<?php echo''.$this->__('language.monthNames').'' ?>]
			});
		
        	$("#allTickets").tablesorter({
            	sortList:[[1,1]],
            	widgets: ['zebra'],
            	headers:{
            		1: {sorter:'germandate'}
    				
				}
            }).tablesorterPager({container: $("#pager")});

        	//assign the sortStart event 
            $("#allTickets").bind("sortStart",function() { 

            	$('#loader').show();
            	

            }).bind("sortEnd",function() { 

            	$('#loader').hide();
              	
           });

           
			
        } 
	); 
    
</script>



<h1><?php echo $this->__('headlines.all_times') ?></h1>
<div id="loader">&nbsp;</div>
<form action="<?=BASE_URL ?>/index.php?act=timesheets.showAll" method="post" id="form" name="form">




<div id="pager"><span class="prev button">&laquo;<?php echo $this->__('buttons.back') ?></span>

- <input class="pagedisplay" type="text" readonly="readonly" /> - <span
	class="next button"><?php echo $this->__('language.nextText') ?> &raquo;</span> <select
	class="pagesize">
	<option value="5">5</option>
	<option value="10" selected="selected">10</option>
	<option value="25">25</option>
	<option value="50">50</option>
	<option value="100">100</option>
</select></div>



<table cellpadding="0" cellspacing="0" border="0" class="allTickets"
	id="allTickets">
	<thead>
		<tr>
			<th><?php echo $this->__('label.id'); ?></th>
            <th><?php echo $this->__('label.date'); ?></th>
            <th><?php echo $this->__('label.hours'); ?></th>
            <th><?php echo $this->__('label.plan_hours'); ?></th>
            <th><?php echo $this->__('label.difference'); ?></th>
			
		</tr>
	</thead>
	<tbody>

	<?php
	$sum = 0;
	foreach($this->get('allTimesheets') as $row) {
		$sum = $sum + $row['hours'];?>
		<tr>
			<td><a href="index.php?act=timesheets.editTime&amp;id=<?php echo $row['id']; ?>"><?php echo $row['id']; ?></a></td>
			<td><a href="index.php?act=timesheets.editTime&amp;id=<?php echo $row['id']; ?>"><?php echo $helper->timestamp2date($row['workDate'], 2); ?></a></td>
			<td><?php echo $row['hours']; ?></td>
			<td><?php echo $row['planHours']; ?></td>
			<?php $diff = $row['planHours']-$row['hours']; ?>
			<td <?php if($diff<0)echo'class="new" ';?>><?php echo $diff; ?></td>
			
		</tr>
		<?php } ?>
		<?php if(count($this->get('allTimesheets')) === 0){ ?>
		<tr>
			<td colspan="5"><p><?php echo $this->__('label.no_results') ?></p></td>
		</tr>

		<?php } ?>
	</tbody>
	<tfoot>
		<tr>
			<td colspan="2"><strong><?php echo $this->__('label.all_hours'); ?>:</strong></td>
			<td colspan="3"><strong><p><?php echo $sum; ?></p></strong></td>
		</tr>
	</tfoot>
</table>



<fieldset><legend><?php echo $this->__('label.filter'); ?></legend>

<label for="dateFrom"><?php echo $this->__('label.date_from'); ?></label><br />
<input type="text" id="dateFrom" name="dateFrom"
			value="<?php echo $this->get('dateFrom'); ?>" size="7" /><br />
			
			
<label for="dateTo"><?php echo $this->__('label.date_to'); ?></label><br />
<input type="text" id="dateTo" name="dateTo"
			value="<?php echo $this->get('dateTo'); ?>" size="7" /><br />
			

<label for="projectFilter"><?php echo $this->__('label.project'); ?></label><br />
<select name="projectFilter" id="projectFilter"
			onchange="submit();">
			<option value="0"><?php echo $this->__('headline.all_projects'); ?></option>

			<?php foreach($this->get('allProjects') as $row) {
				echo'<option value="'.$row['id'].'"';
				if($row['id'] == $this->get('projectFilter')) echo' selected="selected" ';
				echo'>'.$row['name'].'</option>';
			}

			?>
		</select>
		<br />
		<label for="userId"><?php echo $this->__('label.employee'); ?></label><br />
		<select name="userId" id="userId" onchange="submit();">
			<option value="all"><?php echo $this->__('label.all_employees'); ?></option>

			<?php foreach($this->get('employees') as $row) {
				echo'<option value="'.$row['id'].'"';
				if($row['id'] == $this->get('employeeFilter')) echo' selected="selected" ';
				echo'>'.$row['lastname'].', '.$row['firstname'].'</option>';
			}

			?>
		</select><br />
		<label for="kind"><?php echo $this->__('label.kind'); ?></label><br />
		<select id="kind" name="kind" onchange="submit();">
			<option value="all"><?php echo $this->__('label.all_kinds') ?></option>
			<?php foreach($this->get('kind') as $row){
				echo'<option value="'.$row.'"';
				if($row == $this->get('actKind')) echo ' selected="selected"';
				echo'>'.$lang[$row].'</option>';

			}
			?>

		</select> 
		<br />
		<label for="invEmpl"><?php echo $this->__('label.invoiced'); ?></label><br />
		<input type="checkbox" value="on" name="invEmpl" id="invEmpl" onchange="submit();" 
			<?php 
			if($this->get('invEmpl') == '1') echo ' checked="checked"';
			?>
		/><br />
		<label for="invEmpl"><?php echo $this->__('label.invoiced_comp'); ?></label><br /><br />
		<input type="checkbox" value="on" name="invComp" id="invComp" onchange="submit();" 
			<?php 
			if($this->get('invComp') == '1') echo ' checked="checked"';
			?>
		/><br /><br />
		<input type="submit" value="<?php echo $this->__('buttons.reload'); ?>" class="button" />
	
</fieldset>


<p>
<a href="index.php?act=timesheets.addTime" class="link"><?php echo $this->__('links.book_hours'); ?></a>
<a href="index.php?act=timesheets.showMy" class="link"><?php echo $this->__('links.my_timesheets'); ?></a>
</p>


</form>
