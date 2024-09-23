@props([
    'labelText' => '',
    'contentRole' => 'primary',
    'scale' => '',
    'state' => '',
    'variant' => '',
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

    switch($contentRole){
        case 'secondary':
            $typeClass = 'btn-secondary border-primary text-primary hover:bg-primary/20 ';
            break;
        case 'tertiary':
        case 'ghost':
            $typeClass = 'btn-ghost hover:bg-primary/20 text-base-content';
            break;
        case 'link':
            $typeClass = 'btn-link text-primary hover:bg-primary/20 ';
            break;
        default:
            $typeClass = 'btn-primary border-primary hover:bg-primary ';
    }
    $sizeClass = $scale ? 'btn-'.$scale : '';
    $stateClass = $state ? 'btn-'.$state : '';
    $shapeClass = $shape ? 'btn-'.$shape : '';
@endphp

<{{ $tag }} role="button" {{$attributes->merge(['class' => 'btn btn-sm active:shadow-inner '. $typeClass.' '.$sizeClass. ' '.$stateClass. ' '.$shapeClass])->class([
    'join-item' => $join,
    'mr-1' => ! $join,
    ]) }}>
    @if($leadingVisual)
        <div class="h-6 w-6">
            {{ $leadingVisual }}
        </div>
    @endif
    {{ $labelText }}
    {{ $slot }}
    @if($trailingVisual)
        <div class="h-6 w-6">
            {{ $trailingVisual }}
        </div>
    @endif
</{{ $tag }}>



