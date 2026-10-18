<?php

foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$ticket = $ticket ?? null;
$projectData = $projectData ?? [];
$todoTypeIcons = $ticketTypeIcons ?? [];

?>
<script type="text/javascript">
    window.onload = function() {
        if (!window.jQuery) {
            //It's not a modal
            location.href="<?= BASE_URL ?>/tickets/showKanban?showTicketModal=<?php echo $ticket->id; ?>";
        }
    }
</script>

<div style="min-width:70%">

    @php
        $onTheClock = $onTheClock ?? false;
        $ticketTypeIcon = $todoTypeIcons[strtolower($ticket->type)] ?? 'fa-circle';
    @endphp
    <x-global::actions.modal-header :icon="'fa '.$ticketTypeIcon">
        <x-slot:title>
            @if ($ticket->dependingTicketId > 0)
                <a href="#/tickets/showTicket/{{ $ticket->dependingTicketId }}" class="lt-modal-parent">{{ $ticket->parentHeadline }}</a> <span class="lt-modal-parent">//</span>
            @endif
            {{ __('label.'.strtolower($ticket->type)) }} #{{ $ticket->id }}
        </x-slot:title>
        <x-slot:meta>
            {{ __('label.created_by') }} {{ $ticket->userFirstname }} {{ $ticket->userLastname }} | {{ __('label.last_updated') }}: {{ format(! empty($ticket->modified) ? $ticket->modified : $ticket->date)->date() }}
        </x-slot:meta>
        @if ($login::userIsAtLeast($roles::$editor))
            <x-slot:actions>
                <li class="nav-header"><?php echo $tpl->__('subtitles.todo'); ?></li>
                        <li><a href="#/tickets/moveTicket/<?php echo $ticket->id; ?>" class="moveTicketModal sprintModal ticketModal"><i class="fa-solid fa-arrow-right-arrow-left"></i> <?php echo $tpl->__('links.move_todo'); ?></a></li>
                        <li><a href="#/tickets/delTicket/<?php echo $ticket->id; ?>" class="delete"><i class="fa fa-trash"></i> <?php echo $tpl->__('links.delete_todo'); ?></a></li>
                        @module('timesheets', $ticket->projectId)
                        <li class="nav-header border"><?php echo $tpl->__('subtitles.track_time'); ?></li>
                        <li id="timerContainer-ticketDetails-{{ $ticket->id }}"
                            hx-get="{{BASE_URL}}/tickets/timerButton/get-status/{{ $ticket->id }}"
                            hx-trigger="timerUpdate from:body"
                            hx-swap="outerHTML"
                            class="timerContainer">

                            @if ($onTheClock === false)
                                <a href="javascript:void(0);" data-value="{{ $ticket->id }}"
                                   hx-patch="{{ BASE_URL }}/hx/timesheets/stopwatch/start-timer/"
                                   hx-target="#timerHeadMenu"
                                   hx-swap="outerHTML"
                                   hx-vals='{"ticketId": "{{ $ticket->id }}", "action":"start"}'>
                                    <span class="fa-regular fa-clock"></span> {{ __("links.start_work") }}
                                </a>
                            @endif

                            @if ($onTheClock !== false && $onTheClock["id"] == $ticket->id)
                                <a href="javascript:void(0);" data-value="{{ $ticket->id }}"
                                   hx-patch="{{ BASE_URL }}/hx/timesheets/stopwatch/stop-timer/"
                                   hx-target="#timerHeadMenu"
                                   hx-vals='{"ticketId": "{{ $ticket->id }}", "action":"stop"}'
                                   hx-swap="outerHTML">
                                    <span class="fa fa-stop"></span>

                                    @if (is_array($onTheClock) == true)
                                        {!!  sprintf(__("links.stop_work_started_at"), dtHelper()->userNow()->setTimestamp((int) $onTheClock["since"])->format(__("language.timeformat"))) !!}
                                    @else
                                        {!! sprintf(__("links.stop_work_started_at"), dtHelper()->userNow()->format(__("language.timeformat"))) !!}
                                    @endif
                                </a>
                            @endif
                            @if ($onTheClock !== false && $onTheClock["id"] != $ticket->id)
                                <span class='working'>
                    {{ __("text.timer_set_other_todo") }}
                </span>
                            @endif
                        </li>
                        @endmodule
            </x-slot:actions>
        @endif
    </x-global::actions.modal-header>
    <div class="tabbedwidget tab-primary ticketTabs" style="visibility:hidden;">

        <ul>
            <li><a href="#ticketdetails"><span class="fa fa-star"></span> <?php echo $tpl->__('tabs.ticketDetails') ?></a></li>
            <li><a href="#files"><span class="fa fa-file"></span> <?php echo $tpl->__('tabs.files') ?> (<?php echo $numFiles; ?>)</a></li>
            @if ($login::userIsAtLeast($roles::$editor))
                @module('timesheets', $ticket->projectId)
                    <li><a href="#timesheet"><span class="fa fa-clock"></span> <?php echo $tpl->__('tabs.time_tracking') ?></a></li>
                @endmodule
            @endif
            <li><a href="#history"><span class="fa fa-clock-rotate-left"></span> <?php echo $tpl->__('tabs.history') ?></a></li>
            <?php $tpl->dispatchTplEvent('ticketTabs', ['ticket' => $ticket]); ?>
        </ul>

        <div id="ticketdetails">
            <form class="formModal" action="{{ BASE_URL }}/tickets/showTicket/{{ $ticket->id }}" method="post">
                @include('tickets::submodules.ticketDetails')
            </form>
        </div>

        <div id="files">
            @include('files::submodules.showAll')
        </div>

        @if($login::userIsAtLeast($roles::$editor))
            @module('timesheets', $ticket->projectId)
                <div id="timesheet">
                    @include('tickets::submodules.timesheet')
                </div>
            @endmodule
        @endif

        <div id="history">
            {{-- Fetched only when the tab is first shown, so the modal does not load the history up front. --}}
            <x-global::hx :for="\Leantime\Domain\Tickets\Hxcontrollers\TicketHistory::class" :id="$ticket->id" trigger="intersect once" loaderCount="3" />
        </div>

        @dispatchEvent('ticketTabsContent', ['ticket' => $ticket])

    </div>

</div>
<script type="text/javascript">

    jQuery(document).ready(function(){

        <?php if (isset($_GET['closeModal'])) { ?>
            jQuery.nmTop().close();
        <?php } ?>

        leantime.ticketsController.initTicketTabs();

        <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
            leantime.ticketsController.initAsyncInputChange();
            leantime.ticketsController.initDueDateTimePickers();

            leantime.dateController.initDatePicker(".dates");
            leantime.dateController.initDateRangePicker(".editFrom", ".editTo");




        <?php } else { ?>
            leantime.authController.makeInputReadonly(".nyroModalCont");

        <?php } ?>

        <?php if ($login::userHasRole([$roles::$commenter])) { ?>
            leantime.commentsController.enableCommenterForms();
        <?php }?>

    });

</script>
