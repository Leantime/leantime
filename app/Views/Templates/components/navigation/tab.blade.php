@props([
    'label' => '',
    'href' => null,
    'target' => null,
    'active' => false,
])

@php
    $link = $href ?? ($target ? '#' . $target : '#');
    $isPanel = $target !== null;
@endphp

<li @class(['active' => $active])>
    <a href="{{ $link }}"
       role="tab"
       @if($active) aria-selected="true" @endif
       @if($isPanel) onclick="event.stopPropagation()" @endif
       {{ $attributes }}
    >{{ $label ?: $slot }}</a>
</li>
