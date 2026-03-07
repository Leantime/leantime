@props([
    'row' => [],
    'cardType' => 'simple',
    'onTheClock' => false,
    'priorities' => [],
    'statusLabels' => [],
    'efforts' => [],
    'milestones' => [],
])

<div class="ticketBox fixed priority-border-{{ $row['priority'] }}" data-val="{{ $row['id'] }}" aria-label="{{ __('label.priority') }}: {{ $priorities[$row['priority']] ?? $row['priority'] }}">
    <div class="row">
        <div class="col-md-8 titleContainer">
            @if($cardType == "full")
                <small>{{ $row['projectName'] }}</small><br />
                @if($row['dependingTicketId'] > 0)
                    <a href="#/tickets/showTicket/{{ $row['dependingTicketId'] }}">{{ $row['parentHeadline'] }}</a> //
                @endif
            @endif
            <strong><a href="#/tickets/showTicket/{{ $row['id'] }}" >{{ $row['headline'] }}</a></strong>

        </div>
        <div class="col-md-4 timerContainer" style="padding:5px 15px;" id="timerContainer-{{ $row['id'] }}">

            <x-globals::tickets.ticket-submenu :ticket="$row" :on-the-clock="$onTheClock" />
            @if($cardType == "full")
                <div class="scheduler pull-right">
                    @if( $row['editFrom'] != "0000-00-00 00:00:00" && $row['editFrom'] != "1969-12-31 00:00:00")
                        <x-global::elements.icon name="event_available" class="infoIcon tw:mr-xs" style="color:var(--accent2)" data-tippy-content="{{ __('text.schedule_to_start_on') }} {{ format($row['editFrom'])->date() }}" />
                    @else
                        <x-global::elements.icon name="event_busy" class="infoIcon tw:mr-xs" data-tippy-content="{{ __('text.not_scheduled_drag_ai') }}" />
                    @endif
                </div>
            @endif
        </div>
    </div>
    <div class="row">

            <div class="col-md-4" style="padding:0 15px;">
                @if($cardType == "full")
                    <x-global::elements.icon name="business_center" class="infoIcon" data-tippy-content=" {{ __("label.due") }}" />
                    <input type="text" title="{{ __("label.due") }}" value="{{ format($row['dateToFinish'])->date(__("text.anytime")) }}" class="duedates secretInput" style="margin-left:0px;" data-id="{{ $row['id'] }}" name="date" />
                @endif
            </div>

        <div class="col-md-8 dropdownContainer" style="padding-top:5px;">
            <x-globals::actions.chip
                content-role="status"
                :parentId="$row['id']"
                :selectedClass="$statusLabels[$row['status']]['class'] ?? 'label-default'"
                :selectedKey="$row['status']"
                :options="$statusLabels"
                :colorized="true"
                align="end"
                headerLabel="{{ __('dropdown.choose_status') }}"
            />

            @if($cardType == "full")
                @php
                    $milestoneOptions = [0 => ['name' => __('label.no_milestone'), 'class' => '#b0b0b0']];
                    if (isset($milestones)) {
                        foreach ($milestones as $ms) {
                            if (is_object($ms)) {
                                $milestoneOptions[$ms->id] = ['name' => $ms->headline, 'class' => $ms->tags];
                            }
                        }
                    }
                @endphp
                <x-globals::actions.chip
                    content-role="milestone"
                    :parentId="$row['id']"
                    selectedClass="label-default"
                    color="{{ $row['milestoneColor'] }}"
                    :selectedKey="$row['milestoneid'] ?: 0"
                    :options="$milestoneOptions"
                    :colorized="true"
                    align="end"
                    extraClass="tw:mr-sm"
                    headerLabel="{{ __('dropdown.choose_milestone') }}"
                />
            @endif
        </div>
    </div>
</div>
