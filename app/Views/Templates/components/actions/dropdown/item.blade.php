@props([
    'variant' => 'link', // Variant of the dropdown item: 'link', 'border', 'nav-header', or 'nav-header-border'
])

@if ($variant === 'link')
    <li>
        <a
            {{ $attributes->merge(['class' => 'text-neutral-content rounded-full link:text-neutral-content visited:text-neutral-content']) }}>
            {!! $slot !!}
        </a>
    </li>
@elseif ($variant === 'header')
    <li>
        <h2 class="menu-title">{!! $slot !!}</h2>
    </li>
@elseif ($variant === 'header-border')
    <li class="border-t border-neutral">
        <h2 class="menu-title">{!! $slot !!}</h2>
    </li>
@elseif ($variant === 'border')
    <li class="border-t border-neutral opacity-100"></li>
@else
    <li>
        {!! $slot !!}
    </li>
@endif
