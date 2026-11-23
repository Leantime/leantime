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
    {{-- The headline stays in the header (not only in the editable field of the details tab), so it
         is visible on every tab (#4008). --}}
    <x-global::actions.modal-header :icon="'fa '.$ticketTypeIcon">
        <x-slot:title>
            @if ($ticket->dependingTicketId > 0)
                <a href="#/tickets/showTicket/{{ $ticket->dependingTicketId }}" class="lt-modal-parent">{{ $ticket->parentHeadline }}</a> <span class="lt-modal-parent">//</span>
            @endif
            {{ __('label.'.strtolower($ticket->type)) }} #{{ $ticket->id }} - {{ $ticket->headline }}
        </x-slot:title>
        <x-slot:meta>
            {{ __('label.created_by') }} {{ $ticket->userFirstname }} {{ $ticket->userLastname }} | {{ __('label.last_updated') }}: {{ format(! empty($ticket->modified) ? $ticket->modified : $ticket->date)->date() }}
        </x-slot:meta>
        {{-- tickets.view opens the menu for "Your reminders"; every other item keeps its own check. --}}
        @canany(['tickets.view', 'tickets.edit', 'tickets.delete', 'timesheets.create'], $ticket->projectId)
            <x-slot:actions>
                @canany(['tickets.edit', 'tickets.delete'], $ticket->projectId)
                <li class="nav-header"><?php echo $tpl->__('subtitles.todo'); ?></li>
                @endcanany
                        @can('tickets.edit', $ticket->projectId)
                        <li><a href="#/tickets/moveTicket/<?php echo $ticket->id; ?>" class="moveTicketModal sprintModal ticketModal"><i class="fa-solid fa-arrow-right-arrow-left"></i> <?php echo $tpl->__('links.move_todo'); ?></a></li>
                        @endcan
                        @can('tickets.delete', $ticket->projectId)
                        <li><a href="#/tickets/delTicket/<?php echo $ticket->id; ?>" class="delete" data-lt-permitted><i class="fa fa-trash"></i> <?php echo $tpl->__('links.delete_todo'); ?></a></li>
                        @endcan
                        @can('timesheets.create')
                        @module('timesheets', $ticket->projectId)
                        <li class="nav-header border"><?php echo $tpl->__('subtitles.track_time'); ?></li>
                        <li id="timerContainer-ticketDetails-{{ $ticket->id }}"
                            hx-get="{{BASE_URL}}/tickets/timerButton/get-status/{{ $ticket->id }}"
                            hx-trigger="{{ \Leantime\Domain\Timesheets\Events\TimerStarted::CLIENT_EVENT }} from:body, {{ \Leantime\Domain\Timesheets\Events\TimerStopped::CLIENT_EVENT }} from:body, {{ \Leantime\Core\Events\Htmx\HtmxUiEvents::TimerRefresh->value }} from:body"
                            hx-swap="outerHTML"
                            class="timerContainer">

                            @if ($onTheClock === false)
                                <a href="javascript:void(0);" data-value="{{ $ticket->id }}"
                                   hx-patch="{{ BASE_URL }}/hx/timesheets/stopwatch/start-timer/"
                                   data-lt-refresh-container
                                   hx-target="#timerHeadMenu"
                                   hx-swap="outerHTML"
                                   hx-vals='{"ticketId": "{{ $ticket->id }}", "action":"start"}'>
                                    <span class="fa-regular fa-clock"></span> {{ __("links.start_work") }}
                                </a>
                            @endif

                            @if ($onTheClock !== false && $onTheClock["id"] == $ticket->id)
                                <a href="javascript:void(0);" data-value="{{ $ticket->id }}"
                                   hx-patch="{{ BASE_URL }}/hx/timesheets/stopwatch/stop-timer/"
                                   data-lt-refresh-container
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
                        @endcan
                        @if ($ticket->type !== 'milestone')
                        {{-- Your own reminders for this to-do; the choices save inside this menu (a second modal would replace the open to-do). --}}
                        @can('tickets.view', $ticket->projectId)
                        @include('reminders::partials.taskReminderMenuMount', ['ticketId' => $ticket->id])
                        @endcan
                        @endif
            </x-slot:actions>
        @endcanany
    </x-global::actions.modal-header>
    <x-global::navigation.section-tabs class="ticketTabs" :label="__('tabs.ticketDetails')" remember="query:tab">
        <x-slot:tabs>
            <x-global::navigation.section-tabs.tab for="ticketdetails" icon="fa-star">{!! __('tabs.ticketDetails') !!}</x-global::navigation.section-tabs.tab>
            <x-global::navigation.section-tabs.tab for="files" icon="fa-file">{!! __('tabs.files') !!} ({{ $numFiles }})</x-global::navigation.section-tabs.tab>
            @can('timesheets.create')
                @module('timesheets', $ticket->projectId)
                    <x-global::navigation.section-tabs.tab for="timesheet" icon="fa-clock">{!! __('tabs.time_tracking') !!}</x-global::navigation.section-tabs.tab>
                @endmodule
            @endcan
            <x-global::navigation.section-tabs.tab for="history" icon="fa-clock-rotate-left">{!! __('tabs.history') !!}</x-global::navigation.section-tabs.tab>
            @dispatchEvent('ticketTabs', ['ticket' => $ticket])
        </x-slot:tabs>

        <div id="ticketdetails">
            <form class="formModal" action="{{ BASE_URL }}/tickets/showTicket/{{ $ticket->id }}" method="post">
                @include('tickets::submodules.ticketDetails')
            </form>
        </div>

        <div id="files">
            @include('files::submodules.showAll')
        </div>

        @can('timesheets.create')
            @module('timesheets', $ticket->projectId)
                <div id="timesheet" data-lt-permitted>
                    @include('tickets::submodules.timesheet')
                </div>
            @endmodule
        @endcan

        <div id="history">
            {{-- Fetched only when the tab is first shown, so the modal does not load the history up front. --}}
            <x-global::hx :for="\Leantime\Domain\Tickets\Hxcontrollers\TicketHistory::class" :id="$ticket->id" trigger="intersect once" loaderCount="3" />
        </div>

        @dispatchEvent('ticketTabsContent', ['ticket' => $ticket])

    </x-global::navigation.section-tabs>

</div>
<script type="text/javascript">

    jQuery(document).ready(function(){

        <?php if (isset($_GET['closeModal'])) { ?>
            jQuery.nmTop().close();
        <?php } ?>

        @can('tickets.edit', $ticket->projectId)
            leantime.ticketsController.initAsyncInputChange();
            leantime.ticketsController.initDueDateTimePickers();

            leantime.dateController.initDatePicker(".dates");
            leantime.dateController.initDateRangePicker(".editFrom", ".editTo");




        @else
            leantime.authController.makeInputReadonly(".nyroModalCont");
            @can('comments.create', $ticket->projectId)
                leantime.commentsController.enableCommenterForms();
            @endcan
        @endcan

    });

</script>
