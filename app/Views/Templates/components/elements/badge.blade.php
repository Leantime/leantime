@props([
    'contentRole' => '', //primary, secondary, tertiary, accent, ghost
    'scale' => '', //xs, s, m. l
    'outline' => false,
    'leadingVisual' => ''
])

@php
    switch($contentRole){
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
            $typeClass = 'badge-primary';
    }

    if ($scale === 'xs') {
        $sizeClass = 'badge-xs';
    } elseif ($scale === 's') {
        $sizeClass = 'badge-sm';
    } elseif ($scale === 'l') {
        $sizeClass = 'badge-lg';
    } else {
        $sizeClass = '';
    }

    $outlineClass = $outline ? 'badge-outline' : '';
@endphp


<div {{ $attributes->merge(['class' => 'badge '.$typeClass.' '.$sizeClass.' '.$outlineClass]) }}>
    @if($leadingVisual)
        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            {{ $leadingVisual }}>
        </span>
    @endif
    {{ $slot }}
</div>
