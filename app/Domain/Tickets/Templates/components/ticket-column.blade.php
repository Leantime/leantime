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
])
@if (!empty($status))
    <div class="column ticketColumn" id="ticketColumn_{{ $status }}"
        hx-get="{{ BASE_URL }}/hx/tickets/ticketColumn/get?status={{ $status }}" hx-swap="innerHTML"
        hx-trigger="load, reload from:body">
        <x-global::elements.loader id="loadingthis" size="25px" />
    </div>
@else
    <div class="column">
        <div class="contentInner status_{{ $statusKey }}">
            @foreach ($allTickets as $ticket)
                @if ($ticket['status'] == $statusKey)
                    <x-tickets::ticket-column-card :ticket="$ticket" :statusKey="$statusKey" :todoTypeIcons="$ticketTypeIcons"
                        :priorities="$priorities" :efforts="$efforts" :milestones="$milestones" :users="$users" :onTheClock="$onTheClock" />
                @endif
            @endforeach
        </div>
    </div>
@endif
