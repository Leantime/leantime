<?php

defined('RESTRICTED') or exit('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$ticket = $tpl->get('ticket');
$lockedMilestoneId = (int) $tpl->get('lockedMilestoneId');
$lockedMilestoneName = $tpl->get('lockedMilestoneName') ?? '';
$lockedProjectId = (int) $tpl->get('lockedProjectId');
$lockedProjectName = $tpl->get('lockedProjectName') ?? '';

?>

<div style="min-width:90%">
    <h1><?= $tpl->__('headlines.new_to_do') ?></h1>

    <?php echo $tpl->displayNotification(); ?>

    <div style="display:flex; gap:16px; margin-bottom:14px; padding:10px 14px;
                background:rgba(74,158,255,.08); border-radius:6px; border:1px solid rgba(74,158,255,.2);
                font-size:13px; flex-wrap:wrap; align-items:center;">
        <i class="fa fa-lock" style="opacity:.5;"></i>
        <span><i class="fa fa-briefcase" style="opacity:.6; margin-right:4px;"></i>
            <strong>Project:</strong> <?= htmlspecialchars($lockedProjectName, ENT_QUOTES) ?>
        </span>
        <span><i class="fa fa-flag" style="opacity:.6; margin-right:4px;"></i>
            <strong>Milestone:</strong> <?= htmlspecialchars($lockedMilestoneName, ENT_QUOTES) ?>
        </span>
    </div>

    <div class="tabbedwidget tab-primary ticketTabs" style="visibility:hidden;">

        <ul>
            <li><a href="#ticketdetails"><?php echo $tpl->__('tabs.ticketDetails') ?></a></li>
        </ul>

        <div id="ticketdetails">
            <form class="formModal"
                  action="<?= BASE_URL ?>/tickets/newTicketForMilestone"
                  method="post"
                  enctype="multipart/form-data">

                <input type="hidden" name="milestoneId" value="<?= $lockedMilestoneId ?>" />
                <input type="hidden" name="projectId"   value="<?= $lockedProjectId ?>" />

                <?php $tpl->displaySubmodule('tickets-ticketDetails') ?>

            </form>
        </div>

    </div>
</div>
<br />

<script type="text/javascript">
    jQuery(document).ready(function () {

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
