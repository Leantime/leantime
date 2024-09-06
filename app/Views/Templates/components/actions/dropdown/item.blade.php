@props([
    'variant' => 'link', // Variant of the dropdown item: 'link' or 'border'
])

@if ($variant === 'link')
    <li>
        <a {{ $attributes }}>
            {!! $slot !!}
        </a>
    </li>
@elseif ($variant === 'border')
    <li class="border"></li>
@endif
