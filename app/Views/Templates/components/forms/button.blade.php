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
    $variantPrefix = "btn";
    $stateClass = '';

    if($variant == "chip") {
        $variantClasses = "badge badge-lg";
        $variantPrefix = "badge";
        $stateClass = $state ? "text-".$state : '';
    }else if($variant == "input") {
        $variantClasses = "w-full input justify-start";
        $variantPrefix = "btn";
        $stateClass = $state ? $variantPrefix."-".$state : '';
    }else{
        $stateClass = $state ? $variantPrefix."-".$state : '';
    }

    // btn-outline border-primary text-primary
    switch($contentRole){
        case 'secondary':
            $typeClass = $variantPrefix.'-secondary hover:bg-secondary/80';
            break;
        case 'tertiary':
        case 'ghost':
            $typeClass = $variantPrefix.'-ghost text-base-content hover:btn-ghost';
            break;
        case 'link':
            $typeClass = $variantPrefix.'-link hover:bg-secondary/80 ';
            break;
        default:
            $typeClass = $variantPrefix.'-primary border-primary hover:bg-primary/80 ';
    }
    $sizeClass = $scale ? $variantPrefix."-".$scale : $variantPrefix.'-sm';

    $shapeClass = $shape ? $variantPrefix."-".$shape : '';
@endphp

<{{ $tag }} role="button" {{ $attributes->merge(['class' => $variantPrefix.' active:shadow-inner '. $typeClass.' '.$sizeClass. ' '.$stateClass. ' '.$shapeClass . ' '.$variantClasses])->class([
    'join-item' => $join,
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



