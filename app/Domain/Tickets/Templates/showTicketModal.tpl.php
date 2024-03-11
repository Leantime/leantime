<?php

defined('RESTRICTED') or die('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$ticket = $tpl->get('ticket');
$projectData = $tpl->get('projectData');
$todoTypeIcons  = $tpl->get("ticketTypeIcons");

?>
<script type="text/javascript">
    window.onload = function() {
        if (!window.jQuery) {
            //It's not a modal
            location.href="<?=BASE_URL ?>/tickets/showKanban#/tickets/showTicket/<?php echo $ticket->id; ?>";
        }
    }
</script>

<div style="min-width:90%">

    <?php if ($ticket->dependingTicketId > 0) { ?>
        <small><a href="#/tickets/showTicket/<?=$ticket->dependingTicketId ?>"><?=$tpl->escape($ticket->parentHeadline) ?></a></small> //
    <?php } ?>
    <h1><i class="fa <?php echo $todoTypeIcons[strtolower($ticket->type)]; ?>"></i> #<?=$ticket->id ?> - <?php $tpl->e($ticket->headline); ?></h1>

    <?php echo $tpl->displayNotification(); ?>

    <?php if ($login::userIsAtLeast($roles::$editor)) {
        $clockedIn = $tpl->get("onTheClock");

        ?>
        <div class="inlineDropDownContainer" style="float:right; z-index:50; padding-top:10px;">

            <a href="javascript:void(0);" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
                <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
            </a>
            <ul class="dropdown-menu">
                <li class="nav-header"><?php echo $tpl->__("subtitles.todo"); ?></li>
                <li><a href="#/tickets/moveTicket/<?php echo $ticket->id; ?>" class="moveTicketModal sprintModal ticketModal"><i class="fa-solid fa-arrow-right-arrow-left"></i> <?php echo $tpl->__("links.move_todo"); ?></a></li>
                <li><a href="#/tickets/delTicket/<?php echo $ticket->id; ?>" class="delete"><i class="fa fa-trash"></i> <?php echo $tpl->__("links.delete_todo"); ?></a></li>
                <li class="nav-header border"><?php echo $tpl->__("subtitles.track_time"); ?></li>
                <li id="timerContainer-<?php echo $ticket->id;?>" class="timerContainer">
                    <a
                        class="punchIn"
                        href="javascript:void(0);"
                        data-value="<?php echo $ticket->id; ?>"
                        <?php if ($clockedIn !== false) {
                            echo"style='display:none;'";
                        }?>
                    ><span class="fa-regular fa-clock"></span> <?php echo $tpl->__("links.start_work"); ?></a>
                    <a
                        class="punchOut"
                        href="javascript:void(0);"
                        data-value="<?php echo $ticket->id; ?>"
                        <?php if ($clockedIn === false || $clockedIn["id"] != $ticket->id) {
                            echo"style='display:none;'";
                        }?>
                    >
                        <span class="fa fa-stop"></span>
                        <?php if (is_array($clockedIn)) {
                            echo sprintf($tpl->__("links.stop_work_started_at"), date($tpl->__("language.timeformat"), $clockedIn["since"]));
                        } else {
                            echo sprintf($tpl->__("links.stop_work_started_at"), date($tpl->__("language.timeformat"), time()));
                        }?>
                    </a>
                    <span
                        class='working'
                        <?php if ($clockedIn === false || $clockedIn["id"] === $ticket->id) {
                            echo"style='display:none;'";
                        }?>
                    ><?php echo $tpl->__("text.timer_set_other_todo"); ?></span>
                </li>
            </ul>
        </div>
    <?php } ?>
    <div class="tabbedwidget tab-primary ticketTabs" style="visibility:hidden;">

        <ul>
            <li><a href="#ticketdetails"><span class="fa fa-star"></span> <?php echo $tpl->__("tabs.ticketDetails") ?></a></li>
            <li><a href="#files"><span class="fa fa-file"></span> <?php echo $tpl->__("tabs.files") ?> (<?php echo $tpl->get('numFiles'); ?>)</a></li>
            <?php if ($login::userIsAtLeast($roles::$editor)) {  ?>
                <li><a href="#timesheet"><span class="fa fa-clock"></span> <?php echo $tpl->__("tabs.time_tracking") ?></a></li>
            <?php } ?>
            <?php $tpl->dispatchTplEvent('ticketTabs', ['ticket' => $ticket]); ?>
        </ul>

        <div id="ticketdetails">
            <form class="formModal" action="<?=BASE_URL ?>/tickets/showTicket/<?php echo $ticket->id ?>" method="post">
                <?php $tpl->displaySubmodule('tickets-ticketDetails') ?>
            </form>
        </div>

        <div id="files">
            <?php $tpl->displaySubmodule('files-showAll') ?>
        </div>

        <?php if ($login::userIsAtLeast($roles::$editor)) {  ?>
            <div id="timesheet">
                <?php $tpl->displaySubmodule('tickets-timesheet') ?>
            </div>
        <?php } ?>

        <?php $tpl->dispatchTplEvent('ticketTabsContent', ['ticket' => $ticket]); ?>

    </div>

</div>
<script type="text/javascript">

    jQuery(document).ready(function(){

        <?php if (isset($_GET['closeModal'])) { ?>
            jQuery.nmTop().close();
        <?php } ?>

        leantime.ticketsController.initTicketTabs();

        <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
            leantime.ticketsController.initAsyncInputChange();
            leantime.ticketsController.initDueDateTimePickers();

            leantime.dateController.initDatePicker(".dates");
            leantime.dateController.initDateRangePicker(".editFrom", ".editTo");

            leantime.ticketsController.initTagsInput();

            leantime.ticketsController.initEffortDropdown();
            leantime.ticketsController.initStatusDropdown();

            jQuery(".ticketTabs select").chosen();

        <?php } else { ?>
            leantime.authController.makeInputReadonly(".nyroModalCont");

        <?php } ?>

        <?php if ($login::userHasRole([$roles::$commenter])) { ?>
            leantime.commentsController.enableCommenterForms();
        <?php }?>

    });

</script>
