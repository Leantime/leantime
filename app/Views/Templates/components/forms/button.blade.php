@props([
    'labelText' => '',
    'contentRole' => 'primary', //default, primary, secondary, tertiary (ghost), accent, link
    'state' => '', //default, info, warning, danger, success
    'scale' => '',

    'variant' => '', //chip, input
    'tag' => 'button',
    'icon' => '',
    'rightIcon' => '',
    'leadingVisual' => '',
    'trailingVisual' => '',
    'shape' => ''
])

@aware([
    'join' => false,
])

@php
    $variantClasses = "";
    if($variant == "chip") {
        $variantClasses = ":hover bg-neutral";
    }else if($variant == "input") {
        $variantClasses = "w-full input justify-start";
    }

    // btn-outline border-primary text-primary
    switch($contentRole){
        case 'secondary':
            $typeClass = 'btn-secondary hover:bg-secondary/80';
            break;
        case 'tertiary':
        case 'ghost':
            $typeClass = 'btn-ghost text-base-content hover:bg-ghost';
            break;
        case 'link':
            $typeClass = 'btn-link text-primary hover:bg-primary/20 ';
            break;
        default:
            $typeClass = 'btn-primary border-primary hover:bg-primary/80 ';
    }
    $sizeClass = $scale ? 'btn-'.$scale : 'btn-sm';
    $stateClass = $state ? 'btn-'.$state : '';
    $shapeClass = $shape ? 'btn-'.$shape : '';
@endphp

<{{ $tag }} role="button" {{ $attributes->merge(['class' => 'btn active:shadow-inner '. $typeClass.' '.$sizeClass. ' '.$stateClass. ' '.$shapeClass . ' '.$variantClasses])->class([
    'join-item' => $join,
    'mr-1' => ! $join,
    ]) }}>
    @if($leadingVisual)
        <div class="h-6 w-6">
            {{ $leadingVisual }}
        </div>
    @endif
    {!! $labelText !!}
    {{ $slot }}
    @if($trailingVisual)
        <div class="h-6 w-6">
            {{ $trailingVisual }}
        </div>
    @endif
</{{ $tag }}>



