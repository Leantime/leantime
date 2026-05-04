@props([
    'item' => [],
    'canEdit' => false,
])

@php
    $isDone = ($item['status'] ?? 'open') === 'done';
@endphp

<li class="tw-mb-xs tw-p-s tw-rounded tw-flex tw-items-start tw-gap-s"
    style="background:var(--primary-background); border:1px solid var(--main-border-color);">

    @if ($canEdit)
        <button type="button"
                class="btn btn-xs tw-shrink-0"
                hx-patch="{{ BASE_URL }}/hx/oneonone/sessionItems/toggleItem"
                hx-vals='@json(["itemId" => $item["id"]])'
                hx-target="#oneononeItemList"
                hx-swap="innerHTML"
                title="{{ __($isDone ? 'buttons.mark_open' : 'buttons.mark_done') }}">
            <span class="fa {{ $isDone ? 'fa-check-square' : 'fa-square' }}"></span>
        </button>
    @else
        <span class="fa {{ $isDone ? 'fa-check-square' : 'fa-square' }} tw-mt-xs tw-shrink-0"></span>
    @endif

    <div class="tw-flex-1 tw-min-w-0">
        <div class="@if($isDone) tw-line-through @endif"
             style="@if($isDone) color:var(--grey); @endif word-break:break-word;">
            {{ $item['content'] ?? '' }}
        </div>
        <small class="tw-text-xs" style="color:var(--grey);">
            @if (!empty($item['authorFirstname']))
                <span class="fa fa-user"></span>
                {{ $item['authorFirstname'] }} {{ $item['authorLastname'] }}
            @endif
            @if (!empty($item['assigneeFirstname']))
                &middot; <span class="fa fa-arrow-right"></span>
                {{ $item['assigneeFirstname'] }} {{ $item['assigneeLastname'] }}
            @endif
            @if (!empty($item['dueDate']))
                &middot; <span class="fa fa-calendar"></span>
                {{ dtHelper()->parseDbDateTime($item['dueDate'])->setToUserTimezone()->format(__('language.dateformat')) }}
            @endif
            @if (!empty($item['linkedTicketId']) && !empty($item['ticketHeadline']))
                &middot; <a href="{{ BASE_URL }}/tickets/showTicket/{{ $item['linkedTicketId'] }}">
                    <span class="fa fa-link"></span> {{ \Illuminate\Support\Str::limit($item['ticketHeadline'], 40) }}
                </a>
            @endif
        </small>
    </div>

    @if ($canEdit)
        <button type="button"
                class="btn btn-xs tw-shrink-0"
                hx-delete="{{ BASE_URL }}/hx/oneonone/sessionItems/deleteItem"
                hx-vals='@json(["itemId" => $item["id"]])'
                hx-confirm="{{ __('text.oneonone.confirm_delete_item') }}"
                hx-target="#oneononeItemList"
                hx-swap="innerHTML"
                title="{{ __('buttons.delete') }}">
            <span class="fa fa-times" style="color:var(--grey);"></span>
        </button>
    @endif
</li>
