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

        <div class="ticket-toolbar tw:flex tw:items-center tw:justify-between tw:flex-wrap tw:gap-2 tw:mb-10">
            <div>
                @dispatchEvent('filters.afterLefthandSectionOpen')
                @php $tpl->displaySubmodule('tickets-ticketNewBtn'); @endphp
            </div>
            <div class="tw:flex tw:items-center tw:gap-2">
                @php $tpl->displaySubmodule('tickets-ticketFilter'); @endphp
                @dispatchEvent('filters.beforeLefthandSectionClose')
            </div>
        </div>

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
            padding-top: 16px;
            ">
        @foreach($tpl->get('allKanbanColumns') as $key => $statusRow)
            <div class="column">
                <x-globals::elements.section-title variant="primary" :borderColor="$statusRow['class']">
                    @if($login::userIsAtLeast($roles::$manager))
                        <x-globals::actions.dropdown-menu container-class="pull-right">
                            <li><a href="#/setting/editBoxLabel?module=ticketlabels&label={{ $key }}" class="editLabelModal"><x-globals::elements.icon name="edit" /> {{ __('label.edit_label') }}</a></li>
                            <li><a href="{{ BASE_URL }}/projects/showProject/{{ session('currentProject') }}#todosettings"><x-globals::elements.icon name="add" /> {{ __('label.add_remove_columns') }}</a></li>
                        </x-globals::actions.dropdown-menu>
                    @endif

                    <strong class="count">0</strong>
                    {{ e($statusRow['name']) }}
                </x-globals::elements.section-title>
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

                    <x-globals::kanban.swimlane-row-header
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

            <div class="sortableTicketList kanbanBoard tw:-mt-1" id="kanboard-{{ $group['id'] }}">
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

                                <x-globals::tickets.quickadd-form :status-id="$key" :swimlane-key="$group['id'] ?? null" :is-empty="isset($emptyColumns[$key])" :current-group-by="$searchCriteria['groupBy'] ?? null" :reopen-state="$reopenState ?? null" :search-criteria="$searchCriteria ?? []" />

                                @foreach($allTickets as $row)
                                    @if($row['status'] == $key)
                                        <div class="ticketBox moveable container priority-border-{{ $row['priority'] }}" id="ticket_{{ $row['id'] }}">

                                                    <x-globals::tickets.ticket-submenu :ticket="$row" :on-the-clock="$tpl->get('onTheClock')" />

                                                    @if($row['dependingTicketId'] > 0)
                                                        <small><a href="#/tickets/showTicket/{{ $row['dependingTicketId'] }}" class="form-modal">{{ e($row['parentHeadline']) }}</a></small> //
                                                    @endif
                                                    <small><x-globals::elements.icon :name="$todoTypeIcons[strtolower($row['type'])] ?? 'task_alt'" size="sm" /> {{ __('label.' . strtolower($row['type'])) }}</small>
                                                    <small>#{{ $row['id'] }}</small>
                                                    <div class="kanbanCardContent">
                                                        <h4><a href="#/tickets/showTicket/{{ $row['id'] }}">{{ e($row['headline']) }}</a></h4>

                                                        <div class="kanbanContent tw:mb-5">
                                                            {!! $tpl->escapeMinimal($row['description']) !!}
                                                        </div>
                                                    </div>
                                                    <div class="tw:flex tw:flex-wrap tw:items-center tw:gap-1">
                                                        @if($row['dateToFinish'] != '0000-00-00 00:00:00' && $row['dateToFinish'] != '1969-12-31 00:00:00')
                                                            <div>
                                                                <x-globals::elements.icon name="calendar_month" />
                                                                <input type="text" title="{{ __('label.due') }}" value="{{ format($row['dateToFinish'])->date() }}" class="duedates secretInput tw:ml-0" data-id="{{ $row['id'] }}" name="date" />
                                                            </div>
                                                            <div>
                                                                @dispatchEvent('afterDates', ['ticket' => $row])
                                                            </div>
                                                        @endif
                                                    </div>

                                            <div class="clearfix tw:pb-2"></div>

                                            <div class="timerContainer" id="timerContainer-{{ $row['id'] }}">
                                                <x-tickets::chips.milestone-select
                                                    :ticket="(object)$row"
                                                    :milestones="$tpl->get('milestones')"
                                                    class="firstDropdown"
                                                />

                                                @if($row['storypoints'] != '' && $row['storypoints'] > 0)
                                                    <x-tickets::chips.effort-select
                                                        :ticket="(object)$row"
                                                        :efforts="$efforts"
                                                    />
                                                @endif

                                                <x-tickets::chips.priority-select
                                                    :ticket="(object)$row"
                                                    :priorities="$priorities"
                                                />

                                                <x-globals::actions.user-select
                                                    :entityId="$row['id']"
                                                    :assignedUserId="$row['editorId']"
                                                    :assignedName="$row['editorFirstname']"
                                                    :users="is_array($tpl->get('users')) ? $tpl->get('users') : []"
                                                    :showNameLabel="false"
                                                    :showArrowIcon="false"
                                                    :showUnassign="false"
                                                    dropdownClasses="lastDropdown dropRight"
                                                />
                                            </div>
                                            <div class="clearfix"></div>

                                            @if($row['commentCount'] > 0 || $row['subtaskCount'] > 0 || $row['tags'] != '')
                                                <div class="border-top tw:whitespace-nowrap">
                                                    @if($row['commentCount'] > 0)
                                                        <a href="#/tickets/showTicket/{{ $row['id'] }}"><x-globals::elements.icon name="forum" /> {{ $row['commentCount'] }}</a>&nbsp;
                                                    @endif

                                                    @if($row['subtaskCount'] > 0)
                                                        <a id="subtaskLink_{{ $row['id'] }}" href="#/tickets/showTicket/{{ $row['id'] }}" class="subtaskLineLink"> <x-globals::elements.icon name="arrow_forward" /> {{ $row['subtaskCount'] }}</a>&nbsp;
                                                    @endif

                                                    @if($row['tags'] != '')
                                                        @php $tagsArray = explode(',', $row['tags']); @endphp
                                                        <div class="dropdown">
                                                        <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown">
                                                            <x-globals::elements.icon name="sell" /> {{ count($tagsArray) }}
                                                        </a>
                                                        <ul class="dropdown-menu">
                                                            <li class="tw:p-2"><div class="tagsinput readonly">
                                                                @foreach($tagsArray as $tag)
                                                                    <span class="tag"><span>{{ e($tag) }}</span></span>
                                                                @endforeach
                                                            </div></li>
                                                        </ul>
                                                        </div>
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
