@props([
    'ticket' => [], //ticket object or array
    'timer' => [],
    'efforts' => [],
    'milestones' => [],
    'priorities' => [],
    'statuses' => [],
    'id' => '',
    'showProject' => true,
    'type' => 'full'
])

@if(empty($id) === false)

    <div hx-get="{{ BASE_URL }}/hx/tickets/ticketCard/get?id={{ $id }}"
         hx-trigger="load"
         hx-swap="innerhtml"
    >
        <x-global::content.card>
            <x-global::elements.loadingText type="card"/>
        </x-global::content.card>

    </div>

@else

    <x-global::content.card class="moveable-card">
        <div class="flex">
            <div class="ticket-title leading-none">
                @if($type !== 'kanban')
                    <small>{{ $ticket['projectName'] }}</small><br/>
                @endif
                <div class="join pt-sm">
                    @if ($ticket['dependingTicketId'] > 0)
                        <a href="#/tickets/showTicket/{{ $ticket['dependingTicketId'] }}"
                           class="join-item link link-primary link-hover">{{ $ticket['parentHeadline'] }}</a>
                        //
                    @endif

                    <a href="#/tickets/showTicket/{{ $ticket['id'] }}"
                       class="join-item link link-primary link-hover"><strong>{{ $ticket['headline'] }}</strong></a>
                </div>
            </div>
            <div class="timerContainer flex flex-auto justify-end" id="timerContainer-{{ $ticket['id'] }}">
                @if($type !== 'kanban')
                    <div class="scheduler btn btn-sm btn-ghost btn-circle">
                        @if( $ticket['editFrom'] != "0000-00-00 00:00:00" && $ticket['editFrom'] != "1969-12-31 00:00:00")
                            <x-global::content.icon icon="event_available" class="text-accent text-lg" data-tippy-content="{{ __('text.schedule_to_start_on') }} {{ format($ticket['editFrom'])->date() }}"/>
                        @else
                            <x-global::content.icon icon="event_busy" class="text-accent text-lg" data-tippy-content="{{ __('text.not_scheduled_drag_ai') }}" />
                        @endif
                    </div>
                @endif
                @include("tickets::includes.ticketsubmenu", ["ticket" => $ticket, "onTheClock" => $timer])
            </div>
        </div>

        <div class="flex {{ ($type === 'kanban') ? 'flex-col' : '' }} ">
            <div class="flex flex-grow justify-start">

                <x-tickets::chips.duedate :ticket="(object)$ticket" variant="chip" content-role="link"  />

{{--                <i class="fa-solid fa-business-time infoIcon"--}}
{{--                   data-tippy-content=" {{ __('label.due') }}"></i>--}}

{{--                <input type="text" title="{{ __('label.due') }}"--}}
{{--                       value="{{ format($ticket['dateToFinish'])->date(__('text.anytime')) }}"--}}
{{--                       class="duedates secretInput" data-id="{{ $ticket['id'] }}" name="date" />--}}
            </div>

            <div class="flex flex-grow flex-wrap {{ ($type === 'kanban') ? 'justify-start' : 'justify-end' }} gap-x-xs">

                        <x-tickets::chips.priority-select
                            :priorities="$priorities"
                            :ticket="(object)$ticket"
                            :showLabel="false"
                            dropdown-position="right" />

                        <x-tickets::chips.status-select
                            :statuses="$statuses"
                            :ticket="(object)$ticket"
                            :showLabel="false"
                            dropdown-position="right" />

                        <x-tickets::chips.milestone-select
                            :milestones="$milestones"
                            :ticket="(object)$ticket"
                            :showLabel="false"
                            dropdown-position="right" />


{{--                    <x-global::actions.dropdown--}}
{{--                        label-text="<span class='text'>--}}
{{--                            {{ $ticket['storypoints'] != '' && $ticket['storypoints'] > 0 ? $efforts['' . $ticket['storypoints'] . ''] : __('label.story_points_unkown') }}--}}
{{--                                                        </span>&nbsp;<i class='fa fa-caret-down' aria-hidden='true'></i>"--}}
{{--                        contentRole="link" position="bottom" align="start"--}}
{{--                        class="dropdown ticketDropdown effortDropdown show"--}}
{{--                        id="effortDropdownMenuLink{{ $ticket['id'] }}">--}}

{{--                        <x-slot:menu>--}}
{{--                            <!-- Menu Header -->--}}
{{--                            <li class="nav-header border">{{ __('dropdown.how_big_todo') }}</li>--}}

{{--                            <!-- Dynamic Effort Menu Items -->--}}
{{--                            @foreach ($efforts as $effortKey => $effortValue)--}}
{{--                                <x-global::actions.dropdown.item variant="link"--}}
{{--                                                                 href="javascript:void(0)" :data-value="$ticket['id'] . '_' . $effortKey" :id="'ticketEffortChange_' . $ticket['id'] . $effortKey">--}}
{{--                                    {{ $effortValue }}--}}
{{--                                </x-global::actions.dropdown.item>--}}
{{--                            @endforeach--}}
{{--                        </x-slot:menu>--}}
{{--                    </x-global::actions.dropdown>--}}

{{--                    <x-global::actions.dropdown--}}
{{--                        label-text="<span class='text'>--}}
{{--                                        {{ $ticket['milestoneid'] != '' && $ticket['milestoneid'] != 0 ? $ticket['milestoneHeadline'] : __('label.no_milestone') }}--}}
{{--                                    </span>&nbsp;<i class='fa fa-caret-down' aria-hidden='true'></i>"--}}
{{--                        contentRole="link" position="bottom" align="start"--}}
{{--                        class="dropdown ticketDropdown milestoneDropdown colorized show"--}}
{{--                        style="background-color:{{ __( ($ticket['milestoneColor'] ?? '#ccc')) }}"--}}
{{--                        id="milestoneDropdownMenuLink{{ $ticket['id'] }}">--}}

{{--                        <x-slot:menu>--}}
{{--                            <!-- Menu Header -->--}}
{{--                            <li class="nav-header border">{{ __('dropdown.choose_milestone') }}</li>--}}

{{--                            <!-- No Milestone Menu Item -->--}}
{{--                            <x-global::actions.dropdown.item variant="link" href="javascript:void(0);"--}}
{{--                                                             data-label="{{ __('label.no_milestone') }}"--}}
{{--                                                             data-value="{{ $ticket['id'] }}_0_#b0b0b0" class="bg-[#b0b0b0]">--}}
{{--                                {{ __('label.no_milestone') }}--}}
{{--                            </x-global::actions.dropdown.item>--}}

{{--                            <!-- Dynamic Milestone Menu Items -->--}}
{{--                            @foreach ($milestones as $milestone)--}}
{{--                                <x-global::actions.dropdown.item variant="link"--}}
{{--                                                                 href="javascript:void(0);" :data-label="$milestone->headline" :data-value="$ticket['id'] .--}}
{{--                                                                '_' .--}}
{{--                                                                $milestone->id .--}}
{{--                                                                '_' .--}}
{{--                                                                $milestone->tags"--}}
{{--                                                                 :id="'ticketMilestoneChange_' .--}}
{{--                                                                $ticket['id'] .--}}
{{--                                                                $milestone->id" style="background-color:{{ $milestone->tags }}">--}}
{{--                                    {{ $milestone->headline }}--}}
{{--                                </x-global::actions.dropdown.item>--}}
{{--                            @endforeach--}}
{{--                        </x-slot:menu>--}}

{{--                    </x-global::actions.dropdown>--}}


{{--                    <x-global::actions.dropdown--}}
{{--                        label-text="<span class='text'>{!! $statusLabels[$ticket['status']]['name'] !!}</span>&nbsp;<i class='fa fa-caret-down' aria-hidden='true'></i>"--}}
{{--                        contentRole="link" position="bottom" align="start"--}}
{{--                        class="dropdown ticketDropdown statusDropdown colorized show {!! $statusLabels[$ticket['status']]['class'] !!}"--}}
{{--                        id="statusDropdownMenuLink{{ $ticket['id'] }}">--}}

{{--                        <x-slot:menu>--}}
{{--                            <!-- Menu Header -->--}}
{{--                            <li class="nav-header border">{{ __('dropdown.choose_status') }}</li>--}}

{{--                            <!-- Dynamic Status Menu Items -->--}}
{{--                            @foreach ($statusLabels as $key => $label)--}}
{{--                                <x-global::actions.dropdown.item variant="link"--}}
{{--                                                                 href="javascript:void(0);" :class="$label['class']" :data-label="$label['name']"--}}
{{--                                                                 :data-value="$ticket['id'] . '_' . $key . '_' . $label['class']" :id="'ticketStatusChange' . $ticket['id'] . $key">--}}
{{--                                    {{ $label['name'] }}--}}
{{--                                </x-global::actions.dropdown.item>--}}
{{--                            @endforeach--}}
{{--                        </x-slot:menu>--}}
{{--                    </x-global::actions.dropdown>--}}
            </div>
        </div>
    </x-global::content.card>

@endif
