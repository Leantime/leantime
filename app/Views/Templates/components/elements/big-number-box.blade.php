@props([
    'number' => null,   // The big value — text, number, or emoji+number
    'label'  => null,   // Descriptive text below the number
    'state'  => null,   // null|'on-track'|'at-risk'|'miss' — maps to priority-border-*
])

@php
    $stateClass = match($state) {
        'on-track' => 'priority-border-4',
        'at-risk'  => 'priority-border-3',
        'miss'     => 'priority-border-1',
        default    => '',
    };
@endphp

<div {{ $attributes->merge(['class' => 'bigNumberBox ' . $stateClass]) }}>
    <div class="bigNumberBoxInner">
        @if($number !== null)
            <div class="bigNumberBoxNumber">{{ $number }}</div>
        @endif
        @if($slot->isNotEmpty())
            {{ $slot }}
        @endif
        @if($label !== null)
            <div class="bigNumberBoxText">{{ $label }}</div>
        @endif
    </div>
</div>
