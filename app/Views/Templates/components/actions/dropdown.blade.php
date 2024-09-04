@props([
    'variant' => 'regular', // Dropdown variant: regular or card
    'contentRole' => 'primary', // Content role: primary, secondary, or tertiary
    'position' => 'bottom', // Dropdown position: left, right, top, bottom, inner, outer
    'label-text' => 'Dropdown', // Text for the dropdown button
    'card-label' => 'Card Title!', // Text for the card title
    'caption' => ''
])

@php
    // Determine the button class based on the content role
    $buttonClass = match($contentRole) {
        'secondary' => 'btn btn-secondary',
        'tertiary' => 'btn btn-accent',
        default => 'btn btn-primary',
    };

    // Determine the menu class based on the variant
    $menuClass = match($variant) {
        'card' => 'card card-compact bg-primary text-primary-content w-64 p-2 shadow', // Card variant class
        default => 'dropdown-content bg-base-100 rounded-box p-2 shadow w-52 z-50', // Default to regular menu
    };

    // Determine the dropdown position class
    $positionClass = match($position) {
        'left' => 'dropdown-left',
        'right' => 'dropdown-right',
        'top' => 'dropdown-top',
        'inner' => 'dropdown-end', // Use 'dropdown-end' to position inside
        'outer' => 'dropdown-start', // Use 'dropdown-start' to position outside
        default => 'dropdown-bottom', // Default to bottom position
    };
@endphp

<div {{ $attributes->merge(['class' => "dropdown $positionClass"]) }}>
    <!-- Dropdown Button -->
    <div tabindex="0" role="button" class="{{ $buttonClass }}">
        {{ $labelText }}
    </div>

    @if ($variant === 'card')
        <!-- Card Body for Card Variant -->
        <div tabindex="0" class="{{ $menuClass }}">
            <div class="card-body">
                <h3 class="card-title">{{ $cardLabel }}</h3>

            </div>
        </div>
    @else
        <!-- Regular Dropdown Menu -->
        <ul tabindex="0" class="{{ $menuClass }}">
            {{ $menu }}
        </ul>
    @endif
</div>





