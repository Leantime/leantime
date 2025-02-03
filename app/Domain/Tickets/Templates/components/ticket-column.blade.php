@props([
    'status' => '',
    'statusKey' => '',
    'allTickets' => [],
    'ticketTypeIcons' => [],
    'priorities' => [],
    'efforts' => [],
    'milestones' => [],
    'users' => [],
    'onTheClock' => false,
    'searchCriteria' => [],
])

@if (!empty($searchCriteria))
    @php
        $searchCriteria['status'] = $status;
        $queryString = http_build_query($searchCriteria);
        $url = rtrim('/hx/tickets/ticketColumn/get', '?') . '?' . $queryString;
    @endphp
@endif


@if (!empty($status))
    <div class="column ticketColumn" id="ticketColumn_{{ $status }}" hx-get="{{ $url }}" hx-swap="innerHTML"
        hx-trigger="load, reload from:body">
        <x-global::elements.loadingText :count="random_int(1, 5)" :type="'ticket-column-card'" />
    </div>
@else
    <div class="column">
        <div class="contentInner status_{{ $statusKey }}">
            @foreach ($allTickets as $ticket)
                <div class="moveable-card">
                    <x-tickets::cards.ticket-card :ticket="$ticket" :statusKey="$statusKey" :todoTypeIcons="$ticketTypeIcons"
                        :priorities="$priorities" :efforts="$efforts" :milestones="$milestones" :users="$users" :onTheClock="$onTheClock"
                        type="kanban" />
                </div>
            @endforeach
        </div>
    </div>
@endif
