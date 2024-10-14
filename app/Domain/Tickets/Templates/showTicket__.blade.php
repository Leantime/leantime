<?php

defined('RESTRICTED') or die('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$ticket = $tpl->get('ticket');
$projectData = $tpl->get('projectData');

?>

<div class="pageheader">

    <div class="pull-right padding-top">
        <a href="<?php echo session("lastPage") ?>" class="backBtn"><i class="far fa-arrow-alt-circle-left"></i> <?=$tpl->__("links.go_back") ?></a>
    </div>

    <div class="pageicon"><span class="fa fa-location-pin"></span></div>
    <div class="pagetitle">
        <h5><?php $tpl->e(session("currentProjectClient") . " // " . session("currentProjectName")); ?></h5>
        <h1><?=$tpl->__("headlines.edit_todo") ?></h1>
    </div>

</div><!--pageheader-->

<div class="maincontent">

    <div class="maincontentinner">

        @displayNotification()

        <div class="tabbedwidget tab-primary ticketTabs" style="visibility:hidden;">

            <ul>
                <li><a href="#ticketdetails">{{ __("tabs.ticketDetails") }}</a></li>
                <li><a href="#subtasks">{{ __("tabs.subtasks") }} (<?php echo $tpl->get('numSubTasks'); ?>)</a></li>
                <li><a href="#files">{{ __("tabs.files") }} (<?php echo $tpl->get('numFiles'); ?>)</a></li>
                <?php if (session("userdata.role") != "client") { ?>
                    <li><a href="#timesheet" id="timesheetTab">{{ __("tabs.time_tracking") }}</a></li>
                <?php } ?>
            </ul>

            <div id="ticketdetails">
                <form class="formModal" action="{{ BASE_URL }}/tickets/showTicket/<?php echo $ticket->id ?>" method="post">
                    @include("tickets::includes.ticketDetails")
                </form>
            </div>

            <div id="subtasks">

                    <?php $tpl->displaySubmodule('tickets-subTasks') ?>


            </div>

            <div id="files">
                <form action='#files' method='POST' enctype="multipart/form-data" class="formModal">
                    <?php $tpl->displaySubmodule('tickets-attachments') ?>
                </form>
            </div>


            <?php if (session("userdata.role") != "client") { ?>
                <div id="timesheet">
                    @include("tickets::includes.timesheet")
                </div>
            <?php } ?>
        </div>

    </div>

    <div class="maincontentinner">
        <form method="post" action="{{ BASE_URL }}/tickets/showTicket/<?php echo $ticket->id; ?>#comments" class="formModal">
            <input type="hidden" name="comment" value="1" />
            <?php
            $tpl->assign('formUrl', BASE_URL . "/tickets/showTicket/" . $ticket->id . "");

            $tpl->displaySubmodule('comments-generalComment') ;
            ?>
        </form>
    </div>







</div>

<script type="text/javascript">

    jQuery(window).load(function () {
        leantime.ticketsController.initTicketTabs();

        jQuery(window).resize();

    });

</script>
