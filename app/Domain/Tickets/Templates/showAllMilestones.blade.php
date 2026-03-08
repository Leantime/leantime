@php
    $sprints = $tpl->get('sprints');
    $searchCriteria = $tpl->get('searchCriteria');
    $currentSprint = $tpl->get('currentSprint');
    $allTickets = $tpl->get('allTickets');
    $allTicketGroups = $tpl->get('allTickets');
    $todoTypeIcons = $tpl->get('ticketTypeIcons');
    $efforts = $tpl->get('efforts');
    $priorities = $tpl->get('priorities');
    $statusLabels = $tpl->get('allTicketStates');
    $numberofColumns = count($tpl->get('allTicketStates')) - 1;
    $size = floor(100 / $numberofColumns);
@endphp

@php $tpl->displaySubmodule('tickets-timelineHeader') @endphp

<div class="maincontent">

    @php $tpl->displaySubmodule('tickets-timelineTabs') @endphp

    <div class="maincontentinner">

        {!! $tpl->displayNotification() !!}

        <div class="tw:flex tw:justify-between tw:items-start">
            <div>
                @dispatchEvent('filters.afterLefthandSectionOpen')
                @php
                    $tpl->displaySubmodule('tickets-ticketNewBtn');
                    $tpl->displaySubmodule('tickets-ticketFilter');
                @endphp
                @dispatchEvent('filters.beforeLefthandSectionClose')
            </div>

            <div>
                <div class="pull-right">
                    @dispatchEvent('filters.afterRighthandSectionOpen')
                    <div id="tableButtons" class="tw:inline-block"></div>
                    @dispatchEvent('filters.beforeRighthandSectionClose')
                </div>
            </div>
        </div>

        <div class="clearfix tw:mb-5"></div>

        @php
            if (isset($allTicketGroups['all'])) {
                $allTickets = $allTicketGroups['all']['items'];
            }
        @endphp

        @foreach($allTicketGroups as $group)
            @if($group['label'] != 'all')
                <h5 class="accordionTitle {{ $group['class'] }}" @if(!empty($group['color'])) style="color:{{ htmlspecialchars($group['color']) }}" @endif id="accordion_link_{{ $group['id'] }}">
                    <a href="javascript:void(0)" class="accordion-toggle" id="accordion_toggle_{{ $group['id'] }}" onclick="leantime.snippets.accordionToggle('{{ $group['id'] }}');">
                        <x-globals::elements.icon name="expand_more" />{{ $group['label'] }} ({{ count($group['items']) }})
                    </a>
                </h5>
                <div class="simpleAccordionContainer" id="accordion_content-{{ $group['id'] }}">
            @endif

            @php $allTickets = $group['items']; @endphp

            @dispatchEvent('allTicketsTable.before', ['tickets' => $allTickets])
            <x-globals::elements.table :datatable="true" class="tw:w-full tw:overflow-x-auto">
                <x-slot:colgroup>
                    <colgroup>
                        <col class="con1"><col class="con0"><col class="con1"><col class="con0">
                        <col class="con1"><col class="con0"><col class="con1"><col class="con0">
                        <col class="con1"><col class="con0"><col class="con1">
                    </colgroup>
                </x-slot:colgroup>
                @dispatchEvent('allTicketsTable.beforeHead', ['tickets' => $allTickets])
                <x-slot:head>
                @dispatchEvent('allTicketsTable.beforeHeadRow', ['tickets' => $allTickets])
                <tr>
                    <th>{{ __('label.title') }}</th>
                    <th>{{ __('label.todo_type') }}</th>
                    <th>{{ __('label.progress') }}</th>
                    <th class="milestone-col">{{ __('label.dependent_on') }}</th>
                    <th>{{ __('label.todo_status') }}</th>
                    <th class="user-col">{{ __('label.owner') }}</th>
                    <th>{{ __('label.planned_start_date') }}</th>
                    <th>{{ __('label.planned_end_date') }}</th>
                    <th>{{ __('label.planned_hours') }}</th>
                    <th>{{ __('label.estimated_hours_remaining') }}</th>
                    <th>{{ __('label.booked_hours') }}</th>
                    <th class="no-sort" scope="col"><span class="sr-only">{{ __('label.actions') }}</span></th>
                </tr>
                @dispatchEvent('allTicketsTable.afterHeadRow', ['tickets' => $allTickets])
                </x-slot:head>
                @dispatchEvent('allTicketsTable.afterHead', ['tickets' => $allTickets])
                <tbody>
                    @dispatchEvent('allTicketsTable.beforeFirstRow', ['tickets' => $allTickets])
                    @foreach($allTickets as $rowNum => $row)
                        <tr>
                            @dispatchEvent('allTicketsTable.afterRowStart', ['rowNum' => $rowNum, 'tickets' => $allTickets])
                            <td data-order="{{ e($row['headline']) }}">
                                @if($row['type'] == 'milestone')
                                    <a href="#/tickets/editMilestone/{{ e($row['id']) }}">{{ e($row['headline']) }}</a>
                                @else
                                    <a href="#/tickets/showTicket/{{ e($row['id']) }}">{{ e($row['headline']) }}</a>
                                @endif
                            </td>
                            <td>{{ __('label.' . strtolower($row['type'])) }}</td>

                            <td>
                                @if($row['type'] == 'milestone')
                                    <div hx-trigger="load"
                                         hx-target="this"
                                         hx-swap="innerHTML"
                                         hx-get="{{ BASE_URL }}/hx/tickets/milestones/progress?milestoneId={{ $row['id'] }}&view=Progress"
                                         aria-live="polite">
                                        <div class="htmx-indicator" role="status">
                                            {{ __('label.calculating_progress') }}
                                        </div>
                                    </div>
                                @endif
                            </td>

                            @php
                                if ($row['milestoneid'] != '' && $row['milestoneid'] != 0) {
                                    $milestoneHeadline = $tpl->escape($row['milestoneHeadline']);
                                } else {
                                    $milestoneHeadline = __('label.no_milestone');
                                }
                            @endphp

                            <td data-order="{{ $milestoneHeadline }}">
                                <x-tickets::chips.milestone-select
                                    :ticket="(object)$row"
                                    :milestones="$tpl->get('milestones')"
                                />
                            </td>

                            @php
                                $sortKey = $statusLabels[$row['status']]['sortKey'] ?? 0;
                            @endphp
                            <td data-order="{{ $sortKey }}">
                                <x-tickets::chips.status-select
                                    :ticket="(object)$row"
                                    :statuses="$statusLabels"
                                />
                            </td>

                            <td data-order="{{ $row['editorFirstname'] != '' ? e($row['editorFirstname']) : __('dropdown.not_assigned') }}">
                                <x-globals::actions.user-select
                                    :entityId="$row['id']"
                                    :assignedUserId="$row['editorId']"
                                    :assignedName="$row['editorFirstname']"
                                    :users="$tpl->get('users')"
                                    :showNameLabel="true"
                                    :showArrowIcon="true"
                                    :showUnassign="false"
                                />
                            </td>

                            <td data-order="{{ $row['editFrom'] }}">
                                <x-globals::elements.icon name="calendar_month" /><input type="text" title="{{ __('label.planned_start_date') }}" value="{{ format($row['editFrom'])->date() }}" class="editFromDate secretInput milestoneEditFromAsync fromDateTicket-{{ $row['id'] }}" data-id="{{ $row['id'] }}" name="editFrom"/>
                            </td>

                            <td data-order="{{ $row['editTo'] }}">
                                <x-globals::elements.icon name="calendar_month" /><input type="text" title="{{ __('label.planned_end_date') }}" value="{{ format($row['editTo'])->date() }}" class="editToDate secretInput milestoneEditToAsync toDateTicket-{{ $row['id'] }}" data-id="{{ $row['id'] }}" name="editTo"/>
                            </td>

                            <td data-order="{{ $row['planHours'] }}">{{ $row['planHours'] }}</td>
                            <td data-order="{{ $row['hourRemaining'] }}">{{ $row['hourRemaining'] }}</td>
                            <td data-order="{{ $row['bookedHours'] }}">{{ $row['bookedHours'] }}</td>

                            <td>
                                @if($login::userIsAtLeast($roles::$editor))
                                    <x-globals::actions.dropdown-menu>
                                        <li class="nav-header border">{{ __('subtitles.todo') }}</li>
                                        <li><a href="#/tickets/editMilestone/{{ $row['id'] }}" class="ticketModal"><x-globals::elements.icon name="edit" /> {{ __('links.edit_milestone') }}</a></li>
                                        <li><a href="#/tickets/moveTicket/{{ $row['id'] }}" class="moveTicketModal sprintModal"><x-globals::elements.icon name="swap_horiz" /> {{ __('links.move_milestone') }}</a></li>
                                        <li><a href="#/tickets/delMilestone/{{ $row['id'] }}" class="delete"><x-globals::elements.icon name="delete" /> {{ __('links.delete') }}</a></li>
                                        <li><a href="{{ BASE_URL }}/tickets/showAll?search=true&milestone={{ $row['id'] }}">{{ __('links.view_todos') }}</a></li>
                                    </x-globals::actions.dropdown-menu>
                                @endif
                            </td>
                            @dispatchEvent('allTicketsTable.beforeRowEnd', ['tickets' => $allTickets, 'rowNum' => $rowNum])
                        </tr>
                    @endforeach
                    @dispatchEvent('allTicketsTable.afterLastRow', ['tickets' => $allTickets])
                </tbody>
                @dispatchEvent('allTicketsTable.afterBody', ['tickets' => $allTickets])
            </x-globals::elements.table>
            @dispatchEvent('allTicketsTable.afterClose', ['tickets' => $allTickets])

            @if($group['label'] != 'all')
                </div>
            @endif
        @endforeach

    </div>
</div>

<script type="text/javascript">

    @dispatchEvent('scripts.afterOpen')

    jQuery(document).ready(function(){

        @if($login::userIsAtLeast($roles::$editor))
        leantime.ticketsController.initUserDropdown();
        leantime.ticketsController.initMilestoneDropdown();
        leantime.ticketsController.initEffortDropdown();
        leantime.ticketsController.initStatusDropdown();
        leantime.ticketsController.initSprintDropdown();
        leantime.ticketsController.initMilestoneDatesAsyncUpdate();
        @else
            leantime.authController.makeInputReadonly(".maincontentinner");
        @endif

        leantime.ticketsController.initMilestoneTable("{{ $searchCriteria['groupBy'] }}");

        @dispatchEvent('scripts.beforeClose')
    });
</script>
