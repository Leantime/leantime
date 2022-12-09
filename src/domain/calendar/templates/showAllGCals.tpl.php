<?php
defined('RESTRICTED') or die('Restricted access');

?>

<script type="text/javascript">

    <?php $this->dispatchTplEvent('scripts.afterOpen'); ?>

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

    <?php $this->dispatchTplEvent('scripts.beforeClose'); ?>

</script>
<link rel='stylesheet' type='text/css' href='includes/libs/fullCalendar/fullcalendar.css' />

<?php $this->dispatchTplEvent('beforePageHeaderOpen'); ?>
<div class="pageheader">
    <?php $this->dispatchTplEvent('afterPageHeaderOpen'); ?>
    <form action="<?=BASE_URL ?>/index.php?act=tickets.showAll" method="post" class="searchbar">
        <input type="text" name="term" placeholder="To search type and hit enter..." />
    </form>

    <div class="pageicon"><span class="fa <?php echo $this->getModulePicture() ?>"></span></div>
    <div class="pagetitle">
        <h5><?php echo $this->__('OVERVIEW'); ?></h5>
        <h1><?php echo $this->__('ALL_GCCALS'); ?></h1>
    </div>
    <?php $this->dispatchTplEvent('beforePageHeaderClose'); ?>
</div><!--pageheader-->
<?php $this->dispatchTplEvent('afterPageHeaderClose'); ?>

<div class="maincontent">
    <div class="maincontentinner">
        <form action="">

            <?php $this->dispatchTplEvent('afterFormOpen'); ?>

            <?php echo $this->displayLink('calendar.importGCal', $this->__('GOOGLE_CALENDAR_IMPORT'), null, array('class' => 'btn btn-primary btn-rounded')) ?>

            <table cellpadding="0" cellspacing="0" border="0" class="allTickets table table-bordered"
                id="allTickets">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th><?php echo $this->__('NAME'); ?></th>
                        <th><?php echo $this->__('URL'); ?></th>
                        <th><?php echo $this->__('COLOR'); ?></th>
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

            <?php $this->dispatchTplEvent('beforeFormClose'); ?>

        </form>
