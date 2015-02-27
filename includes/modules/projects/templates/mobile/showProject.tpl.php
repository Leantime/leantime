<?php
defined( 'RESTRICTED' ) or die( 'Restricted access' );
$project = $this->get('project');
$helper = $this->get('helper');
$state = $this->get('state');

?>

<script type="text/javascript">
	$(document).ready(function() 
    	{ 
		 	toggleCommentBoxes(0);
		 
			$('#tabs').tabs();
        	
			$('#commentList').pager('div');
 			
			$("#progressbar").progressbar({
				value: <?php echo $this->get('projectPercentage') ?>
			});
		
			$("#accordion").accordion({
				autoHeight: false,
				navigation: true
			});

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

function toggleCommentBoxes(id){
		
		$('.commentBox').hide('fast',function(){

			$('.commentBox textarea').remove(); 

			$('#comment'+id+'').prepend('<textarea rows="5" cols="30" name="text"></textarea>');
			
				
				
				
		}); 

		$('#comment'+id+'').show('slow');		

		
	}
</script>

<h1><?php echo $project['name']; ?></h1>

			<?php if($this->get('info') != ''){ ?>
<div class="fail"><span class="info"><?php echo $lang[$this->get('info')]; ?></span>
</div>
			<?php } ?>

<div id="tabs">

<ul>
	<li><a href="#projectdetails"><?php echo $lang['PROJECT_DETAILS']; ?></a></li>
	<li><a href="#progress"><?php echo $lang['PROGRESS']; ?></a></li>

	<?php if($this->get('role') !== 'client'){ ?>
	<li><a href="#accounts"><?php echo $lang['ACCOUNTS']; ?></a></li>
	<?php } ?>

	<?php if($this->get('role') == 'admin'){ ?>
	<li><a href="#timesheets"><?php echo $lang['TIMESHEET']; ?></a></li>
	<?php } ?>

	<li><a href="#files"><?php echo $lang['PROJECT_FILES']; ?> (<?php echo $this->get('numFiles'); ?>)</a></li>
	<li><a href="#comments"><?php echo $lang['COMMENTS']; ?> (<?php echo $this->get('numComments'); ?>)</a></li>
</ul>

<div id="projectdetails">

<p><strong><?php echo $project['name']; ?></strong><br />
<br />
	<?php echo $project['details']; ?> <br />
</p>



</div>

<div id="progress">

<p><strong><?php printf($lang['PROJECT_IS_FOR_TICKETS'], $lang[$state[$project['state']]]) ?></strong><br />
<br />
<strong><?php echo $this->get('projectPercentage');?> % <?php echo $lang['IS_FINISHED']; ?></strong><br />
</p>

<div id="progressbar"></div>

<p><br />
<?php echo$lang['SUM_TICKETS']; ?>: <?php echo $project['numberOfTickets']; ?><br />
<?php echo$lang['SUM_OPEN_TICKETS']; ?>: <?php echo $this->get('openTickets'); ?><br />
</p>


</div>

<?php if($this->get('role') !== 'client'){ ?>

<div id="accounts">

<p><strong><?php echo $lang['ACCOUNTS']; ?></strong></p>

<div id="accordion"><?php foreach($this->get('accounts') as $rowAccount){ ?>

<h3><a href="javascript:void(0);"><?php echo $rowAccount['name']; ?></a></h3>
<div>
<p><?php echo $lang['ACCOUNT_KIND']; ?>: <?php echo $rowAccount['kind']; ?><br />
<?php echo $lang['ACCOUNT_USERNAME']; ?>: <?php echo $rowAccount['username']; ?><br />
<?php echo $lang['ACCOUNT_PASSWORD']; ?>: <?php echo $rowAccount['password']; ?><br />
<?php echo $lang['ACCOUNT_HOST']; ?>: <?php echo $rowAccount['host']; ?><br />
<br />

<?php if($this->get('role') == 'admin'){ ?> <a
	href="index.php?act=projects.showProject&amp;id=<?php echo $project['id']; ?>&amp;delAccount=<?php echo $rowAccount['id']; ?>"><?php echo $lang['DELETE_ACCOUNT']; ?></a>
<?php }?></p>
</div>
<?php } ?></div>

<?php if(empty($rowAccount)){ ?>
<p><?php echo $lang['NO_ACCOUNTS']; ?></p>
<?php } ?></div>

<?php } ?>

<div id="files"><?php foreach($this->get('files') as $rowFiles){ ?> <a
	href="userdata/<?php echo $rowFiles['encName']; ?>" target="_blank"><?php echo $rowFiles['realName']; ?></a><br />

<?php printf("<span class=\"grey\">".$lang['UPLOADED_BY_ON']."</span>", $rowFiles['firstname'], $rowFiles['lastname'], $helper->timestamp2date($rowFiles['date'], 2)); ?>

<?php if($this->get('role') === 'admin'){ ?> | <a
	href="index.php?act=projects.showProject&amp;id=<?php echo $project['id']; ?>&amp;delFile=<?php echo $rowFiles['encName']; ?>#anhanege"><?php echo $lang['DELETE']; ?></a>
<?php } ?> <br />
<hr />
<br />

<?php } ?> <?php if(count($this->get('files')) == 0){ ?> <?php echo $lang['ERROR_NO_FILES']; ?>
<?php } ?></div>

<div id="comments">

<form method="post" accept-charset="utf-8"
	action="index.php?act=projects.showProject&id=<?php echo $project['id']; ?>#comments">
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
		
		<?php printf("<small class=\"grey\">".$lang['WRITTEN_ON_BY']."</small>", $helper->timestamp2date($row['datetime'], 2), $helper->timestamp2date($row['datetime'], 1), $row['firstname'], $row['lastname']); ?>
		<br />
		<?php if($this->get('role') === 'admin'){ ?> | <a
			href="index.php?act=projects.showProject&amp;id=<?php echo $project['id']; ?>&amp;delComment=<?php echo $row['id']; ?>#commentList"><?php echo $lang['DELETE']; ?></a>
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

<?php if($this->get('role') == 'admin'){ ?>
<div id="timesheets"><strong><?php 
echo $lang['TIMES'];?> </strong> <?php echo $this->get('timesheetsAllHours'); ?>
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











<div id="loader">&nbsp;</div>


<table cellpadding="0" cellspacing="0" border="0" class="allTickets"
	id="allTickets">
	<thead>
		<tr>
			<th><?php echo $lang['ID']; ?></th>
			<th><?php echo $lang['DATE']; ?></th>
			<th><?php echo $lang['HOURS']; ?></th>
			<th><?php echo $lang['PLANHOURS']; ?></th>
			
			
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
			
			
		</tr>
		<?php } ?>
		<?php if(count($this->get('allTimesheets')) === 0){ ?>
		<tr>
			<td colspan="5"><?php echo $lang['NO_RESULTS']; ?></td>
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

<br /><br />

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
		<label for="userId"><?php echo $lang['EMPLOYEE']; ?></label><br />
		<select name="userId" id="userId" onchange="submit();">
			<option value="all"><?php echo $lang['ALL_EMPLOYEES']; ?></option>

			<?php foreach($this->get('employees') as $row) {
				echo'<option value="'.$row['id'].'"';
				if($row['id'] == $this->get('employeeFilter')) echo' selected="selected" ';
				echo'>'.$row['lastname'].', '.$row['firstname'].'</option>';
			}

			?>
		</select><br />
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
		<input type="submit" value="Neu laden" class="button" />
	
























</div>
</div>

<?php } ?> <?php if($this->get('role') === 'admin'){ ?>
<br /><br />

<a
	href="index.php?act=projects.editProject&id=<?php echo $project['id']; ?>" class="link"><?php echo $lang['EDIT']; ?></a>
<a
	href="index.php?act=projects.delProject&id=<?php echo $project['id']; ?>" class="link"><?php echo $lang['DELETE']; ?></a>

<a href="index.php?act=projects.showAll" class="link"><?php echo $lang['BACK']; ?></a>

<?php } ?>
