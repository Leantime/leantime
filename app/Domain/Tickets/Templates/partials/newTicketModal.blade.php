<x-global::content.modal.modal-buttons/>

<?php
$ticket = $tpl->get('ticket');
$projectData = $tpl->get('projectData');
$todoTypeIcons  = $tpl->get("ticketTypeIcons");

?>

<div class="min-w-[80vw]">
    <h1><?=$tpl->__("headlines.new_to_do") ?></h1>

    <?php echo $tpl->displayNotification(); ?>

    <div class="tabbedwidget tab-primary ticketTabs" style="visibility:hidden;">

        <ul>
            <li><a href="#ticketdetails"><?php echo $tpl->__("tabs.ticketDetails") ?></a></li>
        </ul>

        <div id="ticketdetails">
            <form  hx-post="<?=BASE_URL ?>/tickets/ticket/create" method="post">
                @include("tickets::shared.ticketDetails")
            </form>
        </div>

    </div>
</div>

<script type="text/javascript">


    jQuery(document).ready(function(){

        leantime.ticketsController.initTicketTabs();

        <?php if ($login::userIsAtLeast($roles::$editor)) { ?>

            leantime.ticketsController.initDueDateTimePickers();

            leantime.dateController.initDatePicker(".dates");
            leantime.dateController.initDateRangePicker(".editFrom", ".editTo");

            leantime.ticketsController.initTagsInput();

            leantime.ticketsController.initEffortDropdown();
            leantime.ticketsController.initStatusDropdown();

        <?php } else { ?>
            leantime.authController.makeInputReadonly(".nyroModalCont");

        <?php } ?>

        <?php if ($login::userHasRole([$roles::$commenter])) { ?>
            leantime.commentsController.enableCommenterForms();
        <?php }?>

    });

</script>
