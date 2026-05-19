<div class="htmx-indicator full-width-loader">
    <div class="indeterminate"></div>
</div>

<div id="myTodosKanbanContainer"
    hx-get="{{ BASE_URL }}/hx/widgets/myToDosKanban/get"
    hx-trigger="{{ \Leantime\Domain\Tickets\Htmx\HtmxTicketEvents::UPDATE }} from:body"
    hx-swap="outerHTML"
    hx-indicator=".htmx-indicator">

    {{-- Toolbar: view toggle + project filter --}}
    <div class="tw-flex tw-items-center tw-gap-1" style="position:absolute; top:10px; right:35px;">

        {{-- View toggle: List | Kanban | Calendar | Recently Updated --}}
        <div class="btn-group left" style="margin-right:4px;">
            <button class="btn btn-link btn-round-icon"
                title="{{ __('buttons.list_view') }}"
                aria-label="{{ __('buttons.list_view') }}"
                hx-get="{{ BASE_URL }}/widgets/myToDos/get"
                hx-target="#myTodosKanbanContainer"
                hx-swap="outerHTML"
                hx-indicator=".htmx-indicator"
                hx-vals='{"projectFilter": "{{ $projectFilter }}"}'>
                <span class="fa-solid fa-list"></span>
            </button>
            <button class="btn btn-link btn-round-icon active"
                title="Kanban"
                aria-label="Kanban"
                style="color:var(--accent1);">
                <span class="fa-solid fa-table-columns"></span>
            </button>
            <button class="btn btn-link btn-round-icon"
                title="Calendar"
                aria-label="Calendar"
                hx-get="{{ BASE_URL }}/hx/widgets/myToDosCalendar/get"
                hx-target="#myTodosKanbanContainer"
                hx-swap="outerHTML"
                hx-indicator=".htmx-indicator">
                <span class="fa-solid fa-calendar-days"></span>
            </button>
            <button class="btn btn-link btn-round-icon"
                title="Recently Updated"
                aria-label="Recently Updated"
                hx-get="{{ BASE_URL }}/hx/widgets/myToDosRecentlyUpdated/get"
                hx-target="#myTodosKanbanContainer"
                hx-swap="outerHTML"
                hx-indicator=".htmx-indicator">
                <span class="fa-solid fa-clock-rotate-left"></span>
            </button>
        </div>

        {{-- Project filter --}}
        <div class="btn-group left">
            <button class="btn btn-link btn-round-icon dropdown-toggle" type="button"
                data-toggle="dropdown" data-tippy-content="{{ __('text.filter') }}">
                <i class="fas fa-filter"></i>
                @if($projectFilter != '')
                <span class="badge badge-primary">1</span>
                @endif
            </button>
            <ul class="dropdown-menu pull-right">
                <li class="nav-header">{{ __('text.filter') }}</li>
                <li @if($projectFilter == '') class="active" @endif>
                    <a href="javascript:void(0);"
                        hx-get="{{ BASE_URL }}/hx/widgets/myToDosKanban/get"
                        hx-target="#myTodosKanbanContainer"
                        hx-swap="outerHTML"
                        hx-vals='{"projectFilter": "all"}'>
                        {{ __('labels.all_projects') }}
                    </a>
                </li>
                @foreach($allAssignedprojects as $project)
                <li @if($projectFilter == $project['id']) class="active" @endif>
                    <a href="javascript:void(0);"
                        hx-get="{{ BASE_URL }}/hx/widgets/myToDosKanban/get"
                        hx-target="#myTodosKanbanContainer"
                        hx-swap="outerHTML"
                        hx-vals='{"projectFilter": "{{ $project['id'] }}"}'>
                        {{ $project['name'] }}
                    </a>
                </li>
                @endforeach
            </ul>
        </div>
    </div>

    {{-- Kanban board — single "all" group, no swimlanes --}}
    <div class="sortableTicketList kanbanBoard" id="kanboard-widget-todos">
        <div class="row-fluid">

            @foreach($kanbanColumns as $statusKey => $statusRow)
            @php
                $columnTickets = collect($allTickets)->filter(fn($t) => (int)$t['status'] === (int)$statusKey);
                $hasTickets = $columnTickets->isNotEmpty();
            @endphp
            <div class="column">
                <h4 class="widgettitle title-primary title-border-{{ $statusRow['class'] }}">
                    <strong class="count">{{ $columnTickets->count() }}</strong>
                    {{ __($statusRow['name']) }}
                </h4>
                <div class="contentInner status_{{ $statusKey }} {{ !$hasTickets ? 'empty-column' : '' }}"
                    role="list"
                    aria-label="{{ !$hasTickets ? 'Empty column' : __($statusRow['name']).' column items' }}"
                    data-empty-text="{{ !$hasTickets ? 'Empty' : '' }}">

                    {{-- Quick-add link --}}
                    <a href="{{ BASE_URL }}/tickets/newTicket"
                        id="ticket_new_link_group_{{ $statusKey }}"
                        style="display:block; padding:6px 4px; opacity:.6; font-size:var(--font-size-s); text-decoration:none; color:var(--primary-font-color);">
                        <i class="fa fa-plus-circle"></i> {{ __('links.add_task') }}
                    </a>

                    @foreach($allTickets as $row)
                    @if((int)$row['status'] === (int)$statusKey)
                    <div class="ticketBox moveable container priority-border-{{ $row['priority'] }}"
                        id="ticket_{{ $row['id'] }}">

                        {{-- Task name — click to open detail --}}
                        <div class="kanbanCardContent" style="margin-bottom:8px;">
                            <a href="#/tickets/showTicket/{{ $row['id'] }}"
                                preload="mouseover"
                                style="font-weight:600; font-size:var(--base-font-size); color:var(--primary-font-color); text-decoration:none; display:block; line-height:1.4;">
                                {{ $row['headline'] }}
                            </a>
                        </div>

                        {{-- Due date + Priority row --}}
                        <div class="tw-flex tw-items-center tw-justify-between tw-gap-2" style="margin-top:6px;">

                            {{-- Due date --}}
                            @if(!empty($row['dateToFinish']) && $row['dateToFinish'] !== '0000-00-00 00:00:00' && $row['dateToFinish'] !== '1969-12-31 00:00:00')
                            <span style="font-size:var(--font-size-xs); color:var(--primary-font-color); opacity:.7;">
                                <i class="fa-solid fa-calendar-days" style="margin-right:3px;"></i>{{ format($row['dateToFinish'])->date() }}
                            </span>
                            @else
                            <span></span>
                            @endif

                            {{-- Priority badge --}}
                            @if(!empty($row['priority']) && $row['priority'] > 0)
                            <span class="priority-bg-{{ $row['priority'] }}"
                                style="font-size:var(--font-size-xs); padding:2px 8px; border-radius:var(--element-radius); font-weight:500;">
                                {{ $priorities[$row['priority']] ?? '' }}
                            </span>
                            @endif

                        </div>

                    </div>{{-- /.ticketBox --}}
                    @endif
                    @endforeach

                </div>{{-- /.contentInner --}}
            </div>{{-- /.column --}}
            @endforeach

            <div class="clearfix"></div>
        </div>{{-- /.row-fluid --}}
    </div>{{-- /.sortableTicketList --}}

</div>{{-- #myTodosKanbanContainer --}}

<script type="text/javascript">
(function initKanbanWidget() {
    var ticketStatusList = [
        @foreach($allTicketStates as $key => $statusRow)
        '{{ $key }}',
        @endforeach
    ];
    leantime.ticketsController.initTicketKanban(ticketStatusList);
    leantime.ticketsController.setUpKanbanColumns();
})();

htmx.onLoad(function () {
    var ticketStatusList = [
        @foreach($allTicketStates as $key => $statusRow)
        '{{ $key }}',
        @endforeach
    ];
    leantime.ticketsController.initTicketKanban(ticketStatusList);
    leantime.ticketsController.setUpKanbanColumns();
});
</script>
