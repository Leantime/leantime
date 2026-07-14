@props([
    'people' => [],
    'size' => 28,
    'overlap' => -6,
    'maxShow' => 8,
])

@php
    $visible = array_slice($people, 0, $maxShow);
    $remaining = count($people) - $maxShow;
    $statusColors = [
        'full' => '#059669',
        'partial' => '#4A85B5',
        'open' => 'transparent',
    ];
@endphp

<div class="tw-flex tw-items-center">
    @foreach($visible as $i => $person)
        @php
            $bg = $statusColors[$person['status'] ?? 'partial'] ?? '#4A85B5';
            $isOpen = ($person['status'] ?? '') === 'open';
        @endphp
        <div
            class="tw-rounded-full tw-flex tw-items-center tw-justify-center tw-font-bold tw-text-white tw-flex-shrink-0
                   {{ $isOpen ? 'tw-border-2 tw-border-dashed tw-border-[#D1D5DB]' : '' }}"
            style="width: {{ $size }}px; height: {{ $size }}px;
                   font-size: {{ $size * 0.32 }}px;
                   background: {{ $bg }};
                   {{ $i > 0 ? 'margin-left: ' . $overlap . 'px;' : '' }}
                   {{ !$isOpen ? 'border: 2px solid white;' : '' }}"
        >
            {{ $person['initials'] ?? '?' }}
        </div>
    @endforeach

    @if($remaining > 0)
        <div
            class="tw-rounded-full tw-flex tw-items-center tw-justify-center tw-font-bold tw-text-[#9CA3AF] tw-bg-[#F0F1F3] tw-flex-shrink-0"
            style="width: {{ $size }}px; height: {{ $size }}px;
                   font-size: {{ $size * 0.32 }}px;
                   margin-left: {{ $overlap }}px;
                   border: 2px solid white;"
        >
            +{{ $remaining }}
        </div>
    @endif
</div>
