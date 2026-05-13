@props([
    'tickets' => [],
])

<div id="recentlyUpdatedWidget" class="tw-p-s">
    <h3 class="widgettitle title-light tw-mb-s">
        <i class="fa fa-clock-rotate-left"></i> {{ __('widgets.title.recently_updated') }}
    </h3>

    @if (count($tickets) === 0)
        <x-global::emptyState
            icon="fa-clock-rotate-left"
            headline="{{ __('widgets.text.no_recent_activity') }}"
            description="{{ __('widgets.text.no_recent_activity_hint') }}"
        />
    @else
        <ul class="tw-list-none tw-p-0 tw-m-0">
            @foreach($tickets as $ticket)
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
                        <small class="tw-text-right tw-whitespace-nowrap" style="color:var(--grey);">
                            @if(!empty($ticket['editFrom']) && ! str_starts_with($ticket['editFrom'], '0000'))
                                {{ \Carbon\Carbon::parse($ticket['editFrom'])->diffForHumans() }}
                            @endif
                        </small>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</div>
