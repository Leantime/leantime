@props([
    'people' => [],
    'max' => 5,
])

@php
    $colorMap = [
        'full' => '#6B7A4D',
        'partial' => '#5C8A8A',
        'open' => '#D1D5DB',
    ];
    $visible = array_slice($people, 0, $max);
    $overflow = max(0, count($people) - $max);
@endphp

<div {{ $attributes->merge(['class' => 'tw:flex tw:items-center']) }}
     style="direction: ltr;">
    @foreach($visible as $i => $person)
        <div class="tw:inline-flex tw:items-center tw:justify-center tw:rounded-full tw:border-2 tw:border-[var(--secondary-background)]"
             style="width: 24px; height: 24px; font-size: 10px; font-weight: 600; color: {{ ($person['status'] ?? '') === 'open' ? '#6B7280' : '#fff' }}; background: {{ $colorMap[$person['status'] ?? 'open'] ?? '#D1D5DB' }};{{ $i > 0 ? ' margin-left: -6px;' : '' }}"
        >{{ $person['initials'] ?? '?' }}</div>
    @endforeach
    @if($overflow > 0)
        <div class="tw:inline-flex tw:items-center tw:justify-center tw:rounded-full tw:border-2 tw:border-[var(--secondary-background)]"
             style="width: 24px; height: 24px; font-size: 10px; font-weight: 600; color: #6B7280; background: #E5E7EB; margin-left: -6px;">
            +{{ $overflow }}
        </div>
    @endif
</div>
