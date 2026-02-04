@props([
    'allTicketGroups' => [],
    'allKanbanColumns' => [],
    'searchCriteria' => [],

    'ticketTypeIcons' => [],
    'priorities' => [],
    'efforts' => [],
    'milestones' => [],
    'users' => [],
    'onTheClock' => false,
])

@if (!empty($searchCriteria))
    @php
        $queryString = http_build_query($searchCriteria);
        $url = rtrim('/hx/tickets/showKanban/get', '?') . '?' . $queryString;
    @endphp
@endif

@if (empty($allTicketGroups))
    <div hx-get="{{ $url }}" hx-swap="outerHTML" hx-trigger="load, reload from:body" class="sortableTicketList kanbanBoard">
        <div class="row-fluid">
            @foreach ($allKanbanColumns as $status => $statusRow)
                <div class="column ticketColumn" id="kanboard-" style="margin-top:-5px;">
                    <x-global::elements.loadingText :count="random_int(1, 5)" :type="'ticket-column-card'" />
                </div>
            @endforeach
            <div class="clearfix"></div>
        </div>
    </div>
    </div>
@else
    @foreach ($allTicketGroups as $key => $group)
        @php
            $allTickets = $group['items'];
        @endphp

        @if ($group['label'] != 'all')
            @php
                if (!empty($searchCriteria['groupBy']) && !empty($group['id'])) {
                    $groupBy = $searchCriteria['groupBy'];
                    $searchCriteria[$groupBy] = rtrim($group['id']);
                }
            @endphp
            <h5 class="accordionTitle kanbanLane {{ $group['class'] }}" id="accordion_link_{{ $group['id'] }}">
                <a href="javascript:void(0)" class="accordion-toggle" id="accordion_toggle_{{ $group['id'] }}"
                    onclick="leantime.snippets.accordionToggle('{{ $group['id'] }}');">
                    <i class="fa fa-angle-down"></i>{!! $group['label'] !!} ({{ count($group['items']) }})
                </a>
            </h5>
            <div class="simpleAccordionContainer kanban" id="accordion_content-{{ $group['id'] }}">
        @endif

        <div class="sortableTicketList kanbanBoard" id="kanboard-{{ $group['id'] }}" style="margin-top:-5px;">
            <div class="row-fluid">
                @foreach ($allKanbanColumns as $status => $statusRow)
                    @php
                        $searchCriteriaByColumn = $searchCriteria;
                        $searchCriteriaByColumn['status'] = $status;
                    @endphp
                    <x-tickets::ticket-column :allTickets="$allTickets" :ticketTypeIcons="$ticketTypeIcons" :priorities="$priorities" :efforts="$efforts"
                        :milestones="$milestones" :users="$users" :onTheClock="$onTheClock" :statusKey="$status" :searchCriteria="$searchCriteriaByColumn" />
                @endforeach
                <div class="clearfix"></div>
            </div>
        </div>

        @if ($group['label'] != 'all')
            </div>
        @endif
    @endforeach
@endif
