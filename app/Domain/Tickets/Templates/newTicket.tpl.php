<?php
defined('RESTRICTED') or die('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$ticket = $tpl->get('ticket');

?>

<div class="pageheader">

    <div class="pull-right padding-top">
        <a href="<?php echo session("lastPage") ?>" class="backBtn"><i class="far fa-arrow-alt-circle-left"></i> <?=$tpl->__("links.go_back") ?></a>
    </div>

    <div class="pageicon"><span class="fa <?php echo $tpl->getModulePicture() ?>"></span></div>
    <div class="pagetitle">
        <h5><?php $tpl->e(session("currentProjectClient") . " // " . session("currentProjectName")); ?></h5>
        <h1><?=$tpl->__("headlines.new_to_do") ?></h1>
    </div>
</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner">

        <?php
            echo $tpl->displayNotification();
        ?>

        <div class="tabbedwidget tab-primary ticketTabs">

            <ul>
                <li>
                    <a href="#ticketdetails"><?php echo $tpl->__("tabs.ticketDetails") ?></a>
                </li>
            </ul>

            <div id="ticketdetails">
                <form class="ticketModal" action="<?=BASE_URL ?>/tickets/newTicket" method="post">
                    <?php $tpl->displaySubmodule('tickets-ticketDetails') ?>
                </form>
            </div>

        </div>
    </div>
</div>

<script type="text/javascript">

    jQuery(document).ready(function() {
        leantime.ticketsController.initTicketTabs();
        leantime.ticketsController.initTagsInput();
    });


    jQuery(window).load(function () {
        jQuery(window).resize();
    });

</script>
