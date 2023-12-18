<?php
defined('RESTRICTED') or die('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
?>

<script type="text/javascript">

    <?php $tpl->dispatchTplEvent('scripts.afterOpen'); ?>

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

    <?php $tpl->dispatchTplEvent('scripts.beforeClose'); ?>

</script>
<link rel='stylesheet' type='text/css' href='includes/libs/fullCalendar/fullcalendar.css' />

<?php $tpl->dispatchTplEvent('beforePageHeaderOpen'); ?>
<div class="pageheader">
    <?php $tpl->dispatchTplEvent('afterPageHeaderOpen'); ?>
    <form action="<?=BASE_URL ?>/index.php?act=tickets.showAll" method="post" class="searchbar">
        <input type="text" name="term" placeholder="To search type and hit enter..." />
    </form>

    <div class="pageicon"><span class="fa <?php echo $tpl->getModulePicture() ?>"></span></div>
    <div class="pagetitle">
        <h5><?php echo $tpl->__('OVERVIEW'); ?></h5>
        <h1><?php echo $tpl->__('ALL_GCCALS'); ?></h1>
    </div>
    <?php $tpl->dispatchTplEvent('beforePageHeaderClose'); ?>
</div><!--pageheader-->
<?php $tpl->dispatchTplEvent('afterPageHeaderClose'); ?>

<div class="maincontent">
    <div class="maincontentinner">
        <form action="">

            <?php $tpl->dispatchTplEvent('afterFormOpen'); ?>


            <table cellpadding="0" cellspacing="0" border="0" class="allTickets table table-bordered"
                id="allTickets">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th><?php echo $tpl->__('NAME'); ?></th>
                        <th><?php echo $tpl->__('URL'); ?></th>
                        <th><?php echo $tpl->__('COLOR'); ?></th>
                    </tr>
                </thead>

                <tbody>

                <?php foreach ($tpl->get('allCalendars') as $row) { ?>
                    <tr>
                        <td><?php echo $tpl->displayLink('calendar.editGCal', $row['id'], array('id' => $row['id'])) ?></td>
                        <td><?php echo $tpl->displayLink('calendar.editGCal', $row['name'], array('id' => $row['id'])) ?></a></td>
                        <td><?php echo $row['url']; ?></a></td>
                        <td><span class="color: <?php echo $row['colorClass']; ?>" style="padding:2px;"><?php echo $row['colorClass']; ?></span></td>
                    </tr>
                <?php } ?>

                </tbody>
            </table>

            <?php $tpl->dispatchTplEvent('beforeFormClose'); ?>

        </form>
