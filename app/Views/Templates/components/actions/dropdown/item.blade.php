@props([
    'variant' => 'link', // Variant of the dropdown item: 'link', 'border', 'nav-header', or 'nav-header-border'
])

@if ($variant === 'link')
    <li>
        <a {{ $attributes->merge(['class' => 'text-neutral-content rounded-full link:text-neutral-content visited:text-neutral-content']) }}>
            {!! $slot !!}
        </a>
    </li>

@elseif ($variant === 'header')
    <li class="nav-header">
        {!! $slot !!}
    </li>

@elseif ($variant === 'header-border')
    <li class="nav-header border">
        {!! $slot !!}
    </li>

@elseif ($variant === 'border')
    <li class="border"></li>
@endif
