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
                    <div id="tableButtons" style="display:inline-block"></div>
                    @dispatchEvent('filters.beforeRighthandSectionClose')
                </div>
            </div>
        </div>

        <div class="clearfix" style="margin-bottom: 20px;"></div>

        @php
            if (isset($allTicketGroups['all'])) {
                $allTickets = $allTicketGroups['all']['items'];
            }
        @endphp

        @foreach($allTicketGroups as $group)
            @if($group['label'] != 'all')
                <h5 class="accordionTitle {{ $group['class'] }}" @if(!empty($group['color'])) style="color:{{ htmlspecialchars($group['color']) }}" @endif id="accordion_link_{{ $group['id'] }}">
                    <a href="javascript:void(0)" class="accordion-toggle" id="accordion_toggle_{{ $group['id'] }}" onclick="leantime.snippets.accordionToggle('{{ $group['id'] }}');">
                        <i class="fa fa-angle-down" aria-hidden="true"></i>{{ $group['label'] }} ({{ count($group['items']) }})
                    </a>
                </h5>
                <div class="simpleAccordionContainer" id="accordion_content-{{ $group['id'] }}">
            @endif

            @php $allTickets = $group['items']; @endphp

            @dispatchEvent('allTicketsTable.before', ['tickets' => $allTickets])
            <div style="overflow-x: auto;">
            <table class="table table-bordered display ticketTable" style="width:100%">
                <colgroup>
                    <col class="con1"><col class="con0"><col class="con1"><col class="con0">
                    <col class="con1"><col class="con0"><col class="con1"><col class="con0">
                    <col class="con1"><col class="con0"><col class="con1">
                </colgroup>
                @dispatchEvent('allTicketsTable.beforeHead', ['tickets' => $allTickets])
                <thead>
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
                    <th class="no-sort"></th>
                </tr>
                @dispatchEvent('allTicketsTable.afterHeadRow', ['tickets' => $allTickets])
                </thead>
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
                                @php
                                    $milestoneOptions = [0 => ['name' => __('label.no_milestone'), 'class' => '#b0b0b0']];
                                    foreach ($tpl->get('milestones') as $ms) {
                                        $milestoneOptions[$ms->id] = ['name' => $ms->headline, 'class' => $ms->tags];
                                    }
                                @endphp
                                <x-globals::dropdownPill
                                    type="milestone"
                                    :parentId="$row['id']"
                                    selectedClass="label-default"
                                    linkStyle="background-color:{{ e($row['milestoneColor']) }}"
                                    :selectedKey="$row['milestoneid'] ?: 0"
                                    :options="$milestoneOptions"
                                    :colorized="true"
                                    headerLabel="{{ __('dropdown.choose_milestone') }}"
                                />
                            </td>

                            @php
                                if (isset($statusLabels[$row['status']])) {
                                    $class = $statusLabels[$row['status']]['class'];
                                    $name = $statusLabels[$row['status']]['name'];
                                    $sortKey = $statusLabels[$row['status']]['sortKey'];
                                } else {
                                    $class = 'label-important';
                                    $name = 'new';
                                    $sortKey = 0;
                                }
                            @endphp

                            <td data-order="{{ $sortKey }}">
                                <x-globals::dropdownPill
                                    type="status"
                                    :parentId="$row['id']"
                                    :selectedClass="$class"
                                    :selectedKey="$row['status']"
                                    :options="$statusLabels"
                                    :colorized="true"
                                    headerLabel="{{ __('dropdown.choose_status') }}"
                                />
                            </td>

                            <td data-order="{{ $row['editorFirstname'] != '' ? e($row['editorFirstname']) : __('dropdown.not_assigned') }}">
                                <div class="dropdown ticketDropdown userDropdown noBg">
                                    <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown" id="userDropdownMenuLink{{ $row['id'] }}" aria-haspopup="true" aria-expanded="false">
                                        <span class="text">
                                            @if($row['editorFirstname'] != '')
                                                <span id="userImage{{ $row['id'] }}"><img src="{{ BASE_URL }}/api/users?profileImage={{ $row['editorId'] }}" width="25" style="vertical-align: middle; margin-right:5px;"/></span><span id="user{{ $row['id'] }}"> {{ e($row['editorFirstname']) }}</span>
                                            @else
                                                <span id="userImage{{ $row['id'] }}"><img src="{{ BASE_URL }}/api/users?profileImage=false" width="25" style="vertical-align: middle; margin-right:5px;"/></span><span id="user{{ $row['id'] }}">{{ __('dropdown.not_assigned') }}</span>
                                            @endif
                                        </span>
                                        &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="userDropdownMenuLink{{ $row['id'] }}">
                                        <li class="nav-header border">{{ __('dropdown.choose_user') }}</li>
                                        @foreach($tpl->get('users') as $user)
                                            <li class="dropdown-item">
                                                <a href="javascript:void(0);" onclick="document.activeElement.blur();" data-label="{{ sprintf(__('text.full_name'), e($user['firstname']), e($user['lastname'])) }}" data-value="{{ $row['id'] }}_{{ $user['id'] }}_{{ $user['profileId'] }}" id="userStatusChange{{ $row['id'] }}{{ $user['id'] }}"><img src="{{ BASE_URL }}/api/users?profileImage={{ $user['id'] }}" width="25" style="vertical-align: middle; margin-right:5px;"/>{{ sprintf(__('text.full_name'), e($user['firstname']), e($user['lastname'])) }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </td>

                            <td data-order="{{ $row['editFrom'] }}">
                                {{ __('label.due_icon') }}<input type="text" title="{{ __('label.planned_start_date') }}" value="{{ format($row['editFrom'])->date() }}" class="editFromDate secretInput milestoneEditFromAsync fromDateTicket-{{ $row['id'] }}" data-id="{{ $row['id'] }}" name="editFrom"/>
                            </td>

                            <td data-order="{{ $row['editTo'] }}">
                                {{ __('label.due_icon') }}<input type="text" title="{{ __('label.planned_end_date') }}" value="{{ format($row['editTo'])->date() }}" class="editToDate secretInput milestoneEditToAsync toDateTicket-{{ $row['id'] }}" data-id="{{ $row['id'] }}" name="editTo"/>
                            </td>

                            <td data-order="{{ $row['planHours'] }}">{{ $row['planHours'] }}</td>
                            <td data-order="{{ $row['hourRemaining'] }}">{{ $row['hourRemaining'] }}</td>
                            <td data-order="{{ $row['bookedHours'] }}">{{ $row['bookedHours'] }}</td>

                            <td>
                                @if($login::userIsAtLeast($roles::$editor))
                                    <x-globals::elements.dropdown>
                                        <li class="nav-header border">{{ __('subtitles.todo') }}</li>
                                        <li><a href="#/tickets/editMilestone/{{ $row['id'] }}" class="ticketModal"><i class="fa fa-edit" aria-hidden="true"></i> {{ __('links.edit_milestone') }}</a></li>
                                        <li><a href="#/tickets/moveTicket/{{ $row['id'] }}" class="moveTicketModal sprintModal"><i class="fa-solid fa-arrow-right-arrow-left" aria-hidden="true"></i> {{ __('links.move_milestone') }}</a></li>
                                        <li><a href="#/tickets/delMilestone/{{ $row['id'] }}" class="delete"><i class="fa fa-trash" aria-hidden="true"></i> {{ __('links.delete') }}</a></li>
                                        <li><a href="{{ BASE_URL }}/tickets/showAll?search=true&milestone={{ $row['id'] }}">{{ __('links.view_todos') }}</a></li>
                                    </x-globals::elements.dropdown>
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
