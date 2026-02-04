@props([
    'contentRole' => '', //primary, secondary, tertiary, accent, ghost
    'scale' => 'lg', //xs, sm, md, lg
    'state' => '', //default, info, warning, danger, success
    'outline' => false,
    'leadingVisual' => ''
])

@php
    switch($contentRole){
        case 'primary':
            $typeClass = 'badge-primary';
            break;
        case 'secondary':
            $typeClass = 'badge-secondary';
            break;
        case 'tertiary':
        case 'accent':
            $typeClass = 'badge-accent';
            break;
        case 'ghost':
            $typeClass = 'badge-ghost';
            break;
        default:
            $typeClass = '';
    }

    $sizeClass = 'badge-'.$scale.' ';

    $outlineClass = $outline ? 'badge-outline border-color-default' : '';

    $stateClass = '';
    if(!empty($state)) {
        $mappedStatus = format($state)->getStatusClass();

        if($outline === true) {
            $stateClass .= "text-".$mappedStatus ." ";
        }else{
            $stateClass = "border-". $mappedStatus ." bg-".$mappedStatus."  text-".$mappedStatus."-content";
        }

    }

@endphp

<div {{ $attributes->merge(['class' => 'badge text-nowrap '.$typeClass.' '.$sizeClass.' '.$outlineClass. ' '.$stateClass]) }}>
    @if($leadingVisual)
        <span class="">
            {{ $leadingVisual }}
        </span>
    @endif
    {!! $slot !!}
</div>
