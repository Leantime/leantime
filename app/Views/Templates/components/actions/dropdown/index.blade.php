<!-- resources/views/components/actions/dropdown/index.blade.php -->

@props([
    'variant' => 'regular', // Dropdown variant: regular or card
    'contentRole' => 'primary', // Content role: primary, secondary, accent, ghost, link
    'position' => 'bottom', // Dropdown position: top, left, bottom, right
    'align' => 'start', // Dropdown alignment: start or end
    'labelText' => 'Dropdown', // Text for the dropdown button
    'cardLabel' => 'Card Title!', // Text for the card title
])

@php
    // Determine the button class based on the content role
    $buttonClass = match($contentRole) {
        'primary' => 'btn btn-primary',
        'secondary' => 'btn btn-secondary',
        'accent' => 'btn btn-accent',
        'ghost' => 'btn btn-tertiary',
        'link' => 'btn btn-link',
        default => 'btn', // Default to base button class
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
        'bottom' => 'dropdown-bottom',
        default => '', // No default class for invalid positions
    };

    // Determine the alignment class
    $alignmentClass = $align === 'end' ? 'dropdown-end' : ''; // Adds 'dropdown-end' class if 'end' is selected
@endphp

<div {{ $attributes->merge(['class' => "dropdown $positionClass $alignmentClass"]) }}>
    <!-- Dropdown Button -->
    <div tabindex="0" role="button" class="{{ $buttonClass }}">
        {!! $labelText !!}
    </div>

    @if ($variant === 'card')
        <!-- Card Body for Card Variant -->
        <div tabindex="0" class="{{ $menuClass }}">
            <div class="card-body">
                <h3 class="card-title">{{ $cardLabel }}</h3>
                {!! $cardContent ?? '' !!}
            </div>
        </div>
    @else
        <!-- Regular Dropdown Menu -->
        <ul tabindex="0" class="{{ $menuClass }}">
            {{ $menu }}
        </ul>
    @endif
</div>
