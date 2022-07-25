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
                <li><a href="#ticketdetails"><?php echo $this->__("tabs.ticketDetails") ?></a></li>
                <li><a href="#subtasks"><?php echo $this->__('tabs.subtasks') ?> (<?php echo $this->get('numSubTasks'); ?>)</a></li>
                <li><a href="#comments"><?php echo $this->__("tabs.discussion") ?> (<?php echo $this->get('numComments'); ?>)</a></li>
                <li><a href="#files"><?php echo $this->__("tabs.files") ?> (<?php echo $this->get('numFiles'); ?>)</a></li>
                <?php if ($_SESSION["userdata"]["role"] != "client") { ?>
                    <li><a href="#timesheet"><?php echo $this->__("tabs.time_tracking") ?></a></li>
                <?php }; ?>
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
                <form method="post" action="#subtasks" class="ticketModal">
                    <?php $this->displaySubmodule('tickets-subTasks') ?>
                </form>
            </div>

            <div id="files">
                <form action='#files' method='POST' enctype="multipart/form-data" class="ticketModal">
                    <?php $this->displaySubmodule('tickets-attachments') ?>
                </form>
            </div>



            <?php if ($_SESSION["userdata"]["role"] != "client") { ?>
                <div id="timesheet">
                    <?php $this->displaySubmodule('tickets-timesheet') ?>
                </div>
            <?php } ?>
        </div>

        <br />


<script type="text/javascript">

    jQuery(function(){

        leantime.ticketsController.initTicketTabs();
        leantime.ticketsController.initTagsInput();
        leantime.generalController.initComplexEditor();

    });

</script>
