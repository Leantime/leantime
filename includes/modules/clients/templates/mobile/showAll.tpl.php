<?php

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


<h1><?php echo $lang['ALL_CLIENTS']; ?></h1>
<div id="loader">&nbsp;</div>
<form action="">

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
			<th><?php echo $lang['CLIENT_ID']; ?></th>
			<th><?php echo $lang['CLIENTNAME']; ?></th>
			<th><?php echo $lang['URL']; ?></th>
			<th><?php echo $lang['NUMBER_PROJECTS']; ?></th>
		</tr>
	</thead>
	<tbody>

	<?php foreach($this->get('allClients') as $row) { ?>
		<tr>
			<td><a
				href="?act=clients.showClient&amp;id=<?php echo $row['id']; ?>"><?php echo $row['id']; ?></a></td>
			<td><a
				href="?act=clients.showClient&amp;id=<?php echo $row['id']; ?>"><?php echo $row['name']; ?></a></td>
			<td><a href="http://<?php echo $row['internet']; ?>" target="_blank"><?php echo $row['internet']; ?></a></td>
			<td><?php echo $row['numberOfProjects']; ?></td>
		</tr>
		<?php } ?>

	</tbody>
</table>




		<?php if($this->get('admin') === true){ ?>
<br />
<a	href="index.php?act=clients.newClient" class="link"><?php echo $lang['ADD_NEW_CLIENT']; ?></a>

		<?php } ?></form>
