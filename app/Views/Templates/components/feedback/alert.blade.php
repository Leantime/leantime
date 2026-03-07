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
        'success' => 'check_circle',
        'warning' => 'warning',
        'error', 'danger' => 'cancel',
        default   => 'info',
    };
    $resolvedIcon = $icon ?? $defaultIcon;
    $iconIsFa = str_contains($resolvedIcon, 'fa-') || str_starts_with($resolvedIcon, 'fa ');
@endphp

<div {{ $attributes->merge(['class' => "alert $bsClass", 'role' => 'alert']) }}>
    @if($iconIsFa)
        <i class="{{ $resolvedIcon }}" aria-hidden="true"></i>
    @else
        <x-global::elements.icon :name="$resolvedIcon" />
    @endif
    <span>{{ $slot }}</span>
    @if($dismissible)
        <button type="button" class="close" onclick="this.closest('.alert').remove()" aria-label="{{ __('label.dismiss') }}">
            <x-global::elements.icon name="close" />
        </button>
    @endif
</div>
