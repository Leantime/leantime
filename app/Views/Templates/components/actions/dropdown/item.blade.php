@props([
    'variant' => 'link', // Variant of the dropdown item: 'link', 'border', 'nav-header', or 'nav-header-border'
])

@if ($variant === 'link')
    <li>
        <a {{ $attributes }}>
            {!! $slot !!}
        </a>
    </li>

@elseif ($variant === 'nav-header')
    <li class="nav-header">
        {!! $slot !!}
    </li>

@elseif ($variant === 'nav-header-border')
    <li class="nav-header border">
        {!! $slot !!}
    </li>

@elseif ($variant === 'border')
    <li class="border"></li>
@endif
