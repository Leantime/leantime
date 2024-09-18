@props([
    'trailingVisual'
])

@if($trailingVisual)
    <span class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
        {{ $trailingVisual }}
    </span>
@endif
