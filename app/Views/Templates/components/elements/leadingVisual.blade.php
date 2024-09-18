@props([
    'leadingVisual'
])

@if($leadingVisual)
    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
        {{ $leadingVisual }}
    </span>
@endif
