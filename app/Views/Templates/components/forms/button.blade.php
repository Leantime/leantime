@props([
    'label-text' => '',
    'content-role' => 'primary',
    'scale' => '',
    'state' => '',
    'variant' => '',
    'tag' => 'button',
    'leadingVisual' => '',
    'trailingVisual' => ''
])

@aware([
    'join' => false,
])

@php
    $typeClass = ($contentRole == 'secondary' || $contentRole == 'tertiary') ? 'btn-outline' : ''.' btn-'.$contentRole;
    $sizeClass = $scale ? 'btn-'.$scale : '';
    $stateClass = $state ? 'btn-'.$state : ''
@endphp

<{{ $tag }} {{$attributes->merge(['class' => 'btn '.$typeClass.' '.$sizeClass. ' '.$stateClass])->class([
    'join-item' => $join,
    'mr-2' => ! $join,
    ]) }}>
    @if($leadingVisual)
        <div class="h-6 w-6">
            {{ $leadingVisual }}
        </div>
    @endif
    {{ $labelText }}
    {{ $slot }}
    @if($trailingVisual)
        <div class="h-6 w-6">
            {{ $trailingVisual }}
        </div>
    @endif
</{{ $tag }}>





<{{ $tag }} {{ $attributes->merge(['class' => $icon || $rightIcon ? ' gap-2' : ''])->class([
    'join-item' => $join,
    'mr-2' => ! $join,
    ]) }}>
@if($icon)
    {{ $icon }}
@endisset
{{ $label }}
{{ $slot }}
@if($rightIcon)
    <div class="">
        {{ $rightIcon }}
    </div>
@endisset
</{{ $tag }}>



