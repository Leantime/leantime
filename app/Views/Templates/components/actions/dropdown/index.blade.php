@props([
    'variant' => 'regular', // Dropdown variant: regular or card

    'contentRole' => 'primary', // Content role: primary, secondary, accent, ghost, link
    'state' => '',

    'position' => 'bottom', // Dropdown position: top, left, bottom, right
    'align' => 'start', // Dropdown alignment: start or end
    'labelText' => 'Dropdown', // Text for the dropdown button
    'cardLabel' => 'Card Title!', // Text for the card title
    'buttonShape' => '',
    'buttonVariant' => '',
    'scale' => '',
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
    $menuClassBase = "dropdown-content rounded-element bg-base-100 p-2 shadow w-60 z-50";
    // Determine the menu class based on the variant
    $menuClass = match($variant) {
        'card' => 'card card-compact '.$menuClassBase, // Card variant class
        default => 'menu '.$menuClassBase, // Default to regular menu
    };

    $cardClassBase = "dropdown-content rounded-element bg-base-100 p-2 shadow w-fit z-50";
    // Determine the menu class based on the variant
    $cardClass = match($variant) {
        'card' => 'card card-compact '.$cardClassBase, // Card variant class
        default => 'menu '.$cardClassBase, // Default to regular menu
    };

    // Determine the dropdown position class
    $positionClass = match($position) {
        'left' => 'dropdown-left',
        'right' => 'dropdown-right',
        'top' => 'dropdown-top',
        'bottom' => 'dropdown-bottom',
        'end' => 'dropdown-end',
        'start' => 'dropdown-start',
        default => '', // No default class for invalid positions
    };

    // Determine the alignment class
    $alignmentClass = $align === 'end' ? 'dropdown-end' : ''; // Adds 'dropdown-end' class if 'end' is selected
@endphp

<div {{ $attributes->merge(['class' => "dropdown $positionClass $alignmentClass"]) }}>


    <!-- Dropdown Button -->
    <x-global::forms.button tabindex="0" tag="div" :content-role="$contentRole" :shape="$buttonShape" :variant="$buttonVariant" :scale="$scale">
        {!! $labelText !!}
    </x-global::forms.button>

    @if ($variant === 'card')
        <!-- Card Body for Card Variant -->
        <div tabindex="0" class="{{ $cardClass }}">
            <div class="card-body">
                @if($cardLabel)
                    <h3 class="card-title">{{ $cardLabel }}</h3>
                @endif

                {!! $cardContent ?? '' !!}
            </div>
        </div>
    @else
        <!-- Regular Dropdown Menu -->
        <ul tabindex="0" class="{{ $menuClass }}">
            {!! $menu !!}
        </ul>
    @endif
</div>
