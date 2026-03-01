@props([
    'leadingVisual' => null,
    'trailingVisual' => null,
    'href' => 'javascript:void(0)',
    'state' => null,         // active|danger
    'header' => false,
    'divider' => false,
])

@if($divider)
    <li class="nav-header border"></li>
@elseif($header)
    <li class="nav-header">{{ $slot }}</li>
@else
    @php
        $stateClass = match($state) {
            'active' => 'active',
            'danger' => 'tw:text-error',
            default  => '',
        };
    @endphp
    <li>
        <a href="{{ $href }}" {{ $attributes->merge(['class' => $stateClass]) }}>
            @if($leadingVisual)
                <x-global::elements.icon :name="$leadingVisual" style="margin-right:6px;" />
            @endif
            {{ $slot }}
            @if($trailingVisual)
                <x-global::elements.icon :name="$trailingVisual" style="margin-left:auto;" />
            @endif
        </a>
    </li>
@endif
