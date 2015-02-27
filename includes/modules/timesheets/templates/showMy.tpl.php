<?php
defined( 'RESTRICTED' ) or die( 'Restricted access' );


$helper = $this->get('helper');
?>
<script	src="includes/modules/general/templates/js/tableHandling.js" type="text/javascript"></script>
<script type="text/javascript">

    
</script>


<div class="pageheader">
            <form action="index.php?act=tickets.showAll" method="post" class="searchbar">
                <input type="text" name="term" placeholder="To search type and hit enter..." />
            </form>
            
            <div class="pageicon"><span class="iconfa-laptop"></span></div>
            <div class="pagetitle">
                <h5><?php echo $language->lang_echo('OVERVIEW'); ?></h5>
                <h1><?php echo $language->lang_echo('MY_TIMESHEETS'); ?></h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">


<form action="index.php?act=timesheets.showMy" method="post">
<!--
<div id="loader">&nbsp;</div>

<div class="right">

<div id="pager"><span class="prev button">&laquo;<?php echo $lang['BACK']; ?></span>

- <input class="pagedisplay" type="text" readonly="readonly" /> - <span
	class="next button"><?php echo $lang['NEXT']; ?> &raquo;</span> 
<select class="pagesize">
	<option value="5">5</option>
	<option value="10" selected="selected">10</option>
	<option value="25">25</option>
	<option value="50">50</option>
	<option value="100">100</option>
</select>
</div>

</div>
-->
<table class='table table-bordered' cellpadding="0" cellspacing="0" border="0" class="table table-bordered" id="dyntable2">
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
			<th><?php echo $lang['ID']; ?></th>
			<th><?php echo $lang['DATE']; ?></th>
			<th><?php echo $lang['HOURS']; ?></th>
			<th><?php echo $lang['BILLABLE_HOURS'] ?></th>
			<th><?php echo $lang['PLANHOURS']; ?></th>
			<th><?php echo $lang['DIFFERENCE_HOURS']; ?></th>
			<th><?php echo $lang['TICKET']; ?></th>
			<th><?php echo $lang['PROJECT']; ?></th>
			<th><?php echo $lang['KIND']; ?></th>
			<th><?php echo $lang['DESCRIPTION']; ?></th>
			
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
			
		</tr>
	</thead>
	<tbody>

	<?php
	$sum = 0;
	$billableSum = 0;
	foreach($this->get('allTimesheets') as $row) {
		$sum = $sum + $row['hours'];?>
		<tr>
			<td>
				<a href="/timesheets/editTime/<?php echo $row['id']; ?>"><?php echo $row['id']; ?></a>
			</td>
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
			<td><a href="/tickets/showTicket/<?php echo $row['ticketId']; ?>"><?php echo $row['headline']; ?></a></td>
			<td><a href="/projects/showProject/<?php echo $row['projectId']; ?>"><?php echo $row['name']; ?></a></td>
			<td><?php echo $lang[$row['kind']]; ?></td>
			<td><?php echo $row['description']; ?></td>
			
		</tr>
		<?php } ?>
		<?php if(count($this->get('allTimesheets')) === 0){ ?>
		<tr>
			<td colspan="12"><?php echo $lang['NO_RESULTS']; ?></td>
		</tr>

		<?php } ?>
	</tbody>
	<tfoot>
		<tr>
			<td colspan="2"><strong><?php echo $lang['ALL_HOURS']; ?>:</strong></td>
			<td colspan="1"><strong><?php echo $sum; ?></strong></td>
			<td colspan="7"><strong><?php echo $billableSum; ?></strong></td>
			
			</td></td>
		</tr>
	</tfoot>
</table>

</div>
</form>

					</div>
				</div>
