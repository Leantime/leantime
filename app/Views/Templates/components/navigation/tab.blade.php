@props([
    'label' => '',
    'href' => null,
    'target' => null,
    'active' => false,
    'icon' => null,
])

@php
    $link = $href ?? ($target ? '#' . $target : '#');
    $isPanel = $target !== null;
@endphp

<li role="presentation" @class(['active' => $active])>
    <a href="{{ $link }}"
       role="tab"
       @if($active) aria-selected="true" @endif
       @if($isPanel) onclick="event.stopPropagation()" @endif
       {{ $attributes }}
    >@if($icon)<x-globals::elements.icon :name="$icon" /> @endif{{ $label ?: $slot }}</a>
</li>
