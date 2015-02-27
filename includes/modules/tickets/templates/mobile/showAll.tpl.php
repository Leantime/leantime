<?php
defined( 'RESTRICTED' ) or die( 'Restricted access' );

$tickets = $this->get('objTickets');
$helper = $this->get('helper');

?>
<script type="text/javascript">

	$(document).ready(function() 
    	{ 
		    		
        	$("#allTickets").tablesorter({
            	sortList:[[0,1]],
            	widgets: ['zebra']
            }).tablesorterPager({container: $("#pager"), size: 5}); 

        	
        	
        	//assign the sortStart event 
            $("#allTickets").bind("sortStart",function() { 

            	$('#loader').show();
            	

            }).bind("sortEnd",function() { 

            	$('#loader').hide();
              	
           }); 
    	} 
	); 
    
</script>

<h1><?php echo $lang['ALL_TICKETS']; ?></h1>

<div id="loader">&nbsp;</div>

<form action="index.php?act=tickets.showAll" method="post">


<br />



<br />
<div id="pager"><span class="prev button">&laquo;<?php echo $lang['BACK']; ?></span>

- <input class="pagedisplay" type="text" readonly="readonly" /> - <span
	class="next button"><?php echo $lang['NEXT']; ?> &raquo;</span> <select
	class="pagesize">
	<option value="5" selected="selected">5</option>
	<option value="10" >10</option>
	<option value="25">25</option>
	<option value="50">50</option>
	<option value="100">100</option>
</select></div>





<table cellpadding="0" cellspacing="0" border="0" class="allTickets"
	id="allTickets">
	<thead>
		<tr>
			
			<th><?php echo $lang['TITLE']; ?></th>
			<th><?php echo $lang['CLIENT_PROJECT']; ?></th>
			<th><?php echo $lang['STATUS']; ?></th>
			<th><?php echo $lang['PRIORITY']; ?></th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->get('allTickets') as $row) {?>
		<tr>
			<td><a
				href="?act=tickets.showTicket&amp;id=<?php echo $row['id']; ?>"><?php echo $row['headline']; ?></a></td>
			<td><?php echo $row['clientName'].' / '.$row['projectName']; ?></td>
			<td><span
				class="<?php echo strtolower($tickets->getStatus($row['status']));?>"><?php echo $lang[$tickets->getStatus($row['status'])]; ?></span></td>
			<td><?php echo $lang[$tickets->getPriority($row['priority'])]; ?></td>
		</tr>
		<?php } ?>

	</tbody>
</table>



<br />

<label for="projects"><?php echo $lang['FILTER_SORT']; ?></label> <br />
<select
	name="projects" id="projects" onchange="submit();">
	<option value="null"><?php echo $lang['NO_FILTER']; ?></option>

	<?php foreach($this->get('allProjects') as $row) {
		echo'<option value="'.$row['id'].'"';
		if($row['id'] == $this->get('filter')) echo' selected="selected" ';
		echo'>'.$row['name'].'</option>';
	}
	?>
</select><br /><br />

<input type="hidden" id="closedTickets" name="closedTickets" value=""/>

<input type="hidden" name="term" id="term" value=""/>
<br /><br />
<a href="index.php?act=tickets.newTicket" class="link"><?php echo $lang['NEW_TICKET']; ?></a>

</form>
