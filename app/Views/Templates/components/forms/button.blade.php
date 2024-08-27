@props([
    'label' => '',
    'btnType' => 'primary',
    'size' => '',
    'btnState' => '',
    'tag' => 'button'
])

@php
    $typeClass = ($btnType == 'secondary' || $btnType == 'tertiary') ? 'btn-outline' : ''.' btn-'.$btnType;
    $sizeClass = $size ? 'btn-'.$size : '';
    $stateClass = $btnState ? 'btn-'.$btnState : ''
@endphp

<{{ $tag }} {{$attributes->merge(['class' => 'btn '.$typeClass.' '.$sizeClass. ' '.$stateClass])}}>
    {{ $label }}
</{{ $tag }}>



