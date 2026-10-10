@extends($layout)

@section('content')

@php
    $tickets = $tickets ?? [];
    $todoTypeIcons = $ticketTypeIcons;
    $allTicketGroups = $allTickets;
    $reopenState = session()->get('quickadd_reopen', null);
    $currentGroupBy = $searchCriteria['groupBy'] ?? 'all';
    // Program (cross-project) board: columns are semantic status types and tickets are placed
    // by their computed statusType (resolved from each project) instead of the raw status key.
    $programBoard = $programBoard ?? false;
    $placementField = $programBoard ? 'statusType' : 'status';
    // Which optional fields the cards show (#1859). Boards that don't pass a preference
    // (e.g. the program board) render the default card.
    $cardFields = ($kanbanView ?? \Leantime\Domain\Tickets\Services\KanbanViewSettings::defaults())['fields'];
    $showCardDropdowns = $cardFields['milestone'] || $cardFields['effort'] || $cardFields['priority'] || $cardFields['assignee'];
    // Card order inside a column (#1536). Anything but "manual" means dragging only changes status.
    $cardSort = \Leantime\Domain\Tickets\Services\KanbanViewSettings::normalizeSort($kanbanView['sort'] ?? null);
    $isManualSort = \Leantime\Domain\Tickets\Services\KanbanViewSettings::isManualSort($cardSort);
@endphp

{!! $tpl->displayNotification() !!}

<script>
    leantime.kanbanGroupBy = '{{ $tpl->escape($currentGroupBy) }}';
</script>

@include('tickets::submodules.ticketHeader')

<div class="maincontent">

    @include('tickets::submodules.ticketBoardTabs')

    <div class="maincontentinner kanban-board-wrapper" >

        {{-- Board actions (New / Filter / Group By) moved into the nav bar
             (ticketBoardTabs) so there's no separate toolbar row here. --}}
        <div class="clearfix"></div>

        @if ($programBoard)
            <p class="tw-text-[var(--secondary-font-color)]" style="margin-bottom:15px;">
                <i class="fa fa-circle-info" aria-hidden="true"></i>
                {{ __('text.program_status_rollup') }}
            </p>
        @endif

        @if (! $isManualSort)
            <p class="tw-text-[var(--secondary-font-color)]" style="margin-bottom:15px;">
                <i class="fa fa-arrow-down-wide-short" aria-hidden="true"></i>
                {{ sprintf(__('text.kanban_sorted_by'), __('label.kanban_sort_'.$cardSort)) }}
            </p>
        @endif

        @if (isset($allTicketGroups['all']))
            @php $allTickets = $allTicketGroups['all']['items']; @endphp
        @endif

        @php
            $isGroupByActive = ! empty($searchCriteria['groupBy']) && $searchCriteria['groupBy'] !== 'all';
            $columnHeaderClass = $isGroupByActive ? 'groupby-active' : '';
        @endphp
        <div class="kanban-column-headers {{ $columnHeaderClass }}" style="
            display: flex;
            position: sticky;
            top: 110px;
            justify-content: flex-start;
            z-index: 9;
            ">
        @foreach ($allKanbanColumns as $key => $statusRow)
            <div class="column">
                <h4 class="widgettitle title-primary title-border-{{ $statusRow['class'] }}">
                    @if ($login::userIsAtLeast($roles::$manager) && ! $programBoard)
                        <x-global::actions.dropdown trigger-class="editHeadline" style="float:right;">
                            <li><a href="#/setting/editBoxLabel?module=ticketlabels&label={{ $key }}" class="editLabelModal">{!! __('headlines.edit_label') !!}</a>
                            </li>
                            <li><a href="{{ BASE_URL }}/projects/showProject/{{ session('currentProject') }}#todosettings">{!! __('links.add_remove_col') !!}</a></li>

                        </x-global::actions.dropdown>
                    @endif
                    <strong class="count">0</strong>
                    {{ $statusRow['name'] }}
                </h4>
            </div>
        @endforeach
        </div>

        @foreach ($allTicketGroups as $group)
             @php $allTickets = $group['items']; @endphp

            @if ($group['label'] != 'all')
                @php
                $swimlaneExpanded = ! in_array($group['id'], session('collapsedSwimlanes', []));
                $groupBy = $searchCriteria['groupBy'] ?? 'status';
                $groupId = $group['id'];
                $groupIdKey = (string) $groupId;
                $swimlaneBreakdown = $statusBreakdown[$groupIdKey] ?? $statusBreakdown[$groupId] ?? [];
                $statusCounts = $swimlaneBreakdown['statusCounts'] ?? [];
                $timeAlert = $swimlaneBreakdown['timeAlert'] ?? null;
                @endphp
                <div class="kanban-swimlane-row" data-expanded="{{ $swimlaneExpanded ? 'true' : 'false' }}" id="swimlane-row-{{ $group['id'] }}">
                    <div class="kanban-swimlane-sentinel" data-swimlane-id="{{ $group['id'] }}" aria-hidden="true"></div>

                    {{-- Render the component directly. It was previously invoked via
                         app('blade.compiler')::render('<x-...>', $data), but a <x-component> string
                         passed to Blade::render from inside an already-compiled view gets its bare
                         variables ($label, $groupId, …) pre-compiled by the outer pass and they are
                         not in the inner render scope — throwing "Undefined variable" for ANY
                         kanban group-by. A plain component tag with inline expressions is correct. --}}
                    <x-global::kanban.swimlane-row-header
                        :groupBy="$groupBy"
                        :groupId="$group['id']"
                        :label="$group['label']"
                        :totalCount="$swimlaneBreakdown['totalCount'] ?? count($group['items'])"
                        :statusCounts="$statusCounts"
                        :statusColumns="$allKanbanColumns"
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
                            foreach ($allKanbanColumns as $key => $statusRow) {
                                $hasTickets = false;
                                if (isset($allTickets)) {
                                    foreach ($allTickets as $ticket) {
                                        if (isset($ticket[$placementField]) && $ticket[$placementField] == $key) {
                                            $hasTickets = true;
                                            break;
                                        }
                                    }
                                }
                                if (! $hasTickets) {
                                    $emptyColumns[$key] = true;
                                }
                            }
                            @endphp

                            @foreach ($allKanbanColumns as $key => $statusRow)
                            <div class="column">
                                <div class="contentInner status_{{ $key }} {{ isset($emptyColumns[$key]) ? 'empty-column' : '' }}"
                                     data-empty-text="{{ isset($emptyColumns[$key]) ? 'Empty' : '' }}"
                                     aria-label="{{ isset($emptyColumns[$key]) ? 'Empty column' : htmlspecialchars($statusRow['name']).' column items' }}"
                                     role="list">

                                    @include('tickets::partials.quickadd-form', [
                                        'statusId' => $key,
                                        'swimlaneKey' => $group['value'] ?? $group['id'] ?? null,
                                        'isEmpty' => isset($emptyColumns[$key]),
                                        'currentGroupBy' => $searchCriteria['groupBy'] ?? null,
                                        'programBoard' => $programBoard,
                                        'availableProjects' => $availableProjects ?? null,
                                    ])

                                    @foreach ($allTickets as $row)
                                        @if (($row[$placementField] ?? null) == $key)
                                        <div class="ticketBox moveable container priority-border-{{ $row['priority'] }}" id="ticket_{{ $row['id'] }}">

                                            <div class="row" >
                                                <div class="col-md-12">

                                                    @include('tickets::partials.ticketsubmenu', ['ticket' => $row, 'onTheClock' => $onTheClock])

                                                    @if ($row['dependingTicketId'] > 0)
                                                        <small><a href="#/tickets/showTicket/{{ $row['dependingTicketId'] }}" class="form-modal">{{ $row['parentHeadline'] }}</a></small> //
                                                    @endif
                                                    <small><i class="fa {{ $todoTypeIcons[strtolower($row['type'])] }}"></i> {{ __('label.'.strtolower($row['type'])) }}</small>
                                                    <small>#{{ $row['id'] }}</small>
                                                    <div class="kanbanCardContent">
                                                        <h4><a href="#/tickets/showTicket/{{ $row['id'] }}" data-hx-get="{{ BASE_URL }}/tickets/showTicket/{{ $row['id'] }}" hx-swap="none" preload="mouseover">{{ $row['headline'] }}</a></h4>

                                                        {{-- Only render the description block when there IS one. The
                                                             20px bottom margin was inline and unconditional, so every card
                                                             without a description carried 20px of dead space above its
                                                             meta row — which is why cards in the same column sat at
                                                             visibly different densities. --}}
                                                        @if ($cardFields['description'] && trim(strip_tags((string) $row['description'])) !== '')
                                                        <div class="kanbanContent">
                                                            {!! $tpl->escapeMinimal($row['description']) !!}
                                                        </div>
                                                        @endif

                                                    </div>
                                                    <div class="tw-flex">
                                                    @if ($cardFields['dueDate'] && $row['dateToFinish'] != '0000-00-00 00:00:00' && $row['dateToFinish'] != '1969-12-31 00:00:00')
                                                        <div>
                                                            {!! __('label.due_icon') !!}
                                                            <input type="text" title="{{ __('label.due') }}" value="{{ format($row['dateToFinish'])->date() }}" class="duedates secretInput" style="margin-left:0px;" data-id="{{ $row['id'] }}" name="date" />
                                                        </div>
                                                        <div>
                                                            @dispatchEvent('afterDates', ['ticket' => $row])
                                                        </div>
                                                    @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="clearfix" style="padding-bottom: 8px;"></div>

                                            @if ($showCardDropdowns)
                                            <div class="timerContainer " id="timerContainer-{{ $row['id'] }}" >

                                                    @if ($cardFields['milestone'])
                                                    <x-tickets::chip-milestone class="firstDropdown" :ticket-id="$row['id']" :milestone-id="$row['milestoneid']" :headline="$row['milestoneHeadline'] ?? ''" :color="$row['milestoneColor'] ?? ''" :milestones="$milestones" />
                                                    @endif


                                                @if ($cardFields['effort'] && $row['storypoints'] != '' && $row['storypoints'] > 0)
                                                    <x-tickets::chip-effort :ticket-id="$row['id']" :storypoints="$row['storypoints']" :efforts="$efforts" />
                                                @endif


                                                @if ($cardFields['priority'])
                                                <x-tickets::chip-priority :ticket-id="$row['id']" :priority="$row['priority']" :priorities="$priorities" />
                                                @endif


                                                @if ($cardFields['assignee'])
                                                <x-tickets::chip-user class="right lastDropdown dropRight" :float="true" :show-name="false" :compact="true" :ticket-id="$row['id']" :editor-id="$row['editorId']" :editor-name="$row['editorFirstname'] ?? ''" :users="$users" :collaborators="$row['collaboratorPreview'] ?? []" :collaborator-overflow="$row['collaboratorOverflow'] ?? 0" />
                                                @endif

                                            </div>
                                            <div class="clearfix"></div>
                                            @endif

                                            @if ($programBoard)
                                                {{-- Cross-project board: columns are semantic stages, so give each card a
                                                     dropdown of its OWN project's real statuses (e.g. "Blocked") to set the
                                                     detailed status directly. patchTicket writes a key valid in that project,
                                                     so it stays orphan-safe. Also show which project the task belongs to. --}}
                                                @php $rowProjectStatuses = $statusLabelsByProject[$row['projectId']] ?? []; @endphp
                                                @php $rowProjectStatus = $rowProjectStatuses[$row['status']] ?? null; @endphp
                                                <div style="margin-top:4px;">
                                                    <x-tickets::chip-status style="display:inline-block;" :ticket-id="$row['id']" :status="$row['status']" :labels="$rowProjectStatuses" />
                                                    <small class="tw-text-[var(--secondary-font-color)]">{{ $tpl->escape($row['projectName'] ?? '') }}</small>
                                                </div>
                                            @endif

                                            @php
                                                $showComments = $cardFields['comments'] && $row['commentCount'] > 0;
                                                $showSubtasks = $cardFields['subtasks'] && $row['subtaskCount'] > 0;
                                                $showTags = $cardFields['tags'] && $row['tags'] != '';
                                                $showSprint = $cardFields['sprint'] && ! empty($row['sprintName']);
                                            @endphp
                                            @if ($showComments || $showSubtasks || $showTags || $showSprint)
                                            <div class="row">
                                                <div class="col-md-12 border-top" style="white-space: nowrap;">
                                                    @if ($showSprint)
                                                        <span title="{{ __('label.sprint') }}"><i class="fa fa-bars-progress" aria-hidden="true"></i> {{ $row['sprintName'] }}</span>&nbsp;
                                                    @endif

                                                    @if ($showComments)
                                                        <a href="#/tickets/showTicket/{{ $row['id'] }}"><span class="fa-regular fa-comments"></span> {{ $row['commentCount'] }}</a>&nbsp;
                                                    @endif

                                                    @if ($showSubtasks)
                                                        <a id="subtaskLink_{{ $row['id'] }}" href="#/tickets/showTicket/{{ $row['id'] }}" class="subtaskLineLink"> <span class="fa fa-diagram-successor"></span> {{ $row['subtaskCount'] }}</a>&nbsp;
                                                    @endif
                                                    @if ($showTags)
                                                        @php $tagsArray = explode(',', $row['tags']); @endphp
                                                        <x-global::actions.dropdown variant="panel" as="span" class="dropdown" menu-as="ul">
                                                            <x-slot:trigger><i class="fa fa-tags" aria-hidden="true"></i> {{ count($tagsArray) }}</x-slot:trigger>
                                                            <li style="padding:10px"><div class="tagsinput readonly">
                                                                @foreach ($tagsArray as $tag)
                                                                    <span class="tag"><span>{{ $tag }}</span></span>
                                                                @endforeach
                                                            </div></li>
                                                        </x-global::actions.dropdown>
                                                    @endif

                                                </div>

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

            @if ($group['label'] != 'all')
                </div> {{-- .kanban-swimlane-content --}}
                </div> {{-- .kanban-swimlane-row --}}
            @endif

        @endforeach

    </div>

</div>

@once @push('scripts')
<script type="text/javascript">

    jQuery(document).ready(function(){

    @if (can('tickets.edit'))
        leantime.ticketsController.initDueDateTimePickers();



        @if ($programBoard)
            {{-- Program board: columns are status types; drag persists per-project via the plugin. --}}
            var ticketStatusList = [@foreach ($allKanbanColumns as $key => $statusRow)'{{ $key }}',@endforeach];
            if (leantime.pgmProBoard && typeof leantime.pgmProBoard.initProgramKanban === 'function') {
                leantime.pgmProBoard.initProgramKanban(ticketStatusList);
            } else {
                console.warn('PgmPro board JS is not loaded; program kanban drag-and-drop is disabled.');
            }
        @else
            var ticketStatusList = [@foreach ($allTicketStates as $key => $statusRow)'{{ $key }}',@endforeach];
            leantime.ticketsController.initTicketKanban(ticketStatusList, { manualSort: {{ $isManualSort ? 'true' : 'false' }} });
        @endif

    @else
        leantime.authController.makeInputReadonly(".maincontentinner");
    @endif

    leantime.ticketsController.setUpKanbanColumns();


    (function initKanbanHorizontalScrollSync() {
        var header = document.querySelector('.kanban-column-headers');
        var rows = document.querySelectorAll('.sortableTicketList.kanbanBoard .row-fluid');
        var syncTargets = [];
        if (header) { syncTargets.push(header); }
        rows.forEach(function (r) { syncTargets.push(r); });

        // Batch via requestAnimationFrame and skip no-op writes: setting
        // scrollLeft on the other containers fires their own async scroll
        // events, which can arrive after a simple boolean guard has already
        // reset, causing a feedback loop that jitters during momentum
        // scrolling. Comparing against the target's current scrollLeft
        // before writing, and coalescing all pending syncs into a single
        // rAF tick, makes this deterministic regardless of event ordering.
        var pendingScrollLeft = null;
        var pendingSource = null;
        var rafScheduled = false;
        var expected = new Map();

        function flushSync() {
            rafScheduled = false;
            var scrollLeft = pendingScrollLeft;
            var source = pendingSource;
            syncTargets.forEach(function (el) {
                if (el !== source && el.scrollLeft !== scrollLeft) {
                    el.scrollLeft = scrollLeft;
                    expected.set(el, el.scrollLeft);
                }
            });
        }

        syncTargets.forEach(function (el) {
            el.addEventListener('scroll', function () {
                if (!window.matchMedia('(min-width: 1200px)').matches) { return; }
                var wasEcho = expected.has(el) && expected.get(el) === el.scrollLeft;
                expected.delete(el);
                if (wasEcho) { return; }
                pendingScrollLeft = el.scrollLeft;
                pendingSource = el;
                if (!rafScheduled) {
                    rafScheduled = true;
                    window.requestAnimationFrame(flushSync);
                }
            });
        });
    })();

    // Copilot review fix: the desktop-only overflow on .kanban-column-headers
    // (needed for the scroll-sync above) also clips each column's
    // "Edit label / Add column" dropdown menu, since Bootstrap 2's dropdown
    // plugin just toggles an "open" class and positions the menu with
    // ordinary `position: absolute` relative to the header -- which this
    // element now clips. Watch for that class toggle and, only while open,
    // switch the menu to `position: fixed` with live coordinates so it
    // escapes the clipping scrollport; revert on close so normal layout
    // (and the mobile/no-overflow case) is unaffected.
    (function initKanbanHeaderDropdownEscape() {
        var header = document.querySelector('.kanban-column-headers');
        if (!header) return;

        var containers = header.querySelectorAll('.inlineDropDownContainer');
        containers.forEach(function (container) {
            var menu = container.querySelector('.dropdown-menu');
            if (!menu) return;

            // position: fixed escapes the header's clipping scrollport, but
            // its coordinates are only correct at the instant it is applied.
            // If the page or the kanban board scrolls (or the window is
            // resized) while the menu is open, it would otherwise stay
            // frozen at its original spot instead of following the toggle
            // button. Reposition on every scroll (capture: true, so it also
            // catches scrolling on the header/row containers themselves,
            // which don't bubble a window scroll event) and on resize while
            // open, and stop listening as soon as it closes.
            function reposition() {
                var rect = container.getBoundingClientRect();
                menu.style.position = 'fixed';
                menu.style.top = rect.bottom + 'px';
                menu.style.left = 'auto';
                menu.style.right = (window.innerWidth - rect.right) + 'px';
                menu.style.zIndex = '1051';
            }

            function reset() {
                menu.style.position = '';
                menu.style.top = '';
                menu.style.left = '';
                menu.style.right = '';
                menu.style.zIndex = '';
            }

            function syncMenu() {
                if (window.matchMedia('(min-width: 1200px)').matches) {
                    reposition();
                } else {
                    reset();
                }
            }

            var observer = new MutationObserver(function () {
                if (container.classList.contains('open')) {
                    syncMenu();
                    window.addEventListener('scroll', syncMenu, true);
                    window.addEventListener('resize', syncMenu);
                } else {
                    reset();
                    window.removeEventListener('scroll', syncMenu, true);
                    window.removeEventListener('resize', syncMenu);
                }
            });
            observer.observe(container, { attributes: true, attributeFilter: ['class'] });
        });
    })();

        @if (isset($_GET['showTicketModal']))
            @php
                $modalUrl = $_GET['showTicketModal'] == '' ? '' : '/'.(int) $_GET['showTicketModal'];
            @endphp

        leantime.ticketsController.openTicketModalManually("{{ BASE_URL }}/tickets/showTicket{{ $modalUrl }}");
        window.history.pushState({},document.title, '{{ BASE_URL }}/tickets/showKanban');

        @endif


        @php
        foreach ($allTicketGroups as $group) {
            foreach ($group['items'] as $ticket) {
                if ($ticket['dependingTicketId'] > 0) {
        @endphp
            var startElement =  document.getElementById('subtaskLink_{{ $ticket['dependingTicketId'] }}');
            var endElement =  document.getElementById('ticket_{{ $ticket['id'] }}');


            if ( startElement != undefined && endElement != undefined) {

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

                jQuery("#ticket_{{ $ticket['id'] }}").mousedown(function () {

                })
                    .mousemove(function () {

                    })
                    .mouseup(function () {
                        line{{ $ticket['id'] }}.position();
                    });

                jQuery("#ticket_{{ $ticket['dependingTicketId'] }}").mousedown(function () {

                    })
                    .mousemove(function () {


                    })
                    .mouseup(function () {
                        line{{ $ticket['id'] }}.position();

                    });

            }

        @php
                }
            }
        }
        @endphp




    });
</script>
@endpush @endonce

@endsection
