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
                <div id="tableButtons" class="tw:inline-block"></div>
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
                        <x-globals::elements.icon name="expand_more" />{{ $group['label'] }}
                    </a>
                    <x-globals::elements.badge color="primary">{{ count($group['items']) }}</x-globals::elements.badge>
                </h5>

                <div class="simpleAccordionContainer" id="accordion_content-{{ $group['id'] }}">
            @endif

            @php $allTickets = $group['items']; @endphp

            @dispatchEvent('allTicketsTable.before', ['tickets' => $allTicketGroups])
            <x-globals::elements.table :datatable="true" class="tw:w-full">
                <x-slot:colgroup>
                    <colgroup>
                        <col class="con1"><col class="con0" class="tw:max-w-[200px]"><col class="con1"><col class="con0">
                        <col class="con1"><col class="con0"><col class="con1"><col class="con0">
                        <col class="con1"><col class="con0"><col class="con1"><col class="con0">
                        <col class="con1"><col class="con0">
                    </colgroup>
                </x-slot:colgroup>
                @dispatchEvent('allTicketsTable.beforeHead', ['tickets' => $allTickets])
                <x-slot:head>
                    @dispatchEvent('allTicketsTable.beforeHeadRow', ['tickets' => $allTickets])
                    <tr>
                        <th class="id-col">{{ __('label.id') }}</th>
                        <th class="tw:max-w-[350px]">{{ __('label.title') }}</th>
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
                        <th class="no-sort" scope="col"><span class="sr-only">{{ __('label.actions') }}</span></th>
                    </tr>
                    @dispatchEvent('allTicketsTable.afterHeadRow', ['tickets' => $allTickets])
                </x-slot:head>
                @dispatchEvent('allTicketsTable.afterHead', ['tickets' => $allTickets])
                <tbody>
                    @dispatchEvent('allTicketsTable.beforeFirstRow', ['tickets' => $allTickets])
                    @foreach($allTickets as $rowNum => $row)
                        <tr class="tw:h-px">
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
                                $sortKey = $statusLabels[$row['status']]['sortKey'] ?? 0;
                                $name    = $statusLabels[$row['status']]['name'] ?? 'new';
                            @endphp
                            <td data-order="{{ $name }}">
                                <x-tickets::chips.status-select
                                    :ticket="(object)$row"
                                    :statuses="$statusLabels"
                                />
                            </td>

                            @php
                                $milestoneHeadline = ($row['milestoneid'] != '' && $row['milestoneid'] != 0)
                                    ? $tpl->escape($row['milestoneHeadline'])
                                    : __('label.no_milestone');
                            @endphp
                            <td data-order="{{ $milestoneHeadline }}">
                                <x-tickets::chips.milestone-select
                                    :ticket="(object)$row"
                                    :milestones="$tpl->get('milestones')"
                                />
                            </td>

                            <td data-order="{{ $row['storypoints'] ? ($efforts['' . $row['storypoints'] . ''] ?? '?') : __('label.story_points_unkown') }}">
                                <x-tickets::chips.effort-select
                                    :ticket="(object)$row"
                                    :efforts="$efforts"
                                />
                            </td>

                            @php
                                $priorityLabel = ($row['priority'] != '' && $row['priority'] > 0)
                                    ? ($priorities[$row['priority']] ?? __('label.priority_unkown'))
                                    : __('label.priority_unkown');
                            @endphp
                            <td data-order="{{ $priorityLabel }}">
                                <x-tickets::chips.priority-select
                                    :ticket="(object)$row"
                                    :priorities="$priorities"
                                />
                            </td>

                            <td data-order="{{ $row['editorFirstname'] != '' ? e($row['editorFirstname']) : __('dropdown.not_assigned') }}" class="user-cell">
                                <x-globals::actions.user-select
                                    :entityId="$row['id']"
                                    :assignedUserId="$row['editorId']"
                                    :assignedName="$row['editorFirstname']"
                                    :users="$tpl->get('users')"
                                    :showNameLabel="true"
                                    :showArrowIcon="false"
                                    :showUnassign="true"
                                />
                            </td>

                            @php
                                $sprintHeadline = ($row['sprint'] != '' && $row['sprint'] != 0 && $row['sprint'] != -1)
                                    ? $tpl->escape($row['sprintName'])
                                    : __('label.not_assigned_to_sprint');
                            @endphp
                            <td data-order="{{ $sprintHeadline }}">
                                <x-tickets::chips.sprint-select
                                    :ticket="(object)$row"
                                    :sprints="$sprints ?? []"
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
                <x-slot:foot>
                    <tr><td colspan="9"></td><td></td><td></td><td></td><td></td><td></td></tr>
                </x-slot:foot>
            </x-globals::elements.table>
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
