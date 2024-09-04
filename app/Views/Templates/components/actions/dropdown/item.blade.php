@props([
    'label-text' => '', // Text or HTML content for the dropdown item
    'variant' => 'link', // Variant of the dropdown item: 'link' or 'border'
])

@if ($variant === 'link')
    <li>
        <a {{ $attributes }}>
            {!! $labelText !!}
        </a>
    </li>
@elseif ($variant === 'border')
    <li class="border"></li>
@endif