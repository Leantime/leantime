@props([
    'title' => '',
    'icon' => '',
    'defaultOpen' => true,
])

@php
    $uid = 'collapsible-' . crc32($title . rand());
@endphp

<div {{ $attributes->merge(['class' => 'collapsible-section']) }}
     data-collapsible-id="{{ $uid }}">
    <div class="tw:flex tw:items-center tw:justify-between tw:cursor-pointer tw:select-none tw:py-2 tw:px-1 tw:rounded tw:hover:bg-[var(--secondary-background)]"
         onclick="(function(el){
            var body = el.closest('[data-collapsible-id]').querySelector('[data-collapsible-body]');
            var summary = el.closest('[data-collapsible-id]').querySelector('[data-collapsible-summary]');
            var chevron = el.querySelector('[data-collapsible-chevron]');
            var expanded = body.style.display !== 'none';
            body.style.display = expanded ? 'none' : 'block';
            if (summary) summary.style.display = expanded ? 'flex' : 'none';
            chevron.textContent = expanded ? 'chevron_right' : 'expand_more';
         })(this)">
        <div class="tw:flex tw:items-center tw:gap-2">
            <span class="material-symbols-outlined" data-collapsible-chevron aria-hidden="true">{{ $defaultOpen ? 'expand_more' : 'chevron_right' }}</span>
            @if($icon)
                <span>{{ $icon }}</span>
            @endif
            <span class="tw:font-semibold" style="font-size: var(--font-size-s, 13px);">{{ $title }}</span>

            @isset($collapsedSummary)
                <div data-collapsible-summary
                     class="tw:flex tw:items-center tw:ml-2"
                     style="display: {{ $defaultOpen ? 'none' : 'flex' }};">
                    {{ $collapsedSummary }}
                </div>
            @endisset
        </div>

        @isset($headerActions)
            <div class="tw:flex tw:items-center">{{ $headerActions }}</div>
        @endisset
    </div>

    <div data-collapsible-body style="display: {{ $defaultOpen ? 'block' : 'none' }};">
        {{ $slot }}
    </div>
</div>
