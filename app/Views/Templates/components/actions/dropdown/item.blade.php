@props([
    'variant' => 'link', // Variant of the dropdown item: 'link' or 'border'
])

@if ($variant === 'link')
    <li>
        <a {{ $attributes->merge(['class' => 'text-neutral-content link:text-neutral-content visited:text-neutral-content']) }}>
            {!! $slot !!}
        </a>
    </li>
@elseif ($variant === 'border')
    <li class="border"></li>
@endif
