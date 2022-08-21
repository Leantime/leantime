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


<h1><i class="fa <?php echo $todoTypeIcons[strtolower($ticket->type)]; ?>"></i> #<?=$ticket->id ?> - <?php $this->e($ticket->headline); ?></h1>

<?php echo $this->displayNotification(); ?>

<div class="tabbedwidget tab-primary ticketTabs" style="visibility:hidden;">

    <ul>
        <li><a href="#ticketdetails"><span class="fa fa-star"></span> <?php echo $this->__("tabs.ticketDetails") ?></a></li>
        <li><a href="#subtasks"><span class="fa fa-tasks"></span> <?php echo $this->__('tabs.subtasks') ?> (<?php echo $this->get('numSubTasks'); ?>)</a></li>
        <li><a href="#comments"><span class="fa fa-comments"></span> <?php echo $this->__("tabs.discussion") ?> (<?php echo $this->get('numComments'); ?>)</a></li>
        <li><a href="#files"><span class="fa fa-file"></span> <?php echo $this->__("tabs.files") ?> (<?php echo $this->get('numFiles'); ?>)</a></li>
        <?php if($login::userIsAtLeast($roles::$editor)) {  ?>
            <li><a href="#timesheet"><span class="fa fa-clock-o"></span> <?php echo $this->__("tabs.time_tracking") ?></a></li>
        <?php } ?>
    </ul>

    <div id="ticketdetails">
        <form class="ticketModal" action="<?=BASE_URL ?>/tickets/showTicket/<?php echo $ticket->id ?>" method="post">
            <?php $this->displaySubmodule('tickets-ticketDetails') ?>
        </form>
    </div>

    <div id="comments">
        <form method="post" action="<?=BASE_URL ?>/tickets/showTicket/<?php echo $ticket->id; ?>#comments" class="ticketModal">
            <input type="hidden" name="comment" value="1" />
            <?php
            $this->assign('formUrl', "".BASE_URL."/tickets/showTicket/".$ticket->id."#comments");

            $this->displaySubmodule('comments-generalComment') ;
            ?>
        </form>
    </div>

    <div id="subtasks">
        <?php $this->displaySubmodule('tickets-subTasks') ?>
    </div>

    <div id="files">
        <?php $this->displaySubmodule('tickets-attachments') ?>
    </div>

    <?php if($login::userIsAtLeast($roles::$editor)) {  ?>
        <div id="timesheet">
            <?php $this->displaySubmodule('tickets-timesheet') ?>
        </div>
    <?php } ?>

</div>

<script type="text/javascript">

    jQuery(function(){
        <?php if(isset($_GET['closeModal'])){ ?>
            jQuery.nmTop().close();
        <?php } ?>

        leantime.ticketsController.initTicketTabs();
        leantime.generalController.initComplexEditor();

        <?php if($login::userIsAtLeast($roles::$editor)) { ?>

            leantime.ticketsController.initAsyncInputChange();
            leantime.ticketsController.initDueDateTimePickers();
            leantime.ticketsController.initDates();

            leantime.ticketsController.initTagsInput();

            leantime.ticketsController.initEffortDropdown();
            leantime.ticketsController.initStatusDropdown();

        <?php }else{ ?>

            leantime.generalController.makeInputReadonly(".nyroModalCont");

        <?php } ?>

        <?php if($login::userHasRole([$roles::$commenter])) { ?>
            leantime.generalController.enableCommenterForms();
        <?php }?>

    });

</script>
