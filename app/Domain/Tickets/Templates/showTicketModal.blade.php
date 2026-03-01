<?php

foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$ticket = $tpl->get('ticket');
$projectData = $tpl->get('projectData');
$todoTypeIcons = $tpl->get('ticketTypeIcons');

?>
<script type="text/javascript">
    if (!window.jQuery) {
        // Not inside a modal — redirect to the full kanban view which will
        // open the ticket in a modal. Replace the document to prevent further
        // inline scripts from executing and throwing ReferenceErrors.
        location.replace("<?= BASE_URL ?>/tickets/showKanban?showTicketModal=<?php echo $ticket->id; ?>");
        document.write('');
        document.close();
    }
</script>

<div style="min-width: min(70%, 95vw)">

    <?php if ($ticket->dependingTicketId > 0) { ?>
        <small><a href="#/tickets/showTicket/<?= $ticket->dependingTicketId ?>"><?= $tpl->escape($ticket->parentHeadline) ?></a></small> //
    <?php } ?>
    <small class="pull-right tw:pr-md" style="padding:5px 30px 0px 0px">Created by <?php $tpl->e($ticket->userFirstname); ?> <?php $tpl->e($ticket->userLastname); ?> | Last Updated: <?= format($ticket->date)->date(); ?> </small>
    <h1 class="tw:mb-0" style="margin-bottom:0px;"><i class="fa <?php echo $todoTypeIcons[strtolower($ticket->type)]; ?>" aria-hidden="true"></i> #<?= $ticket->id ?> - <?php $tpl->e($ticket->headline); ?></h1>

    <br />

    <?php if ($login::userIsAtLeast($roles::$editor)) {
        $onTheClock = $tpl->get('onTheClock');
        ?>
        <x-globals::actions.dropdown-menu container-class="pull-right tw:z-50" style="padding-top:10px; padding-right:10px;">
                <li class="nav-header border"><?php echo $tpl->__('subtitles.todo'); ?></li>
                <li><a href="#/tickets/moveTicket/<?php echo $ticket->id; ?>" class="moveTicketModal sprintModal ticketModal"><x-global::elements.icon name="swap_horiz" /> <?php echo $tpl->__('links.move_todo'); ?></a></li>
                <li><a href="#/tickets/delTicket/<?php echo $ticket->id; ?>" class="delete"><x-global::elements.icon name="delete" /> <?php echo $tpl->__('links.delete_todo'); ?></a></li>
                <li class="nav-header border"><?php echo $tpl->__('subtitles.track_time'); ?></li>
                <li id="timerContainer-ticketDetails-{{ $ticket->id }}"
                    hx-get="{{BASE_URL}}/tickets/timerButton/get-status/{{ $ticket->id }}"
                    hx-trigger="timerUpdate from:body"
                    hx-swap="outerHTML"
                    aria-live="assertive"
                    class="timerContainer">

                    @if ($onTheClock === false)
                        <a href="javascript:void(0);" data-value="{{ $ticket->id }}"
                           hx-patch="{{ BASE_URL }}/hx/timesheets/stopwatch/start-timer/"
                           hx-target="#timerHeadMenu"
                           hx-swap="outerHTML"
                           hx-vals='{"ticketId": "{{ $ticket->id }}", "action":"start"}'>
                            <x-global::elements.icon name="schedule" /> {{ __("links.start_work") }}
                        </a>
                    @endif

                    @if ($onTheClock !== false && $onTheClock["id"] == $ticket->id)
                        <a href="javascript:void(0);" data-value="{{ $ticket->id }}"
                           hx-patch="{{ BASE_URL }}/hx/timesheets/stopwatch/stop-timer/"
                           hx-target="#timerHeadMenu"
                           hx-vals='{"ticketId": "{{ $ticket->id }}", "action":"stop"}'
                           hx-swap="outerHTML">
                            <x-global::elements.icon name="stop" />

                            @if (is_array($onTheClock) == true)
                                {!!  sprintf(__("links.stop_work_started_at"), date(__("language.timeformat"), $onTheClock["since"])) !!}
                            @else
                                {!! sprintf(__("links.stop_work_started_at"), date(__("language.timeformat"), time())) !!}
                            @endif
                        </a>
                    @endif
                    @if ($onTheClock !== false && $onTheClock["id"] != $ticket->id)
                        <span class='working'>
            {{ __("text.timer_set_other_todo") }}
        </span>
                    @endif
                </li>
        </x-globals::actions.dropdown-menu>
    <?php } ?>
    <div class="lt-tabs tabbedwidget ticketTabs" style="visibility:hidden;" data-tabs data-tabs-persist="url">

        <ul role="tablist">
            <li><a href="#ticketdetails"><x-global::elements.icon name="star" /> <?php echo $tpl->__('tabs.ticketDetails') ?></a></li>
            <li><a href="#files"><x-global::elements.icon name="description" /> <?php echo $tpl->__('tabs.files') ?> (<?php echo $tpl->get('numFiles'); ?>)</a></li>
            <?php if ($login::userIsAtLeast($roles::$editor)) {  ?>
                <li><a href="#timesheet"><x-global::elements.icon name="schedule" /> <?php echo $tpl->__('tabs.time_tracking') ?></a></li>
            <?php } ?>
            <?php $tpl->dispatchTplEvent('ticketTabs', ['ticket' => $ticket]); ?>
        </ul>

        <div id="ticketdetails">
            <form class="formModal" action="<?= BASE_URL ?>/tickets/showTicket/<?php echo $ticket->id ?>" method="post">
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
            leantime.authController.makeInputReadonly("#global-modal-content");

        <?php } ?>

        <?php if ($login::userHasRole([$roles::$commenter])) { ?>
            leantime.commentsController.enableCommenterForms();
        <?php }?>

    });

</script>
