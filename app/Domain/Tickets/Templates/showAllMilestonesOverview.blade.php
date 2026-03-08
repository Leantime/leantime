@php
    $sprints = $tpl->get('sprints');
    $searchCriteria = $tpl->get('searchCriteria');
    $currentSprint = $tpl->get('currentSprint');
    $todoTypeIcons = $tpl->get('ticketTypeIcons');
    $efforts = $tpl->get('efforts');
    $statusLabels = $tpl->get('allTicketStates');
    $allTickets = $tpl->get('allTickets');
    $numberofColumns = count($tpl->get('allTicketStates')) - 1;
    $size = floor(100 / $numberofColumns);
@endphp

@php $tpl->displaySubmodule('tickets-portfolioHeader') @endphp

<div class="maincontent">
    @php $tpl->displaySubmodule('tickets-portfolioTabs') @endphp

    <div class="maincontentinner">

        {!! $tpl->displayNotification() !!}

        <form action="" method="get" id="ticketSearch">

            @dispatchEvent('filters.afterFormOpen')

            <input type="hidden" value="1" name="search"/>
            <div class="tw:flex tw:justify-between tw:items-start">
                <div>
                    @dispatchEvent('filters.afterLefthandSectionOpen')
                    @php
                        $tpl->displaySubmodule('tickets-ticketNewBtn');
                        $tpl->displaySubmodule('tickets-ticketFilter');
                    @endphp
                    @dispatchEvent('filters.beforeLefthandSectionClose')
                </div>

                <div class="center">
                    @dispatchEvent('filters.afterCenterSectionOpen')
                    @dispatchEvent('filters.beforeCenterSectionClose')
                </div>
                <div>
                    <div class="pull-right">
                        @dispatchEvent('filters.afterRighthandSectionOpen')
                        <div id="tableButtons" class="tw:inline-block"></div>
                        @dispatchEvent('filters.beforeRighthandSectionClose')
                    </div>
                </div>
            </div>

            @dispatchEvent('filters.beforeFormClose')

            <div class="clearfix"></div>

        </form>

        @dispatchEvent('allTicketsTable.before', ['tickets' => $allTickets])

        <x-globals::elements.table id="allTicketsTable" :datatable="true" class="tw:w-full tw:overflow-x-auto">
            <x-slot:colgroup>
                <colgroup>
                    <col class="con1"><col class="con0"><col class="con1"><col class="con0">
                    <col class="con1"><col class="con0"><col class="con1"><col class="con0">
                    <col class="con1"><col class="con0"><col class="con1"><col class="con0">
                </colgroup>
            </x-slot:colgroup>
            @dispatchEvent('allTicketsTable.beforeHead', ['tickets' => $allTickets])
            <x-slot:head>
            @dispatchEvent('allTicketsTable.beforeHeadRow', ['tickets' => $allTickets])
            <tr>
                <th>{{ __('label.project_name') }}</th>
                <th>{{ __('label.title') }}</th>
                <th class="milestone-col">{{ __('label.dependent_on') }}</th>
                <th>{{ __('label.todo_status') }}</th>
                <th class="user-col">{{ __('label.owner') }}</th>
                <th>{{ __('label.planned_start_date') }}</th>
                <th>{{ __('label.planned_end_date') }}</th>
                <th>{{ __('label.planned_hours') }}</th>
                <th>{{ __('label.estimated_hours_remaining') }}</th>
                <th>{{ __('label.booked_hours') }}</th>
                <th>{{ __('label.progress') }}</th>
                <th class="no-sort" scope="col"><span class="sr-only">{{ __('label.actions') }}</span></th>
            </tr>
            @dispatchEvent('allTicketsTable.afterHeadRow', ['tickets' => $allTickets])
            </x-slot:head>
            @dispatchEvent('allTicketsTable.afterHead', ['tickets' => $allTickets])
            <tbody>
                @dispatchEvent('allTicketsTable.beforeFirstRow', ['tickets' => $allTickets])
                @foreach($allTickets as $rowNum => $row)
                    <tr>
                        <td><h4>{{ $row->projectName }}</h4></td>
                        @dispatchEvent('allTicketsTable.afterRowStart', ['rowNum' => $rowNum, 'tickets' => $allTickets])
                        <td data-order="{{ e($row->headline) }}"><a href="#/tickets/editMilestone/{{ e($row->id) }}">{{ e($row->headline) }}</a></td>

                        @php
                            if ($row->milestoneid != '' && $row->milestoneid != 0) {
                                $milestoneHeadline = $tpl->escape($row->milestoneHeadline);
                            } else {
                                $milestoneHeadline = __('label.no_milestone');
                            }
                        @endphp

                        <td class="dropdown-cell" data-order="{{ $milestoneHeadline }}">
                            <x-tickets::chips.milestone-select
                                :ticket="$row"
                                :milestones="$tpl->get('milestones')"
                            />
                        </td>

                        @php
                            $name = $statusLabels[$row->status]['name'] ?? 'new';
                        @endphp
                        <td class="dropdown-cell" data-order="{{ $name }}">
                            <x-tickets::chips.status-select
                                :ticket="$row"
                                :statuses="$statusLabels"
                            />
                        </td>

                        <td class="dropdown-cell" data-order="{{ $row->editorFirstname != '' ? e($row->editorFirstname) : __('dropdown.not_assigned') }}">
                            <x-globals::actions.user-select
                                :entityId="$row->id"
                                :assignedUserId="$row->editorId"
                                :assignedName="$row->editorFirstname"
                                :users="$tpl->get('users')"
                                :showNameLabel="true"
                                :showArrowIcon="true"
                                :showUnassign="false"
                            />
                        </td>

                        <td data-order="{{ $row->editFrom }}">
                            <x-globals::elements.icon name="calendar_month" /><input type="text" title="{{ __('label.planned_start_date') }}" value="{{ format($row->editFrom)->date() }}" class="editFromDate secretInput milestoneEditFromAsync fromDateTicket-{{ $row->id }}" data-id="{{ $row->id }}" name="editFrom"/>
                        </td>

                        <td data-order="{{ $row->editTo }}">
                            <x-globals::elements.icon name="calendar_month" /><input type="text" title="{{ __('label.planned_end_date') }}" value="{{ format($row->editTo)->date() }}" class="editToDate secretInput milestoneEditToAsync toDateTicket-{{ $row->id }}" data-id="{{ $row->id }}" name="editTo"/>
                        </td>

                        <td data-order="{{ $row->planHours }}">{{ $row->planHours }}</td>
                        <td data-order="{{ $row->hourRemaining }}">{{ $row->hourRemaining }}</td>
                        <td data-order="{{ $row->bookedHours }}">{{ $row->bookedHours }}</td>

                        <td data-order="{{ $row->percentDone }}">
                            <x-globals::feedback.progress :value="$row->percentDone" size="sm" />
                        </td>
                        <td>
                            @if($login::userIsAtLeast($roles::$editor))
                                <x-globals::actions.dropdown-menu>
                                    <li class="nav-header border">{{ __('subtitles.todo') }}</li>
                                    <li><a href="#/tickets/editMilestone/{{ $row->id }}" class="ticketModal"><x-globals::elements.icon name="edit" /> {{ __('links.edit_milestone') }}</a></li>
                                    <li><a href="#/tickets/moveTicket/{{ $row->id }}" class="moveTicketModal sprintModal"><x-globals::elements.icon name="swap_horiz" /> {{ __('links.move_milestone') }}</a></li>
                                    <li><a href="#/tickets/delMilestone/{{ $row->id }}" class="delete"><x-globals::elements.icon name="delete" /> {{ __('links.delete') }}</a></li>
                                    <li><a href="{{ BASE_URL }}/tickets/showAll?search=true&milestone={{ $row->id }}">{{ __('links.view_todos') }}</a></li>
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

    </div>
</div>

<script type="text/javascript">

    @dispatchEvent('scripts.afterOpen')

    jQuery(document).ready(function(){
    });

    leantime.ticketsController.initTicketSearchSubmit("{{ BASE_URL }}/tickets/showAll");

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

</script>
