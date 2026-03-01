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
                        <div id="tableButtons" style="display:inline-block"></div>
                        @dispatchEvent('filters.beforeRighthandSectionClose')
                    </div>
                </div>
            </div>

            @dispatchEvent('filters.beforeFormClose')

            <div class="clearfix"></div>

        </form>

        @dispatchEvent('allTicketsTable.before', ['tickets' => $allTickets])

        <div style="overflow-x: auto;">
        <table id="allTicketsTable" class="table table-bordered display" style="width:100%">
            <colgroup>
                <col class="con1"><col class="con0"><col class="con1"><col class="con0">
                <col class="con1"><col class="con0"><col class="con1"><col class="con0">
                <col class="con1"><col class="con0"><col class="con1"><col class="con0">
            </colgroup>
            @dispatchEvent('allTicketsTable.beforeHead', ['tickets' => $allTickets])
            <thead>
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
                <th class="no-sort"></th>
            </tr>
            @dispatchEvent('allTicketsTable.afterHeadRow', ['tickets' => $allTickets])
            </thead>
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
                            @php
                                $milestoneOptions = [0 => ['name' => __('label.no_milestone'), 'class' => '#b0b0b0']];
                                foreach ($tpl->get('milestones') as $ms) {
                                    $milestoneOptions[$ms->id] = ['name' => $ms->headline, 'class' => $ms->tags];
                                }
                            @endphp
                            <x-globals::actions.chip
                                content-role="milestone"
                                :parentId="$row->id"
                                selectedClass="label-default"
                                color="{{ e($row->milestoneColor) }}"
                                :selectedKey="$row->milestoneid ?: 0"
                                :options="$milestoneOptions"
                                :colorized="true"
                                headerLabel="{{ __('dropdown.choose_milestone') }}"
                            />
                        </td>

                        @php
                            if (isset($statusLabels[$row->status])) {
                                $class = $statusLabels[$row->status]['class'];
                                $name = $statusLabels[$row->status]['name'];
                            } else {
                                $class = 'label-important';
                                $name = 'new';
                            }
                        @endphp

                        <td class="dropdown-cell" data-order="{{ $name }}">
                            <x-globals::actions.chip
                                content-role="status"
                                :parentId="$row->id"
                                :selectedClass="$class"
                                :selectedKey="$row->status"
                                :options="$statusLabels"
                                :colorized="true"
                                headerLabel="{{ __('dropdown.choose_status') }}"
                            />
                        </td>

                        <td class="dropdown-cell" data-order="{{ $row->editorFirstname != '' ? e($row->editorFirstname) : __('dropdown.not_assigned') }}">
                            <div class="dropdown ticketDropdown userDropdown noBg">
                                <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown" id="userDropdownMenuLink{{ $row->id }}" aria-haspopup="true" aria-expanded="false">
                                    <span class="text">
                                        @if($row->editorFirstname != '')
                                            <span id="userImage{{ $row->id }}"><img src="{{ BASE_URL }}/api/users?profileImage={{ $row->editorId }}" width="25" style="vertical-align: middle; margin-right:5px;"/></span><span id="user{{ $row->id }}"> {{ e($row->editorFirstname) }}</span>
                                        @else
                                            <span id="userImage{{ $row->id }}"><img src="{{ BASE_URL }}/api/users?profileImage=false" width="25" style="vertical-align: middle; margin-right:5px;"/></span><span id="user{{ $row->id }}">{{ __('dropdown.not_assigned') }}</span>
                                        @endif
                                    </span>
                                    &nbsp;<x-global::elements.icon name="arrow_drop_down" />
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="userDropdownMenuLink{{ $row->id }}">
                                    <li class="nav-header border">{{ __('dropdown.choose_user') }}</li>
                                    @foreach($tpl->get('users') as $user)
                                        <li class="dropdown-item">
                                            <a href="javascript:void(0);" onclick="document.activeElement.blur();" data-label="{{ sprintf(__('text.full_name'), e($user['firstname']), e($user['lastname'])) }}" data-value="{{ $row->id }}_{{ $user['id'] }}_{{ $user['profileId'] }}" id="userStatusChange{{ $row->id }}{{ $user['id'] }}"><img src="{{ BASE_URL }}/api/users?profileImage={{ $user['id'] }}" width="25" style="vertical-align: middle; margin-right:5px;"/>{{ sprintf(__('text.full_name'), e($user['firstname']), e($user['lastname'])) }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </td>

                        <td data-order="{{ $row->editFrom }}">
                            <x-global::elements.icon name="calendar_month" /><input type="text" title="{{ __('label.planned_start_date') }}" value="{{ format($row->editFrom)->date() }}" class="editFromDate secretInput milestoneEditFromAsync fromDateTicket-{{ $row->id }}" data-id="{{ $row->id }}" name="editFrom"/>
                        </td>

                        <td data-order="{{ $row->editTo }}">
                            <x-global::elements.icon name="calendar_month" /><input type="text" title="{{ __('label.planned_end_date') }}" value="{{ format($row->editTo)->date() }}" class="editToDate secretInput milestoneEditToAsync toDateTicket-{{ $row->id }}" data-id="{{ $row->id }}" name="editTo"/>
                        </td>

                        <td data-order="{{ $row->planHours }}">{{ $row->planHours }}</td>
                        <td data-order="{{ $row->hourRemaining }}">{{ $row->hourRemaining }}</td>
                        <td data-order="{{ $row->bookedHours }}">{{ $row->bookedHours }}</td>

                        <td data-order="{{ $row->percentDone }}">
                            <x-global::progress :value="$row->percentDone" size="sm" />
                        </td>
                        <td>
                            @if($login::userIsAtLeast($roles::$editor))
                                <x-globals::actions.dropdown-menu>
                                    <li class="nav-header border">{{ __('subtitles.todo') }}</li>
                                    <li><a href="#/tickets/editMilestone/{{ $row->id }}" class="ticketModal"><x-global::elements.icon name="edit" /> {{ __('links.edit_milestone') }}</a></li>
                                    <li><a href="#/tickets/moveTicket/{{ $row->id }}" class="moveTicketModal sprintModal"><x-global::elements.icon name="swap_horiz" /> {{ __('links.move_milestone') }}</a></li>
                                    <li><a href="#/tickets/delMilestone/{{ $row->id }}" class="delete"><x-global::elements.icon name="delete" /> {{ __('links.delete') }}</a></li>
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
        </table>
        </div>
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
