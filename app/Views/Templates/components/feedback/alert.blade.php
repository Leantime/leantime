@props([
    'type' => 'info',
    'state' => null,
    'dismissible' => false,
    'icon' => null,
])

@php
    $resolvedState = $state ?? $type;

    $bsClass = match($resolvedState) {
        'success' => 'alert-success',
        'warning' => 'alert-warning',
        'error', 'danger' => 'alert-danger',
        default   => 'alert-info',
    };
    $defaultIcon = match($resolvedState) {
        'success' => 'fa-solid fa-circle-check',
        'warning' => 'fa-solid fa-triangle-exclamation',
        'error', 'danger' => 'fa-solid fa-circle-xmark',
        default   => 'fa-solid fa-circle-info',
    };
@endphp

<div {{ $attributes->merge(['class' => "alert $bsClass", 'role' => 'alert']) }}>
    <i class="{{ $icon ?? $defaultIcon }}" aria-hidden="true"></i>
    <span>{{ $slot }}</span>
    @if($dismissible)
        <button type="button" class="close" onclick="this.closest('.alert').remove()" aria-label="{{ __('label.dismiss') }}">
            <i class="fa fa-xmark" aria-hidden="true"></i>
        </button>
    @endif
</div>
