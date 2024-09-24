@props([
    'size' => '', //lg, md, sm. xs
    'type' => '', //neutral, primary, secondary, accent, ghost, info, success, warning, error
    'outlineState' => false,
    'leadingVisual' => ''
])

@php
    $sizeClass = $size ? 'badge-'.$size : '';
    $typeClass = $type ? 'badge-'.$type : '';
    $outlineClass = $outlineState ? 'badge-outline' : '';
@endphp


<div {{ $attributes->merge(['class' => 'badge mt-1 '.$typeClass.' '.$sizeClass.' '.$outlineClass]) }}>
    @if($leadingVisual)
        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            {{ $leadingVisual }}>
        </span>
    @endif
    {{ $slot }}
</div>
