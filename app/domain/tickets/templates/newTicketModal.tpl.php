<?php

    defined('RESTRICTED') or die('Restricted access');
    foreach ($__data as $var => $val) $$var = $val; // necessary for blade refactor
    $ticket = $tpl->get('ticket');
    $projectData = $tpl->get('projectData');
    $todoTypeIcons  = $tpl->get("ticketTypeIcons");

?>

<div style="min-width:90%">
        <h1><?=$tpl->__("headlines.new_to_do") ?></h1>

        <?php echo $tpl->displayNotification(); ?>

        <div class="tabbedwidget tab-primary ticketTabs" style="visibility:hidden;">

            <ul>
                <li><a href="#ticketdetails"><?php echo $tpl->__("tabs.ticketDetails") ?></a></li>
            </ul>

            <div id="ticketdetails">
                <form class="formModal" action="<?=BASE_URL ?>/tickets/newTicket" method="post">
                    <?php $tpl->displaySubmodule('tickets-ticketDetails') ?>
                </form>
            </div>

        </div>
</div>
        <br />


<script type="text/javascript">

    jQuery(function(){

        leantime.ticketsController.initTicketTabs();
        leantime.ticketsController.initTagsInput();
        leantime.editorController.initComplexEditor();

        leantime.ticketsController.initDueDateTimePickers();
        leantime.ticketsController.initDates();

    });

</script>
