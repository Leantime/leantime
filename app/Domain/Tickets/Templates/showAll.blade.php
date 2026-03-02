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
    $newField = $tpl->get('newField');
    $numberofColumns = count($tpl->get('allTicketStates')) - 1;
    $size = floor(100 / $numberofColumns);
@endphp

{!! $tpl->displayNotification() !!}

@php $tpl->displaySubmodule('tickets-ticketHeader') @endphp

<div class="maincontent">

    @php $tpl->displaySubmodule('tickets-ticketBoardTabs') @endphp

    <div class="maincontentinner">

        <div class="ticket-toolbar tw:flex tw:items-center tw:justify-between tw:flex-wrap tw:gap-2 tw:mb-5">
            <div>
                @dispatchEvent('filters.afterLefthandSectionOpen')
                @php $tpl->displaySubmodule('tickets-ticketNewBtn'); @endphp
            </div>
            <div class="tw:flex tw:items-center tw:gap-2">
                @php $tpl->displaySubmodule('tickets-ticketFilter'); @endphp
                @dispatchEvent('filters.beforeLefthandSectionClose')
                @dispatchEvent('filters.afterRighthandSectionOpen')
                <div id="tableButtons" style="display:inline-block"></div>
                @dispatchEvent('filters.beforeRighthandSectionClose')
            </div>
        </div>

        @php
            if (isset($allTicketGroups['all'])) {
                $allTickets = $allTicketGroups['all']['items'];
            }
        @endphp

        @foreach($allTicketGroups as $group)
            @if($group['label'] != 'all')
                <h5 class="accordionTitle {{ $group['class'] }}" @if(!empty($group['color'])) style="color:{{ htmlspecialchars($group['color']) }}" @endif id="accordion_link_{{ $group['id'] }}">
                    <a href="javascript:void(0)" class="accordion-toggle" id="accordion_toggle_{{ $group['id'] }}" onclick="leantime.snippets.accordionToggle('{{ $group['id'] }}');">
                        <x-global::elements.icon name="expand_more" />{{ $group['label'] }}
                    </a>
                    <x-globals::elements.badge color="primary">{{ count($group['items']) }}</x-globals::elements.badge>
                </h5>

                <div class="simpleAccordionContainer" id="accordion_content-{{ $group['id'] }}">
            @endif

            @php $allTickets = $group['items']; @endphp

            @dispatchEvent('allTicketsTable.before', ['tickets' => $allTicketGroups])
            <table class="table table-bordered display ticketTable" style="width:100%">
                <colgroup>
                    <col class="con1"><col class="con0" style="max-width:200px;"><col class="con1"><col class="con0">
                    <col class="con1"><col class="con0"><col class="con1"><col class="con0">
                    <col class="con1"><col class="con0"><col class="con1"><col class="con0">
                    <col class="con1"><col class="con0">
                </colgroup>
                @dispatchEvent('allTicketsTable.beforeHead', ['tickets' => $allTickets])
                <thead>
                    @dispatchEvent('allTicketsTable.beforeHeadRow', ['tickets' => $allTickets])
                    <tr>
                        <th class="id-col">{{ __('label.id') }}</th>
                        <th style="max-width: 350px;">{{ __('label.title') }}</th>
                        <th class="status-col">{{ __('label.todo_status') }}</th>
                        <th class="milestone-col">{{ __('label.milestone') }}</th>
                        <th class="effort-col">{{ __('label.effort') }}</th>
                        <th class="priority-col">{{ __('label.priority') }}</th>
                        <th class="user-col">{{ __('label.editor') }}.</th>
                        <th class="sprint-col">{{ __('label.sprint') }}</th>
                        <th class="tags-col">{{ __('label.tags') }}</th>
                        <th class="duedate-col">{{ __('label.due_date') }}</th>
                        <th class="planned-hours-col">{{ __('label.planned_hours') }}</th>
                        <th class="remaining-hours-col">{{ __('label.estimated_hours_remaining') }}</th>
                        <th class="booked-hours-col">{{ __('label.booked_hours') }}</th>
                        <th class="no-sort"></th>
                    </tr>
                    @dispatchEvent('allTicketsTable.afterHeadRow', ['tickets' => $allTickets])
                </thead>
                @dispatchEvent('allTicketsTable.afterHead', ['tickets' => $allTickets])
                <tbody>
                    @dispatchEvent('allTicketsTable.beforeFirstRow', ['tickets' => $allTickets])
                    @foreach($allTickets as $rowNum => $row)
                        <tr style="height:1px;">
                            @dispatchEvent('allTicketsTable.afterRowStart', ['rowNum' => $rowNum, 'tickets' => $allTickets])
                            <td data-order="{{ e($row['id']) }}">
                                #{{ e($row['id']) }}
                            </td>

                            <td data-order="{{ e($row['headline']) }}" class="title-cell">
                                @if($row['dependingTicketId'] > 0)
                                    <small><a href="#/tickets/showTicket/{{ $row['dependingTicketId'] }}">{{ e($row['parentHeadline']) }}</a></small> //<br />
                                @endif
                                <a class="ticketModal" href="#/tickets/showTicket/{{ e($row['id']) }}" title="{{ e($row['headline']) }}">{{ e($row['headline']) }}</a>
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
                            <td data-order="{{ $name }}">
                                <x-globals::actions.chip
                                    content-role="status"
                                    :parentId="$row['id']"
                                    :selectedClass="$class"
                                    :selectedKey="$row['status']"
                                    :options="$statusLabels"
                                    :colorized="true"
                                    headerLabel="{{ __('dropdown.choose_status') }}"
                                />
                            </td>

                            @php
                                if ($row['milestoneid'] != '' && $row['milestoneid'] != 0) {
                                    $milestoneHeadline = $tpl->escape($row['milestoneHeadline']);
                                } else {
                                    $milestoneHeadline = __('label.no_milestone');
                                }
                            @endphp

                            @php
                                $milestoneOptions = [0 => ['name' => __('label.no_milestone'), 'class' => '#b0b0b0']];
                                foreach ($tpl->get('milestones') as $ms) {
                                    $milestoneOptions[$ms->id] = ['name' => $ms->headline, 'class' => $ms->tags];
                                }
                            @endphp
                            <td data-order="{{ $milestoneHeadline }}">
                                <x-globals::actions.chip
                                    content-role="milestone"
                                    :parentId="$row['id']"
                                    selectedClass="label-default"
                                    color="{{ e($row['milestoneColor']) }}"
                                    :selectedKey="$row['milestoneid'] ?: 0"
                                    :options="$milestoneOptions"
                                    :colorized="true"
                                    headerLabel="{{ __('dropdown.choose_milestone') }}"
                                />
                            </td>

                            <td data-order="{{ $row['storypoints'] ? ($efforts['' . $row['storypoints'] . ''] ?? '?') : __('label.story_points_unkown') }}">
                                <x-globals::actions.chip
                                    content-role="effort"
                                    :parentId="$row['id']"
                                    selectedClass="label-default"
                                    :selectedKey="'' . $row['storypoints']"
                                    :options="$efforts"
                                    headerLabel="{{ __('dropdown.how_big_todo') }}"
                                />
                            </td>

                            @php
                                $priorityLabel = ($row['priority'] != '' && $row['priority'] > 0) ? ($priorities[$row['priority']] ?? __('label.priority_unkown')) : __('label.priority_unkown');
                            @endphp
                            <td data-order="{{ $priorityLabel }}">
                                <x-globals::actions.chip
                                    content-role="priority"
                                    :parentId="$row['id']"
                                    selectedClass="label-default priority-bg-{{ $row['priority'] }}"
                                    :selectedKey="$row['priority']"
                                    :options="$priorities"
                                    headerLabel="{{ __('dropdown.select_priority') }}"
                                />
                            </td>

                            <td data-order="{{ $row['editorFirstname'] != '' ? e($row['editorFirstname']) : __('dropdown.not_assigned') }}" class="user-cell">
                                <div class="dropdown ticketDropdown userDropdown noBg show f-left">
                                    <a class="dropdown-toggle" href="javascript:void(0);" role="button" id="userDropdownMenuLink{{ $row['id'] }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="{{ $row['editorFirstname'] != '' ? e($row['editorFirstname']) : __('dropdown.not_assigned') }}">
                                        <span class="text">
                                            @if($row['editorFirstname'] != '')
                                                <span id="userImage{{ $row['id'] }}"><img src="{{ BASE_URL }}/api/users?profileImage={{ $row['editorId'] }}" width="25" style="vertical-align: middle;"/></span><span id="user{{ $row['id'] }}" class="user-name-label">{{ e($row['editorFirstname']) }}</span>
                                            @else
                                                <span id="userImage{{ $row['id'] }}"><img src="{{ BASE_URL }}/api/users?profileImage=false" width="25" style="vertical-align: middle;"/></span><span id="user{{ $row['id'] }}" class="user-name-label">{{ __('dropdown.not_assigned') }}</span>
                                            @endif
                                        </span>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="userDropdownMenuLink{{ $row['id'] }}">
                                        <li class="nav-header border">{{ __('dropdown.choose_user') }}</li>
                                        <li class="dropdown-item">
                                            <a href="javascript:void(0);" onclick="document.activeElement.blur();" data-label="{{ __('label.not_assigned_to_user') }}" data-value="{{ $row['id'] }}_0_0" id="userStatusChange{{ $row['id'] }}0">{{ __('label.not_assigned_to_user') }}</a>
                                        </li>
                                        @foreach($tpl->get('users') as $user)
                                            <li class="dropdown-item">
                                                <a href="javascript:void(0);" onclick="document.activeElement.blur();" data-label="{{ sprintf(__('text.full_name'), e($user['firstname']), e($user['lastname'])) }}" data-value="{{ $row['id'] }}_{{ $user['id'] }}_{{ $user['profileId'] }}" id="userStatusChange{{ $row['id'] }}{{ $user['id'] }}"><img src="{{ BASE_URL }}/api/users?profileImage={{ $user['id'] }}" width="25" style="vertical-align: middle; margin-right:5px;"/>{{ sprintf(__('text.full_name'), e($user['firstname']), e($user['lastname'])) }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </td>

                            @php
                                if ($row['sprint'] != '' && $row['sprint'] != 0 && $row['sprint'] != -1) {
                                    $sprintHeadline = $tpl->escape($row['sprintName']);
                                } else {
                                    $sprintHeadline = __('label.not_assigned_to_sprint');
                                }
                            @endphp

                            @php
                                $sprintOptions = [0 => __('label.not_assigned_to_sprint')];
                                if ($tpl->get('sprints')) {
                                    foreach ($tpl->get('sprints') as $sprintItem) {
                                        $sprintOptions[$sprintItem->id] = $sprintItem->name;
                                    }
                                }
                            @endphp
                            <td data-order="{{ $sprintHeadline }}">
                                <x-globals::actions.chip
                                    content-role="sprint"
                                    :parentId="$row['id']"
                                    selectedClass="label-default"
                                    :selectedKey="$row['sprint'] ?: 0"
                                    :options="$sprintOptions"
                                    headerLabel="{{ __('dropdown.choose_sprint') }}"
                                />
                            </td>

                            <td data-order="{{ $row['tags'] }}">
                                @if($row['tags'] != '')
                                    @php $tagsArray = explode(',', $row['tags']); @endphp
                                    <div class="tagsinput readonly">
                                        @foreach($tagsArray as $tag)
                                            <span class="tag"><span>{{ e($tag) }}</span></span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>

                            @php
                                if ($row['dateToFinish'] == '0000-00-00 00:00:00' || $row['dateToFinish'] == '1969-12-31 00:00:00') {
                                    $date = __('text.anytime');
                                } else {
                                    $dateObj = new DateTime($row['dateToFinish']);
                                    $date = $dateObj->format(__('language.dateformat'));
                                }
                            @endphp
                            <td data-order="{{ $row['dateToFinish'] }}">
                                <input type="text" title="{{ __('label.due') }}" value="{{ $date }}" class="quickDueDates secretInput" data-id="{{ $row['id'] }}" name="date" />
                            </td>
                            <td data-order="{{ e($row['planHours']) }}">
                                <input type="text" value="{{ e($row['planHours']) }}" name="planHours" class="small-input secretInput" onchange="leantime.ticketsController.updatePlannedHours(this, '{{ $row['id'] }}'); jQuery(this).parent().attr('data-order',jQuery(this).val());" />
                            </td>
                            <td data-order="{{ e($row['hourRemaining']) }}">
                                <input type="text" value="{{ e($row['hourRemaining']) }}" name="remainingHours" class="small-input secretInput" onchange="leantime.ticketsController.updateRemainingHours(this, '{{ $row['id'] }}');" />
                            </td>

                            <td data-order="{{ ($row['bookedHours'] === null || $row['bookedHours'] == '') ? '0' : $row['bookedHours'] }}">
                                {{ ($row['bookedHours'] === null || $row['bookedHours'] == '') ? '0' : $row['bookedHours'] }}
                            </td>
                            <td>
                                <x-globals::tickets.ticket-submenu :ticket="$row" :on-the-clock="$tpl->get('onTheClock')" />
                            </td>
                            @dispatchEvent('allTicketsTable.beforeRowEnd', ['tickets' => $allTickets, 'rowNum' => $rowNum])
                        </tr>
                    @endforeach
                    @dispatchEvent('allTicketsTable.afterLastRow', ['tickets' => $allTickets])
                </tbody>
                @dispatchEvent('allTicketsTable.afterBody', ['tickets' => $allTickets])
                <tfoot align="right">
                    <tr><td colspan="9"></td><td></td><td></td><td></td><td></td><td></td></tr>
                </tfoot>
            </table>
            @dispatchEvent('allTicketsTable.afterClose', ['tickets' => $allTickets])

            @if($group['label'] != 'all')
                </div>
            @endif
        @endforeach

    </div>
</div>

<script type="text/javascript">

    jQuery(document).ready(function() {
        @dispatchEvent('scripts.afterOpen')

        @if($login::userIsAtLeast($roles::$editor))
            leantime.ticketsController.initDueDateTimePickers();
            leantime.ticketsController.initUserDropdown();
            leantime.ticketsController.initMilestoneDropdown();
            leantime.ticketsController.initEffortDropdown();
            leantime.ticketsController.initPriorityDropdown();
            leantime.ticketsController.initSprintDropdown();
            leantime.ticketsController.initStatusDropdown();
        @else
        leantime.authController.makeInputReadonly(".maincontentinner");
        @endif

        leantime.ticketsController.initTicketsTable("{{ $searchCriteria['groupBy'] }}");

        @dispatchEvent('scripts.beforeClose')

    });

</script>
