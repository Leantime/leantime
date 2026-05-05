@props([
    'openActionItems' => [],
])

@php
    $csrfValue = $csrf_token ?? null;
@endphp

<div class="maincontentinner" id="oneononeMyActionItems"
     hx-get="{{ BASE_URL }}/hx/oneonone/sessionItems/myOpen"
     hx-trigger="oneonone_item_changed from:body"
     hx-swap="outerHTML">
    <h4 class="widgettitle title-light">{{ __('headlines.oneonone.my_action_items') }}</h4>

    @if (count($openActionItems) === 0)
        <p class="tw-text-sm">{{ __('text.oneonone.no_open_actions') }}</p>
    @else
        <ul class="tw-list-none tw-p-0 tw-m-0">
            @foreach ($openActionItems as $item)
                @php
                    $itemHxVals = [
                        'itemId' => (int) ($item['id'] ?? 0),
                        'sessionId' => (int) ($item['sessionId'] ?? 0),
                        'csrf_token' => $csrfValue,
                    ];
                @endphp
                <li class="tw-mb-s tw-p-s tw-rounded"
                    style="border:1px solid var(--main-border-color); background:var(--secondary-background);">
                    <div class="tw-flex tw-justify-between tw-items-start tw-gap-s">
                        <div class="tw-flex-1">
                            <div>{{ $item['content'] }}</div>
                            <small class="tw-text-xs" style="color:var(--grey);">
                                @if (!empty($item['dueDate']))
                                    <span class="fa fa-calendar"></span>
                                    {{ dtHelper()->parseDbDateTime($item['dueDate'])->setToUserTimezone()->format(__('language.dateformat')) }} &middot;
                                @endif
                                <a href="{{ BASE_URL }}/oneonone/showSession/{{ $item['sessionId'] }}">
                                    {{ __('text.oneonone.from_session') }}
                                </a>
                            </small>
                        </div>
                        <button type="button"
                                class="btn btn-xs"
                                hx-patch="{{ BASE_URL }}/hx/oneonone/sessionItems/toggleItem"
                                hx-vals='@json($itemHxVals)'
                                hx-swap="none"
                                title="{{ __('buttons.mark_done') }}">
                            <span class="fa fa-check"></span>
                        </button>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</div>
