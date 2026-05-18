<?php

defined('RESTRICTED') or exit('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$ticket = $tpl->get('ticket');

?>

<div style="min-width:90%">
    <h1><?= $tpl->__('headlines.new_to_do') ?></h1>

    <?php echo $tpl->displayNotification(); ?>

    <div class="tabbedwidget tab-primary ticketTabs" style="visibility:hidden;">

        <ul>
            <li><a href="#ticketdetails"><?php echo $tpl->__('tabs.ticketDetails') ?></a></li>
        </ul>

        <div id="ticketdetails">
            <form class="formModal"
                action="<?= BASE_URL ?>/tickets/newTicketForPlan"
                method="post"
                enctype="multipart/form-data">
                <input type="hidden" name="planId" value="<?php $tpl->e($tpl->get('planId')); ?>" />
                <input type="hidden" name="employeeId" value="<?php $tpl->e($tpl->get('lockedEmployeeId')); ?>" />
                <?php $tpl->displaySubmodule('tickets-ticketDetailsForPlan') ?>
            </form>
        </div>

    </div>
</div>
<br />


<script type="text/javascript">
    jQuery(document).ready(function() {

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

    });
</script>
