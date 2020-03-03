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


<div class="pageheader">
            <form action="<?=BASE_URL ?>/index.php?act=tickets.showAll" method="post" class="searchbar">
                <input type="text" name="term" placeholder="To search type and hit enter..." />
            </form>
            
            <div class="pageicon"><span class="<?php echo $this->getModulePicture() ?>"></span></div>
            <div class="pagetitle">
                <h5><?php echo $this->__('OVERVIEW'); ?></h5>
                <h1><?php echo $this->__('ALL_GCCALS'); ?></h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">
<form action="">


<?php echo $this->displayLink('calendar.importGCal', $this->__('GOOGLE_CALENDAR_IMPORT'), null, array('class' => 'btn btn-primary btn-rounded')) ?>

<table cellpadding="0" cellspacing="0" border="0" class="allTickets table table-bordered"
    id="allTickets">
    <thead>
        <tr>
            <th>Id</th>
            <th><?php echo $lang['NAME']; ?></th>
            <th><?php echo $lang['URL']; ?></th>
            <th><?php echo $lang['COLOR']; ?></th>
        </tr>
    </thead>

    <tbody>

    <?php foreach($this->get('allCalendars') as $row) { ?>
        <tr>
            <td><?php echo $this->displayLink('calendar.editGCal', $row['id'], array('id' => $row['id'])) ?></td>
            <td><?php echo $this->displayLink('calendar.editGCal', $row['name'], array('id' => $row['id'])) ?></a></td>
            <td><?php echo $row['url']; ?></a></td>
            <td><span class="color: <?php echo $row['colorClass']; ?>" style="padding:2px;"><?php echo $row['colorClass']; ?></span></td>
        </tr>
    <?php } ?>

    </tbody>
</table>

</fieldset>

</form>
