@props([
    'tickets' => [],
])

<div id="waitingOnMeWidget" class="tw-p-s">
    <h3 class="widgettitle title-light tw-mb-s">
        <i class="fa fa-hourglass-half"></i> {{ __('widgets.title.waiting_on_me') }}
    </h3>

    @if (count($tickets) === 0)
        <x-global::emptyState
            icon="fa-check-circle"
            headline="{{ __('widgets.text.nothing_waiting') }}"
            description="{{ __('widgets.text.nothing_waiting_hint') }}"
        />
    @else
        <ul class="tw-list-none tw-p-0 tw-m-0">
            @foreach($tickets as $ticket)
                @php
                    $isOverdue = !empty($ticket['dateToFinish'])
                        && ! str_starts_with($ticket['dateToFinish'], '0000')
                        && \Carbon\Carbon::parse($ticket['dateToFinish'])->isPast();
                    $isWaitingStatus = (int) ($ticket['status'] ?? 0) === 4;
                @endphp
                <li class="tw-py-xs tw-border-b" style="border-color:var(--main-border-color);">
                    <div class="tw-flex tw-justify-between tw-items-start tw-gap-s">
                        <div class="tw-flex-1 tw-min-w-0">
                            <a href="#/tickets/showTicket/{{ $ticket['id'] }}"
                               preload="mouseover"
                               class="tw-text-sm tw-font-medium tw-truncate tw-block">
                                {{ $ticket['headline'] }}
                            </a>
                            <small style="color:var(--grey);">
                                {{ $ticket['projectName'] ?? '' }}
                            </small>
                        </div>
                        <div class="tw-text-right tw-whitespace-nowrap">
                            @if($isWaitingStatus)
                                <span class="label label-warning tw-text-xs">
                                    {{ __('label.status_for_approval') }}
                                </span>
                            @else
                                <span class="label label-default tw-text-xs">
                                    {{ __('widgets.text.stale') }}
                                </span>
                            @endif
                            @if($isOverdue)
                                <br />
                                <small class="tw-text-red-500">
                                    {{ \Carbon\Carbon::parse($ticket['dateToFinish'])->diffForHumans() }}
                                </small>
                            @elseif(!empty($ticket['editFrom']) && ! str_starts_with($ticket['editFrom'], '0000'))
                                <br />
                                <small style="color:var(--grey);">
                                    {{ \Carbon\Carbon::parse($ticket['editFrom'])->diffForHumans() }}
                                </small>
                            @endif
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</div>
