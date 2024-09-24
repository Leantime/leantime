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





{{-- @props([
    'asLink' => false,
    'color' => match ($color ?? null) {
        'yellow' => ['yellow-500', 'bg-yellow-500'],
        'red' => ['red-500', 'bg-red-500'],
        'blue' => ['blue-500', 'bg-blue-500'],
        'green' => ['green', 'bg-green'],
        'primary' => ['primary', 'bg-primary'],
        'gray', default => ['gray-900', 'bg-gray-900'],
    },
])

@if ($asLink)
<a
@else
<span
@endif
{{ $attributes->merge([
    'class' => 'mix-blend-difference px-2.5 py-0.5 rounded' . ($asLink ? 'text-white' . $color[1] : $color[0] . 'bg-gray-300'),
] + ($asLink ? ['href' => $url ?? '#'] : [])) }}>
    {{ $slot }}
@if ($asLink)
</a>
@else
</span>
@endif --}}
