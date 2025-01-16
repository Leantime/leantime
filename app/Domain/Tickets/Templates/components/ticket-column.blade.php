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
    'searchcriteria' => []
])

@php
    $searchcriteria['status'] = $status;
@endphp

@if (!empty($searchcriteria))
    @php
        $queryString = http_build_query($searchcriteria);
        $url = rtrim('/hx/tickets/ticketColumn/get', '?') . '?' . $queryString;
    @endphp
@endif


@if (!empty($status))
    <div class="column ticketColumn" id="ticketColumn_{{ $status }}"
        hx-get="{{ $url }}" hx-swap="innerHTML"
        hx-trigger="load, reload from:body">
        <x-global::elements.loadingText :count="random_int(1, 5)" :type="'ticket-column-card'" />
    </div>
@else
    <div class="column">
        <div class="contentInner status_{{ $statusKey }}">
            @foreach ($allTickets as $ticket)
                @if ($ticket['status'] == $statusKey)
                    <x-tickets::cards.ticket-card
                            :ticket="$ticket"
                            :statusKey="$statusKey"
                            :todoTypeIcons="$ticketTypeIcons"
                            :priorities="$priorities"
                            :efforts="$efforts"
                            :milestones="$milestones"
                            :users="$users"
                            :onTheClock="$onTheClock"
                            show-project="false" />
                @endif
            @endforeach
        </div>
    </div>
@endif
