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

        <div class="row">
            <div class="col-md-4">
                @dispatchEvent('filters.afterLefthandSectionOpen')
                @php
                    $tpl->displaySubmodule('tickets-ticketNewBtn');
                    $tpl->displaySubmodule('tickets-ticketFilter');
                @endphp
                @dispatchEvent('filters.beforeLefthandSectionClose')
            </div>

            <div class="col-md-4 center">
            </div>
            <div class="col-md-4">
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
                        <i class="fa fa-angle-down"></i>{{ $group['label'] }}({{ count($group['items']) }})
                    </a><br />
                    <small style="padding-left:20px; color:var(--primary-font-color); font-size:var(--font-size-s);">{{ $group['more-info'] }}</small>
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

                            <td data-order="{{ e($row['headline']) }}">
                                @if($row['dependingTicketId'] > 0)
                                    <small><a href="#/tickets/showTicket/{{ $row['dependingTicketId'] }}">{{ e($row['parentHeadline']) }}</a></small> //<br />
                                @endif
                                <a class="ticketModal" href="#/tickets/showTicket/{{ e($row['id']) }}">{{ e($row['headline']) }}</a>
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
                                <div class="dropdown ticketDropdown statusDropdown colorized show">
                                    <a class="dropdown-toggle status {{ $class }} f-left" href="javascript:void(0);" role="button" id="statusDropdownMenuLink{{ $row['id'] }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <span class="text">{{ $name }}</span>
                                        &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="statusDropdownMenuLink{{ $row['id'] }}">
                                        <li class="nav-header border">{{ __('dropdown.choose_status') }}</li>
                                        @foreach($statusLabels as $key => $label)
                                            <li class="dropdown-item">
                                                <a href="javascript:void(0);" class="{{ $label['class'] }}" data-label="{{ e($label['name']) }}" data-value="{{ $row['id'] }}_{{ $key }}_{{ $label['class'] }}" id="ticketStatusChange{{ $row['id'] }}{{ $key }}">{{ e($label['name']) }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </td>

                            @php
                                if ($row['milestoneid'] != '' && $row['milestoneid'] != 0) {
                                    $milestoneHeadline = $tpl->escape($row['milestoneHeadline']);
                                } else {
                                    $milestoneHeadline = __('label.no_milestone');
                                }
                            @endphp

                            <td data-order="{{ $milestoneHeadline }}">
                                <div class="dropdown ticketDropdown milestoneDropdown colorized show">
                                    <a style="background-color:{{ e($row['milestoneColor']) }}" class="dropdown-toggle label-default milestone f-left" href="javascript:void(0);" role="button" id="milestoneDropdownMenuLink{{ $row['id'] }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <span class="text">{{ $milestoneHeadline }}</span>
                                        &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="milestoneDropdownMenuLink{{ $row['id'] }}">
                                        <li class="nav-header border">{{ __('dropdown.choose_milestone') }}</li>
                                        <li class="dropdown-item"><a style="background-color:#b0b0b0" href="javascript:void(0);" data-label="{{ __('label.no_milestone') }}" data-value="{{ $row['id'] }}_0_#b0b0b0"> {{ __('label.no_milestone') }} </a></li>
                                        @foreach($tpl->get('milestones') as $milestone)
                                            <li class="dropdown-item">
                                                <a href="javascript:void(0);" data-label="{{ e($milestone->headline) }}" data-value="{{ $row['id'] }}_{{ $milestone->id }}_{{ e($milestone->tags) }}" id="ticketMilestoneChange{{ $row['id'] }}{{ $milestone->id }}" style="background-color:{{ e($milestone->tags) }}">{{ e($milestone->headline) }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </td>

                            <td data-order="{{ $row['storypoints'] ? ($efforts['' . $row['storypoints'] . ''] ?? '?') : __('label.story_points_unkown') }}">
                                <div class="dropdown ticketDropdown effortDropdown show">
                                    <a class="dropdown-toggle label-default effort f-left" href="javascript:void(0);" role="button" id="effortDropdownMenuLink{{ $row['id'] }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <span class="text">@if($row['storypoints'] != '' && $row['storypoints'] > 0){{ $efforts['' . $row['storypoints']] ?? $row['storypoints'] }}@else{{ __('label.story_points_unkown') }}@endif</span>
                                        &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="effortDropdownMenuLink{{ $row['id'] }}">
                                        <li class="nav-header border">{{ __('dropdown.how_big_todo') }}</li>
                                        @foreach($efforts as $effortKey => $effortValue)
                                            <li class="dropdown-item">
                                                <a href="javascript:void(0);" data-value="{{ $row['id'] }}_{{ $effortKey }}" id="ticketEffortChange{{ $row['id'] }}{{ $effortKey }}">{{ $effortValue }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </td>

                            @php
                                $priorityLabel = ($row['priority'] != '' && $row['priority'] > 0) ? ($priorities[$row['priority']] ?? __('label.priority_unkown')) : __('label.priority_unkown');
                            @endphp
                            <td data-order="{{ $priorityLabel }}">
                                <div class="dropdown ticketDropdown priorityDropdown show">
                                    <a class="dropdown-toggle label-default priority priority-bg-{{ $row['priority'] }} f-left" href="javascript:void(0);" role="button" id="priorityDropdownMenuLink{{ $row['id'] }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <span class="text">{{ $priorityLabel }}</span>
                                        &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="priorityDropdownMenuLink{{ $row['id'] }}">
                                        <li class="nav-header border">{{ __('dropdown.select_priority') }}</li>
                                        @foreach($priorities as $priorityKey => $priorityValue)
                                            <li class="dropdown-item">
                                                <a href="javascript:void(0);" class="priority-bg-{{ $priorityKey }}" data-value="{{ $row['id'] }}_{{ $priorityKey }}" id="ticketPriorityChange{{ $row['id'] }}{{ $priorityKey }}">{{ $priorityValue }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </td>

                            <td data-order="{{ $row['editorFirstname'] != '' ? e($row['editorFirstname']) : __('dropdown.not_assigned') }}">
                                <div class="dropdown ticketDropdown userDropdown noBg show f-left">
                                    <a class="dropdown-toggle" href="javascript:void(0);" role="button" id="userDropdownMenuLink{{ $row['id'] }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <span class="text">
                                            @if($row['editorFirstname'] != '')
                                                <span id="userImage{{ $row['id'] }}"><img src="{{ BASE_URL }}/api/users?profileImage={{ $row['editorId'] }}" width="25" style="vertical-align: middle; margin-right:5px;"/></span><span id="user{{ $row['id'] }}">{{ e($row['editorFirstname']) }}</span>
                                            @else
                                                <span id="userImage{{ $row['id'] }}"><img src="{{ BASE_URL }}/api/users?profileImage=false" width="25" style="vertical-align: middle; margin-right:5px;"/></span><span id="user{{ $row['id'] }}">{{ __('dropdown.not_assigned') }}</span>
                                            @endif
                                        </span>
                                        &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="userDropdownMenuLink{{ $row['id'] }}">
                                        <li class="nav-header border">{{ __('dropdown.choose_user') }}</li>
                                        <li class="dropdown-item">
                                            <a href="javascript:void(0);" data-label="{{ __('label.not_assigned_to_user') }}" data-value="{{ $row['id'] }}_0_0" id="userStatusChange{{ $row['id'] }}0">{{ __('label.not_assigned_to_user') }}</a>
                                        </li>
                                        @foreach($tpl->get('users') as $user)
                                            <li class="dropdown-item">
                                                <a href="javascript:void(0);" data-label="{{ sprintf(__('text.full_name'), e($user['firstname']), e($user['lastname'])) }}" data-value="{{ $row['id'] }}_{{ $user['id'] }}_{{ $user['profileId'] }}" id="userStatusChange{{ $row['id'] }}{{ $user['id'] }}"><img src="{{ BASE_URL }}/api/users?profileImage={{ $user['id'] }}" width="25" style="vertical-align: middle; margin-right:5px;"/>{{ sprintf(__('text.full_name'), e($user['firstname']), e($user['lastname'])) }}</a>
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

                            <td data-order="{{ $sprintHeadline }}">
                                <div class="dropdown ticketDropdown sprintDropdown show">
                                    <a class="dropdown-toggle label-default sprint f-left" href="javascript:void(0);" role="button" id="sprintDropdownMenuLink{{ $row['id'] }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <span class="text">{{ $sprintHeadline }}</span>
                                        <i class="fa fa-caret-down" aria-hidden="true"></i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="sprintDropdownMenuLink{{ $row['id'] }}">
                                        <li class="nav-header border">{{ __('dropdown.choose_sprint') }}</li>
                                        <li class="dropdown-item"><a href="javascript:void(0);" data-label="{{ __('label.not_assigned_to_sprint') }}" data-value="{{ $row['id'] }}_0"> {{ __('label.not_assigned_to_sprint') }} </a></li>
                                        @if($tpl->get('sprints'))
                                            @foreach($tpl->get('sprints') as $sprint)
                                                <li class="dropdown-item">
                                                    <a href="javascript:void(0);" data-label="{{ e($sprint->name) }}" data-value="{{ $row['id'] }}_{{ $sprint->id }}" id="ticketSprintChange{{ $row['id'] }}{{ $sprint->id }}">{{ e($sprint->name) }}</a>
                                                </li>
                                            @endforeach
                                        @endif
                                    </ul>
                                </div>
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
                                @include('tickets::partials.ticketsubmenu', ['ticket' => $row, 'onTheClock' => $tpl->get('onTheClock')])
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
