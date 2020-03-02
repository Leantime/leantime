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
				
				dateFormat: 'dd.mm.yy',
				dayNames: [<?php echo''.$lang['DAYNAMES'].'' ?>],
				dayNamesMin:  [<?php echo''.$lang['DAYNAMES_MIN'].'' ?>],
				monthNames: [<?php echo''.$lang['MONTHS'].'' ?>]
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


<h1><?php echo $lang['MY_TIMESHEETS']; ?></h1>




<div id="loader">&nbsp;</div>
<form action="<?=BASE_URL ?>/index.php?act=timesheets.showMy" method="post">


<br /><br />


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



<table cellpadding="0" cellspacing="0" border="0" class="allTickets"
	id="allTickets">
	<thead>
		<tr>
			<th><?php echo $lang['ID']; ?></th>
			<th><?php echo $lang['DATE']; ?></th>
			<th><?php echo $lang['HOURS']; ?></th>
			<th><?php echo $lang['PLANHOURS']; ?></th>
			<th><?php echo $lang['DIFFERENCE_HOURS']; ?></th>
		</tr>
	</thead>
	<tbody>

	<?php
	$sum = 0;
	foreach($this->get('allTimesheets') as $row) {
		$sum = $sum + $row['hours'];?>
		<tr>
			<td><a
				href="index.php?act=timesheets.editTime&amp;id=<?php echo $row['id']; ?>"><?php echo $row['id']; ?></a></td>
			<td><a
				href="index.php?act=timesheets.editTime&amp;id=<?php echo $row['id']; ?>"><?php echo $helper->timestamp2date($row['workDate'], 2); ?></a></td>
			<td><?php echo $row['hours']; ?></td>
			<td><?php echo $row['planHours']; ?></td>
			<?php $diff = $row['planHours']-$row['hours']; ?>
			<td <?php if($diff<0)echo'class="new" ';?>><?php echo $diff; ?></td>
			

		</tr>
		<?php } ?>
		<?php if(count($this->get('allTimesheets')) === 0){ ?>
		<tr>
			<td colspan="5"><p><?php echo $lang['NO_RESULTS']; ?></p></td>
		</tr>

		<?php } ?>
	</tbody>
	<tfoot>
		<tr>
			<td colspan="2"><strong><?php echo $lang['ALL_HOURS']; ?>:</strong></td>
			<td colspan="3"><strong><?php echo $sum; ?></strong></td>
			
		</tr>
	</tfoot>
</table>


<fieldset><legend><?php echo $lang['FILTER']; ?></legend>

<label for="dateFrom"><?php echo $lang['DATE_FROM']; ?></label><br />
<input type="text" id="dateFrom" name="dateFrom"
			value="<?php echo $this->get('dateFrom'); ?>" size="7" /><br />
			
			
<label for="dateTo"><?php echo $lang['DATE_TO']; ?></label><br />
<input type="text" id="dateTo" name="dateTo"
			value="<?php echo $this->get('dateTo'); ?>" size="7" /><br />
			

<label for="projectFilter"><?php echo $lang['PROJECT']; ?></label><br />
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
		<label for="kind"><?php echo $lang['KIND']; ?></label><br />
		<select id="kind" name="kind" onchange="submit();">
			<option value="all"><?php echo $lang['ALL_KINDS']; ?></option>
			<?php foreach($this->get('kind') as $row){
				echo'<option value="'.$row.'"';
				if($row == $this->get('actKind')) echo ' selected="selected"';
				echo'>'.$lang[$row].'</option>';

			}
			?>

		</select> 
		<br />
		<label for="invEmpl"><?php echo $lang['INVOICED']; ?></label><br />
		<input type="checkbox" value="on" name="invEmpl" id="invEmpl" onchange="submit();" 
			<?php 
			if($this->get('invEmpl') == '1') echo ' checked="checked"';
			?>
		/><br />
		<label for="invEmpl"><?php echo $lang['INVOICED_COMP']; ?></label><br /><br />
		<input type="checkbox" value="on" name="invComp" id="invComp" onchange="submit();" 
			<?php 
			if($this->get('invComp') == '1') echo ' checked="checked"';
			?>
		/><br /><br />
		<input type="submit" value="<?php echo $lang['RELOAD']; ?>" class="button" />
	
</fieldset>

<a href="index.php?act=timesheets.addTime" class="link"><?php echo $lang['BOOK_HOURS']; ?></a>
<?php if($this->get('admin') === true) {?> <a
	href="index.php?act=timesheets.showAll" class="link"><?php echo $lang['ALL_TIMES']; ?></a>

	<?php } ?> 



</form>
