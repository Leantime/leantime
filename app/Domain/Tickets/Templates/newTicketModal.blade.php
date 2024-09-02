<?php

defined('RESTRICTED') or die('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
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


    jQuery(document).ready(function(){

        <?php if (isset($_GET['closeModal'])) { ?>
        jQuery.nmTop().close();
        <?php } ?>

        leantime.ticketsController.initTicketTabs();

        <?php if ($login::userIsAtLeast($roles::$editor)) { ?>

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
