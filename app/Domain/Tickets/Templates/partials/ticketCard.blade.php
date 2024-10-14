

<div class="ticketBox fixed priority-border-{{ $row['priority'] }}"
     data-val="{{ $row['id'] }}"
     id="ticketbox-{{ $row['id'] }}"
>

    <form
        hx-post="{{BASE_URL}}/hx/tickets/ticket-card/save/{{ $row['id'] }}"
        hx-target="#ticketbox-{{ $row['id'] }}"
        id="ticketboxForm{{ $row['id'] }}"
        hx-ext="debug"
    >
        <input type="submit" style="display:none;"/>
        <input type="hidden" name="id" value="{{ $row['id'] }}" />
        <div class="row">
            <div class="col-md-12 timerContainer" style="padding:5px 15px;" id="timerContainer-{{ $row['id'] }}">

                @include("tickets::includes.ticketsubmenu", ["ticket" => $row, "onTheClock" => $onTheClock])

                <small>{{ $row['projectName'] }}</small><br />
                @if($row['dependingTicketId'] > 0)
                    <a href="#/tickets/showTicket/{{ $row['dependingTicketId'] }}">{{ $row['parentHeadline'] }}</a> //
                @endif
                <strong><a href="#/tickets/showTicket/{{ $row['id'] }}" >{{ $row['headline'] }}</a></strong>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4" style="padding:0 15px;">
                @if( $row['editFrom'] != "0000-00-00 00:00:00" && $row['editFrom'] != "1969-12-31 00:00:00")
                    <i class="fa-solid fa-calendar-check infoIcon mr-sm" data-tippy-content="{{ __('text.schedule_to_start_on') }} {{ format($row['editFrom'])->date() }}"></i>
                @else
                    <i class="fa-regular fa-calendar-xmark infoIcon mr-sm" data-tippy-content="{{ __('text.not_scheduled_drag_ai') }}"></i>
                @endif
                <i class="fa-solid fa-business-time infoIcon" data-tippy-content=" {{ __("label.due") }}"></i>
                <input type="text" title="{{ __("label.due") }}" value="{{ format($row['dateToFinish'])->date(__("text.anytime")) }}" class="duedates secretInput" data-id="{{ $row['id'] }}" name="date" />
            </div>
            <div class="col-md-8" style="padding-top:5px;">
                <div class="right">

                    <x-global::dropdownPill
                        :selectedKey="$row['storypoints']"
                        :type="'effort'"
                        :selectedClass="'label-default'"
                        :parentId="$row['id']"
                        :options="$efforts"
                        :submit="'#ticketboxForm'.$row['id']"
                    />

                    <x-global::dropdownPill
                        :linkStyle="'background-color:'.$row['milestoneColor']"
                        :selectedKey="$row['milestoneid']"
                        :type="'milestone'"
                        :selectedClass="'label-default'"
                        :parentId="$row['id']"
                        :options="$milestones[$row['projectId']] ?? []"
                        :extraClass="'colorized'"
                        :submit="'#ticketboxForm'.$row['id']"
                    />

                    <x-global::dropdownPill
                        :selectedKey="$row['status']"
                        :type="'status'"
                        :selectedClass="$statusLabels[$row['projectId']][$row['status']]['class']"
                        :parentId="$row['id']"
                        :options="$statusLabels[$row['projectId']] ?? []"
                        :extraClass="'colorized'"
                        :submit="'#ticketboxForm'.$row['id']"
                    />

                </div>
            </div>
        </div>
    </form>
</div>

