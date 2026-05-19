<div class="htmx-indicator full-width-loader">
    <div class="indeterminate"></div>
</div>

<div id="myTodosRecentlyUpdatedContainer">

    {{-- Toolbar: view toggle --}}
    <div class="tw-flex tw-items-center tw-gap-1" style="position:absolute; top:10px; right:35px;">

        {{-- View toggle: List | Kanban | Calendar | Recently Updated --}}
        <div class="btn-group left" style="margin-right:4px;">
            <button class="btn btn-link btn-round-icon"
                title="{{ __('buttons.list_view') }}"
                aria-label="{{ __('buttons.list_view') }}"
                hx-get="{{ BASE_URL }}/widgets/myToDos/get"
                hx-target="#myTodosRecentlyUpdatedContainer"
                hx-swap="outerHTML"
                hx-indicator=".htmx-indicator">
                <span class="fa-solid fa-list"></span>
            </button>
            <button class="btn btn-link btn-round-icon"
                title="Kanban"
                aria-label="Kanban"
                hx-get="{{ BASE_URL }}/hx/widgets/myToDosKanban/get"
                hx-target="#myTodosRecentlyUpdatedContainer"
                hx-swap="outerHTML"
                hx-indicator=".htmx-indicator">
                <span class="fa-solid fa-table-columns"></span>
            </button>
            <button class="btn btn-link btn-round-icon"
                title="Calendar"
                aria-label="Calendar"
                hx-get="{{ BASE_URL }}/hx/widgets/myToDosCalendar/get"
                hx-target="#myTodosRecentlyUpdatedContainer"
                hx-swap="outerHTML"
                hx-indicator=".htmx-indicator">
                <span class="fa-solid fa-calendar-days"></span>
            </button>
            <button class="btn btn-link btn-round-icon active"
                title="Recently Updated"
                aria-label="Recently Updated"
                style="color:var(--accent1);">
                <span class="fa-solid fa-clock-rotate-left"></span>
            </button>
        </div>

    </div>

    {{-- Recently updated task list --}}
    <div style="margin-top:44px;">

        @if(count($tickets) === 0)
            <x-global::emptyState
                icon="fa-clock-rotate-left"
                headline="{{ __('widgets.text.no_recent_activity') }}"
                description="{{ __('widgets.text.no_recent_activity_hint') }}" />
        @else
            <ul class="tw-list-none tw-p-0 tw-m-0">
                @foreach($tickets as $ticket)
                <li class="tw-py-xs tw-border-b" style="border-color:var(--main-border-color);">
                    <div class="tw-flex tw-justify-between tw-items-center tw-gap-s">

                        <div class="tw-flex-1 tw-min-w-0">
                            <a href="#/tickets/showTicket/{{ $ticket['id'] }}"
                                preload="mouseover"
                                class="tw-font-medium tw-block"
                                style="font-size:var(--base-font-size); color:var(--primary-font-color); text-decoration:none; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                {{ $ticket['headline'] }}
                            </a>
                            <small style="color:var(--grey);">{{ $ticket['projectName'] ?? '' }}</small>
                        </div>

                        <div class="tw-flex tw-items-center tw-gap-2 tw-shrink-0">
                            @if(!empty($ticket['priority']) && $ticket['priority'] > 0)
                            <span class="priority-bg-{{ $ticket['priority'] }}"
                                style="font-size:var(--font-size-xs); padding:2px 8px; border-radius:var(--element-radius); font-weight:500; white-space:nowrap;">
                                {{ $priorities[$ticket['priority']] ?? '' }}
                            </span>
                            @endif

                            <small class="tw-text-right tw-whitespace-nowrap" style="color:var(--grey);">
                                @if(!empty($ticket['editFrom']) && !str_starts_with($ticket['editFrom'], '0000'))
                                    {{ \Carbon\Carbon::parse($ticket['editFrom'])->diffForHumans() }}
                                @endif
                            </small>
                        </div>

                    </div>
                </li>
                @endforeach
            </ul>
        @endif

    </div>

</div>
