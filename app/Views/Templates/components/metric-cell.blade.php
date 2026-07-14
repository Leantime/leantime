@props([
    'label' => '',
    'value' => '',
    'suffix' => null,
    'secondary' => null,
    'color' => '#1A1A2E',
    'secondaryColor' => '#9CA3AF',
])

<div class="tw-px-4 tw-py-3.5">
    <div class="tw-text-[11px] tw-font-medium tw-text-[#9CA3AF] tw-mb-1">
        {{ $label }}
    </div>
    <div class="tw-flex tw-items-baseline tw-gap-1">
        <span class="tw-text-[22px] tw-font-bold" style="color: {{ $color }}">
            {{ $value }}
        </span>
        @if($suffix)
            <span class="tw-text-xs tw-font-normal tw-text-[#9CA3AF]">{{ $suffix }}</span>
        @endif
    </div>
    @if($secondary)
        <div class="tw-text-[11px] tw-mt-0.5" style="color: {{ $secondaryColor }}">
            {{ $secondary }}
        </div>
    @endif
    {{-- Optional slot for inline visualizations --}}
    {{ $slot ?? '' }}
</div>
