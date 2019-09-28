<?php
defined('RESTRICTED') or die('Restricted access');

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
<link rel='stylesheet' type='text/css' href='includes/libs/fullCalendar/fullcalendar.css' />
<h1><?php echo $lang['ALL_GCCALS']; ?></h1>
<div id="loader">&nbsp;</div>
<form action="">
<br /><br />


<div id="pager"><span class="prev button">&laquo; <?php echo $lang['BACK']; ?></span>

- <input class="pagedisplay" type="text" /> - <span class="next button"><?php echo $lang['NEXT']; ?>
&raquo;</span> <select class="pagesize">
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
            <th>Id</th>
            <th><?php echo $lang['NAME']; ?></th>
            
            <th><?php echo $lang['COLOR']; ?></th>
        </tr>
    </thead>

    <tbody>

    <?php foreach($this->get('allCalendars') as $row) { ?>
        <tr>
            <td><a href="?act=calendar.editGCal&amp;id=<?php echo $row['id'] ?>"><?php echo $row['id']; ?></a></td>
            <td><a href="?act=calendar.editGCal&amp;id=<?php echo $row['id'] ?>"><?php echo $row['name']; ?></a></td>
            
            <td><span class="<?php echo $row['colorClass']; ?>" style="padding:2px;"><?php echo $row['colorClass']; ?></span></td>
        </tr>
    <?php } ?>

    </tbody>
</table>

<br />
<a
    href="index.php?act=calendar.importGCal" class="link">Google Kalender hinzufügen</a>
<a href="index.php?act=calendar.showMyCalendar" class="link">zurück</a>

</form>
