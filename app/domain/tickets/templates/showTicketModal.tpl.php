<?php

    defined('RESTRICTED') or die('Restricted access');
    $ticket = $this->get('ticket');
    $projectData = $this->get('projectData');
    $todoTypeIcons  = $this->get("ticketTypeIcons");

?>
<script type="text/javascript">
    window.onload = function() {
        if (!window.jQuery) {
            //It's not a modal
            location.href="<?=BASE_URL ?>/tickets/showKanban&showTicketModal=<?php echo $ticket->id; ?>";
        }
    }
</script>

<div style="min-width:90%">

    <?php if($ticket->dependingTicketId > 0){ ?>
        <small><a href="<?=$_SESSION['lastPage'] ?>/#/tickets/showTicket/<?=$ticket->dependingTicketId ?>"><?=$this->escape($ticket->parentHeadline) ?></a></small> //
    <?php } ?>
    <h1><i class="fa <?php echo $todoTypeIcons[strtolower($ticket->type)]; ?>"></i> #<?=$ticket->id ?> - <?php $this->e($ticket->headline); ?></h1>

    <?php echo $this->displayNotification(); ?>

    <?php if ($login::userIsAtLeast($roles::$editor)) {
        $clockedIn = $this->get("onTheClock");

        ?>
        <div class="inlineDropDownContainer" style="float:right; z-index:50; padding-top:10px;">

            <a href="javascript:void(0);" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
                <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
            </a>
            <ul class="dropdown-menu">
                <li class="nav-header"><?php echo $this->__("subtitles.todo"); ?></li>
                <li><a href="<?=BASE_URL ?>/tickets/moveTicket/<?php echo $ticket->id; ?>" class="moveTicketModal sprintModal ticketModal"><i class="fa-solid fa-arrow-right-arrow-left"></i> <?php echo $this->__("links.move_todo"); ?></a></li>
                <li><a href="<?=BASE_URL ?>/tickets/delTicket/<?php echo $ticket->id; ?>" class="delete"><i class="fa fa-trash"></i> <?php echo $this->__("links.delete_todo"); ?></a></li>
                <li class="nav-header border"><?php echo $this->__("subtitles.track_time"); ?></li>
                <li id="timerContainer-<?php echo $ticket->id;?>" class="timerContainer">
                    <a class="punchIn" href="javascript:void(0);" data-value="<?php echo $ticket->id; ?>" <?php if ($clockedIn !== false) {
                        echo"style='display:none;'";
                    }?>><span class="fa-regular fa-clock"></span> <?php echo $this->__("links.start_work"); ?></a>
                    <a class="punchOut" href="javascript:void(0);" data-value="<?php echo $ticket->id; ?>" <?php if ($clockedIn === false || $clockedIn["id"] != $ticket->id) {
                        echo"style='display:none;'";
                    }?>><span class="fa fa-stop"></span> <?php if (is_array($clockedIn) == true) {
                            echo sprintf($this->__("links.stop_work_started_at"), date($this->__("language.timeformat"), $clockedIn["since"]));
                        } else {
                            echo sprintf($this->__("links.stop_work_started_at"), date($this->__("language.timeformat"), time()));
                        }?></a>
                    <span class='working' <?php if ($clockedIn === false || $clockedIn["id"] === $ticket->id) {
                        echo"style='display:none;'";
                    }?>><?php echo $this->__("text.timer_set_other_todo"); ?></span>
                </li>
            </ul>
        </div>
    <?php } ?>
    <div class="tabbedwidget tab-primary ticketTabs" style="visibility:hidden;">

        <ul>
            <li><a href="#ticketdetails"><span class="fa fa-star"></span> <?php echo $this->__("tabs.ticketDetails") ?></a></li>
            <li><a href="#subtasks"><span class="fa fa-tasks"></span> <?php echo $this->__('tabs.subtasks') ?> (<?php echo $this->get('numSubTasks'); ?>)</a></li>
            <li><a href="#files"><span class="fa fa-file"></span> <?php echo $this->__("tabs.files") ?> (<?php echo $this->get('numFiles'); ?>)</a></li>
            <?php if ($login::userIsAtLeast($roles::$editor)) {  ?>
                <li><a href="#timesheet"><span class="fa fa-clock"></span> <?php echo $this->__("tabs.time_tracking") ?></a></li>
            <?php } ?>
        </ul>

        <div id="ticketdetails">
            <form class="formModal" action="<?=BASE_URL ?>/tickets/showTicket/<?php echo $ticket->id ?>" method="post">
                <?php $this->displaySubmodule('tickets-ticketDetails') ?>
            </form>

        </div>



        <div id="subtasks">
            <?php $this->displaySubmodule('tickets-subTasks') ?>
        </div>

        <div id="files">
            <?php $this->displaySubmodule('files-showAll') ?>
        </div>

        <?php if ($login::userIsAtLeast($roles::$editor)) {  ?>
            <div id="timesheet">
                <?php $this->displaySubmodule('tickets-timesheet') ?>
            </div>
        <?php } ?>

    </div>

</div>
<script type="text/javascript">

    jQuery(function(){



        <?php if (isset($_GET['closeModal'])) { ?>
            jQuery.nmTop().close();
        <?php } ?>

        leantime.ticketsController.initTicketTabs();
        leantime.timesheetsController._initTicketTimers();

        <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
            leantime.ticketsController.initAsyncInputChange();
            leantime.ticketsController.initDueDateTimePickers();
            leantime.ticketsController.initDates();

            leantime.ticketsController.initTagsInput();

            leantime.ticketsController.initEffortDropdown();
            leantime.ticketsController.initStatusDropdown();

        <?php } else { ?>
            leantime.generalController.makeInputReadonly(".nyroModalCont");

        <?php } ?>

        <?php if ($login::userHasRole([$roles::$commenter])) { ?>
            leantime.generalController.enableCommenterForms();
        <?php }?>

    });

</script>
