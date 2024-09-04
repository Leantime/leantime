@props([
    'link' => '#', // Default URL for the dropdown item link
    'label-text' => '', // Text or HTML content for the dropdown item
    'border' => false, // Whether to show a border after the item
])

<li>
    <a href="{{ $link }}">
        {!! $labelText !!}
    </a>
</li>

@if ($border)
    <li class="border"></li>
@endif
