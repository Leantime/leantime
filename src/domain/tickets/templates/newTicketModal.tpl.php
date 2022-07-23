<?php

    defined('RESTRICTED') or die('Restricted access');
	$ticket = $this->get('ticket');
	$projectData = $this->get('projectData');
    $todoTypeIcons  = $this->get("ticketTypeIcons");

?>


        <h1><?=$this->__("headlines.new_to_do") ?></h1>

        <?php echo $this->displayNotification(); ?>

        <div class="tabbedwidget tab-primary ticketTabs" style="visibility:hidden;">

            <ul>
                <li><a href="#ticketdetails"><?php echo $this->__("tabs.ticketDetails") ?></a></li>
            </ul>

            <div id="ticketdetails">
                <form class="ticketModal" action="<?=BASE_URL ?>/tickets/newTicket" method="post">
                    <?php $this->displaySubmodule('tickets-ticketDetails') ?>
                </form>
            </div>

        </div>

        <br />


<script type="text/javascript">

    jQuery(function(){

        leantime.ticketsController.initTicketTabs();
        leantime.ticketsController.initTagsInput();
        leantime.generalController.initComplexEditor();

    });

</script>
