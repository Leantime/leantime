<?php
defined( 'RESTRICTED' ) or die( 'Restricted access' );
?>
<script type="text/javascript">
	$(document).ready(function() 
    	{ 
        	$("#allTickets").tablesorter({
            	sortList:[[0,0]],
            	widgets: ['zebra']
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

<h1><?php echo $lang['ALL_PROJECTS']; ?></h1>
<div id="loader">&nbsp;</div>


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
			<th><?php echo $lang['NAME']; ?></th>
			<th><?php echo $lang['CLIENT']; ?></th>
			<th><?php echo $lang['NUMBER_OF_TICKETS']; ?></th>
		</tr>
	</thead>
	<tbody>

	<?php foreach($this->get('allProjects') as $row) { ?>

		<tr>
			<td><a
				href="?act=projects.showProject&amp;id=<?php echo $row['id']; ?>"><?php echo $row['name']; ?></a></td>
			<td><?php if($this->get('role') != 'client'){ ?> <a
				href="?act=clients.showClient&amp;id=<?php echo $row['clientId']; ?>"><?php echo $row['clientName']; ?></a>
				<?php }else{ ?> <?php echo $row['clientName']; ?> <?php } ?></td>


			<td><?php echo $row['numberOfTickets']; ?></td>
		</tr>

		<?php } ?>

	</tbody>
</table>



		<?php if($this->get('role') === 'admin'){ ?>
<br />
<a
	href="index.php?act=projects.newProject" class="link"><?php echo $lang['NEW_PROJECT']; ?></a>

		<?php } ?>