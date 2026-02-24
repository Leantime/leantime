@props([
    'type' => 'info',
    'dismissible' => false,
    'icon' => null,
])

@php
    $bsClass = match($type) {
        'success' => 'alert-success',
        'warning' => 'alert-warning',
        'error'   => 'alert-danger',
        default   => 'alert-info',
    };
    $defaultIcon = match($type) {
        'success' => 'fa-solid fa-circle-check',
        'warning' => 'fa-solid fa-triangle-exclamation',
        'error'   => 'fa-solid fa-circle-xmark',
        default   => 'fa-solid fa-circle-info',
    };
@endphp

<div {{ $attributes->merge(['class' => "alert $bsClass", 'role' => 'alert']) }}>
    <i class="{{ $icon ?? $defaultIcon }}"></i>
    <span>{{ $slot }}</span>
    @if($dismissible)
        <button type="button" class="close" onclick="this.closest('.alert').remove()">
            <i class="fa fa-xmark"></i>
        </button>
    @endif
</div>
