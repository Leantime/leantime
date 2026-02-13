@props([
    'type' => 'info',
    'dismissible' => false,
    'icon' => null,
])

@php
    $typeClass = match($type) {
        'success' => 'tw:alert-success',
        'warning' => 'tw:alert-warning',
        'error'   => 'tw:alert-error',
        default   => 'tw:alert-info',
    };
    $defaultIcon = match($type) {
        'success' => 'fa-solid fa-circle-check',
        'warning' => 'fa-solid fa-triangle-exclamation',
        'error'   => 'fa-solid fa-circle-xmark',
        default   => 'fa-solid fa-circle-info',
    };
@endphp

<div {{ $attributes->merge(['class' => "tw:alert $typeClass", 'role' => 'alert']) }}>
    <i class="{{ $icon ?? $defaultIcon }}"></i>
    <span>{{ $slot }}</span>
    @if($dismissible)
        <button type="button" class="tw:btn tw:btn-sm tw:btn-ghost" onclick="this.closest('.tw\\:alert').remove()">
            <i class="fa fa-xmark"></i>
        </button>
    @endif
</div>
