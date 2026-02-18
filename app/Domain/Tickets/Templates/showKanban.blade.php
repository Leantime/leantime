@php
    $tickets = $tpl->get('tickets');
    $sprints = $tpl->get('sprints');
    $searchCriteria = $tpl->get('searchCriteria');
    $currentSprint = $tpl->get('currentSprint');
    $todoTypeIcons = $tpl->get('ticketTypeIcons');
    $efforts = $tpl->get('efforts');
    $priorities = $tpl->get('priorities');
    $allTicketGroups = $tpl->get('allTickets');

    // Get quick-add reopen state from session
    $reopenState = session()->get('quickadd_reopen', null);

    // Get current groupBy for JavaScript access
    $currentGroupBy = $searchCriteria['groupBy'] ?? 'all';
@endphp

{!! $tpl->displayNotification() !!}

<script>
jQuery(document).ready(function(){
    // Expose current groupBy setting to JavaScript
    leantime.kanbanGroupBy = '{{ e($currentGroupBy) }}';
});
</script>

@php $tpl->displaySubmodule('tickets-ticketHeader') @endphp

<div class="maincontent">

    @php $tpl->displaySubmodule('tickets-ticketBoardTabs') @endphp

    <div class="maincontentinner kanban-board-wrapper">

        <div class="tw:grid tw:grid-cols-3">
            <div>
                @dispatchEvent('filters.afterLefthandSectionOpen')
                @php
                    $tpl->displaySubmodule('tickets-ticketNewBtn');
                    $tpl->displaySubmodule('tickets-ticketFilter');
                @endphp
                @dispatchEvent('filters.beforeLefthandSectionClose')
            </div>

            <div class="tw:text-center">
            </div>
            <div>
            </div>
        </div>

        <div class="clearfix"></div>

        @php
            if (isset($allTicketGroups['all'])) {
                $allTickets = $allTicketGroups['all']['items'];
            }

            $isGroupByActive = !empty($searchCriteria['groupBy']) && $searchCriteria['groupBy'] !== 'all';
            $columnHeaderClass = $isGroupByActive ? 'groupby-active' : '';
        @endphp

        <div class="kanban-column-headers {{ $columnHeaderClass }}" style="
            display: flex;
            position: sticky;
            top: 110px;
            justify-content: flex-start;
            z-index: 9;
            ">
        @foreach($tpl->get('allKanbanColumns') as $key => $statusRow)
            <div class="column">
                <h4 class="widgettitle title-primary title-border-{{ $statusRow['class'] }}">
                    @if($login::userIsAtLeast($roles::$manager))
                        <x-global::elements.dropdown containerClass="tw:float-right">
                            <li><a href="#/setting/editBoxLabel?module=ticketlabels&label={{ $key }}" class="editLabelModal">{{ __('headlines.edit_label') }}</a></li>
                            <li><a href="{{ BASE_URL }}/projects/showProject/{{ session('currentProject') }}#todosettings">{{ __('links.add_remove_col') }}</a></li>
                        </x-global::elements.dropdown>
                    @endif

                    <strong class="count">0</strong>
                    {{ e($statusRow['name']) }}
                </h4>
            </div>
        @endforeach
        </div>

        @foreach($allTicketGroups as $group)
            @php $allTickets = $group['items']; @endphp

            @if($group['label'] != 'all')
                {{-- Swimlane row wrapper --}}
                @php
                    $swimlaneExpanded = !in_array($group['id'], session('collapsedSwimlanes', []));
                    $groupBy = $searchCriteria['groupBy'] ?? 'status';
                    $statusBreakdown = $tpl->get('statusBreakdown');
                    $groupIdKey = (string)$group['id'];
                    $swimlaneBreakdown = $statusBreakdown[$groupIdKey] ?? $statusBreakdown[$group['id']] ?? [];
                    $statusCounts = $swimlaneBreakdown['statusCounts'] ?? [];
                    $timeAlert = $swimlaneBreakdown['timeAlert'] ?? null;
                @endphp
                <div class="kanban-swimlane-row" data-expanded="{{ $swimlaneExpanded ? 'true' : 'false' }}" id="swimlane-row-{{ $group['id'] }}">
                    <div class="kanban-swimlane-sentinel" data-swimlane-id="{{ $group['id'] }}" aria-hidden="true"></div>

                    <x-global::kanban.swimlane-row-header
                        :groupBy="$groupBy"
                        :groupId="$group['id']"
                        :label="$group['label']"
                        :totalCount="$swimlaneBreakdown['totalCount'] ?? count($group['items'])"
                        :statusCounts="$statusCounts"
                        :statusColumns="$tpl->get('allKanbanColumns')"
                        :expanded="$swimlaneExpanded"
                        :moreInfo="$group['more-info'] ?? null"
                        :timeAlert="$group['timeAlert'] ?? null"
                    />

                    <div class="kanban-swimlane-content{{ !$swimlaneExpanded ? ' collapsed' : '' }}" id="swimlane-content-{{ $group['id'] }}">
            @endif

            <div class="sortableTicketList kanbanBoard" id="kanboard-{{ $group['id'] }}" style="margin-top:-5px;">
                <div class="row-fluid">
                    @php
                        $emptyColumns = [];
                        foreach ($tpl->get('allKanbanColumns') as $key => $statusRow) {
                            $hasTickets = false;
                            if (isset($allTickets)) {
                                foreach ($allTickets as $ticket) {
                                    if (isset($ticket['status']) && $ticket['status'] == $key) {
                                        $hasTickets = true;
                                        break;
                                    }
                                }
                            }
                            if (!$hasTickets) {
                                $emptyColumns[$key] = true;
                            }
                        }
                    @endphp

                    @foreach($tpl->get('allKanbanColumns') as $key => $statusRow)
                        <div class="column">
                            <div class="contentInner status_{{ $key }} {{ isset($emptyColumns[$key]) ? 'empty-column' : '' }}"
                                 data-empty-text="{{ isset($emptyColumns[$key]) ? 'Empty' : '' }}"
                                 aria-label="{{ isset($emptyColumns[$key]) ? 'Empty column' : htmlspecialchars($statusRow['name']) . ' column items' }}"
                                 role="list">

                                @include('tickets::partials.quickadd-form', [
                                    'statusId' => $key,
                                    'swimlaneKey' => $group['id'] ?? null,
                                    'isEmpty' => isset($emptyColumns[$key]),
                                    'currentGroupBy' => $searchCriteria['groupBy'] ?? null,
                                ])

                                @foreach($allTickets as $row)
                                    @if($row['status'] == $key)
                                        <div class="ticketBox moveable container priority-border-{{ $row['priority'] }}" id="ticket_{{ $row['id'] }}">

                                                    @include('tickets::partials.ticketsubmenu', ['ticket' => $row, 'onTheClock' => $tpl->get('onTheClock')])

                                                    @if($row['dependingTicketId'] > 0)
                                                        <small><a href="#/tickets/showTicket/{{ $row['dependingTicketId'] }}" class="form-modal">{{ e($row['parentHeadline']) }}</a></small> //
                                                    @endif
                                                    <small><i class="fa {{ $todoTypeIcons[strtolower($row['type'])] }}"></i> {{ __('label.' . strtolower($row['type'])) }}</small>
                                                    <small>#{{ $row['id'] }}</small>
                                                    <div class="kanbanCardContent">
                                                        <h4><a href="#/tickets/showTicket/{{ $row['id'] }}">{{ e($row['headline']) }}</a></h4>

                                                        <div class="kanbanContent" style="margin-bottom: 20px">
                                                            {!! $tpl->escapeMinimal($row['description']) !!}
                                                        </div>
                                                    </div>
                                                    <div class="tw:flex tw:flex-wrap tw:items-center tw:gap-1">
                                                        @if($row['dateToFinish'] != '0000-00-00 00:00:00' && $row['dateToFinish'] != '1969-12-31 00:00:00')
                                                            <div>
                                                                {!! __('label.due_icon') !!}
                                                                <input type="text" title="{{ __('label.due') }}" value="{{ format($row['dateToFinish'])->date() }}" class="duedates secretInput" style="margin-left:0px;" data-id="{{ $row['id'] }}" name="date" />
                                                            </div>
                                                            <div>
                                                                @dispatchEvent('afterDates', ['ticket' => $row])
                                                            </div>
                                                        @endif
                                                    </div>

                                            <div class="clearfix" style="padding-bottom: 8px;"></div>

                                            <div class="timerContainer" id="timerContainer-{{ $row['id'] }}">
                                                <div class="dropdown ticketDropdown milestoneDropdown colorized show firstDropdown">
                                                    <a style="background-color:{{ e($row['milestoneColor']) }}" class="dropdown-toggle f-left label-default milestone" href="javascript:void(0);" role="button" id="milestoneDropdownMenuLink{{ $row['id'] }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <span class="text">@if($row['milestoneid'] != '' && $row['milestoneid'] != 0){{ e($row['milestoneHeadline']) }}@else{{ __('label.no_milestone') }}@endif</span>
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

                                                @if($row['storypoints'] != '' && $row['storypoints'] > 0)
                                                    <div class="dropdown ticketDropdown effortDropdown show">
                                                        <a class="dropdown-toggle f-left label-default effort" href="javascript:void(0);" role="button" id="effortDropdownMenuLink{{ $row['id'] }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <span class="text">{{ $efforts['' . $row['storypoints']] ?? $row['storypoints'] }}</span>
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
                                                @endif

                                                <div class="dropdown ticketDropdown priorityDropdown show">
                                                    <a class="dropdown-toggle f-left label-default priority priority-bg-{{ $row['priority'] }}" href="javascript:void(0);" role="button" id="priorityDropdownMenuLink{{ $row['id'] }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <span class="text">@if($row['priority'] != '' && $row['priority'] > 0){{ $priorities[$row['priority']] ?? __('label.priority_unkown') }}@else{{ __('label.priority_unkown') }}@endif</span>
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

                                                <div class="dropdown ticketDropdown userDropdown noBg show right lastDropdown dropRight">
                                                    <a class="dropdown-toggle f-left" href="javascript:void(0);" role="button" id="userDropdownMenuLink{{ $row['id'] }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <span class="text">
                                                            @if($row['editorFirstname'] != '')
                                                                <span id="userImage{{ $row['id'] }}"><img src="{{ BASE_URL }}/api/users?profileImage={{ $row['editorId'] }}" width="25" style="vertical-align: middle;"/></span>
                                                            @else
                                                                <span id="userImage{{ $row['id'] }}"><img src="{{ BASE_URL }}/api/users?profileImage=false" width="25" style="vertical-align: middle;"/></span>
                                                            @endif
                                                        </span>
                                                    </a>
                                                    <ul class="dropdown-menu" aria-labelledby="userDropdownMenuLink{{ $row['id'] }}">
                                                        <li class="nav-header border">{{ __('dropdown.choose_user') }}</li>
                                                        @if(is_array($tpl->get('users')))
                                                            @foreach($tpl->get('users') as $user)
                                                                <li class="dropdown-item">
                                                                    <a href="javascript:void(0);" data-label="{{ sprintf(__('text.full_name'), e($user['firstname']), e($user['lastname'])) }}" data-value="{{ $row['id'] }}_{{ $user['id'] }}_{{ $user['profileId'] }}" id="userStatusChange{{ $row['id'] }}{{ $user['id'] }}"><img src="{{ BASE_URL }}/api/users?profileImage={{ $user['id'] }}" width="25" style="vertical-align: middle; margin-right:5px;"/>{{ sprintf(__('text.full_name'), e($user['firstname']), e($user['lastname'])) }}</a>
                                                                </li>
                                                            @endforeach
                                                        @endif
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="clearfix"></div>

                                            @if($row['commentCount'] > 0 || $row['subtaskCount'] > 0 || $row['tags'] != '')
                                                <div class="border-top" style="white-space: nowrap;">
                                                    @if($row['commentCount'] > 0)
                                                        <a href="#/tickets/showTicket/{{ $row['id'] }}"><span class="fa-regular fa-comments"></span> {{ $row['commentCount'] }}</a>&nbsp;
                                                    @endif

                                                    @if($row['subtaskCount'] > 0)
                                                        <a id="subtaskLink_{{ $row['id'] }}" href="#/tickets/showTicket/{{ $row['id'] }}" class="subtaskLineLink"> <span class="fa fa-diagram-successor"></span> {{ $row['subtaskCount'] }}</a>&nbsp;
                                                    @endif

                                                    @if($row['tags'] != '')
                                                        @php $tagsArray = explode(',', $row['tags']); @endphp
                                                        <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">
                                                            <i class="fa fa-tags" aria-hidden="true"></i> {{ count($tagsArray) }}
                                                        </a>
                                                        <ul class="dropdown-menu">
                                                            <li style="padding:10px"><div class="tagsinput readonly">
                                                                @foreach($tagsArray as $tag)
                                                                    <span class="tag"><span>{{ e($tag) }}</span></span>
                                                                @endforeach
                                                            </div></li>
                                                        </ul>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                    <div class="clearfix"></div>
                </div>
            </div>

            @if($group['label'] != 'all')
                </div> {{-- .kanban-swimlane-content --}}
                </div> {{-- .kanban-swimlane-row --}}
            @endif
        @endforeach

    </div>
</div>

<script type="text/javascript">

    jQuery(document).ready(function(){

    @if($login::userIsAtLeast($roles::$editor))
        leantime.ticketsController.initUserDropdown();
        leantime.ticketsController.initMilestoneDropdown();
        leantime.ticketsController.initDueDateTimePickers();
        leantime.ticketsController.initEffortDropdown();
        leantime.ticketsController.initPriorityDropdown();

        var ticketStatusList = [@foreach($tpl->get('allTicketStates') as $key => $statusRow)'{{ $key }}',@endforeach];
        leantime.ticketsController.initTicketKanban(ticketStatusList);

    @else
        leantime.authController.makeInputReadonly(".maincontentinner");
    @endif

    leantime.ticketsController.setUpKanbanColumns();

        @if(isset($_GET['showTicketModal']))
            @php
                $modalUrl = $_GET['showTicketModal'] == '' ? '' : '/' . (int)$_GET['showTicketModal'];
            @endphp
        leantime.ticketsController.openTicketModalManually("{{ BASE_URL }}/tickets/showTicket{{ $modalUrl }}");
        window.history.pushState({},document.title, '{{ BASE_URL }}/tickets/showKanban');
        @endif

        @foreach($allTicketGroups as $group)
            @foreach($group['items'] as $ticket)
                @if($ticket['dependingTicketId'] > 0)
        var startElement = document.getElementById('subtaskLink_{{ $ticket['dependingTicketId'] }}');
        var endElement = document.getElementById('ticket_{{ $ticket['id'] }}');

        if (startElement != undefined && endElement != undefined) {
            var startAnchor = LeaderLine.mouseHoverAnchor({
                element: startElement,
                showEffectName: 'draw',
                style: {background: 'none', backgroundColor: 'none'},
                hoverStyle: {background: 'none', backgroundColor: 'none', cursor: 'pointer'}
            });

            var line{{ $ticket['id'] }} = new LeaderLine(startAnchor, endElement, {
                startPlugColor: 'var(--accent1)',
                endPlugColor: 'var(--accent2)',
                gradient: true,
                size: 2,
                path: "grid",
                startSocket: 'bottom',
                endSocket: 'auto'
            });

            jQuery("#ticket_{{ $ticket['id'] }}").mousedown(function () {})
                .mousemove(function () {})
                .mouseup(function () {
                    line{{ $ticket['id'] }}.position();
                });

            jQuery("#ticket_{{ $ticket['dependingTicketId'] }}").mousedown(function () {})
                .mousemove(function () {})
                .mouseup(function () {
                    line{{ $ticket['id'] }}.position();
                });
        }
                @endif
            @endforeach
        @endforeach

    });
</script>
