@props([
    'statTitle' => '',
    'statValue' => '',
    'statDesc' => '',
    'trailingVisual' => '',
    'contentRole' => 'primary',
])

@php
    $bgClass = $contentRole ? 'bg-'.$contentRole.' text-'.$contentRole.'-content' : '';
    $textContentClass = $contentRole ? 'text-'.$contentRole.'-content' : '';
@endphp

<div {{$attributes->merge(['class' => 'stat '.$bgClass.' my-3'])}} >
    @if ($trailingVisual)
            <div class="stat-figure" >
            <i class="{{ $trailingVisual }}"></i>
        </div>
    @endif

    @isset($statTitle)
        <div {{$attributes->merge(['class' => 'stat-title '.$textContentClass])}}>{{ $statTitle }}</div>
    @endisset

    @isset($statValue)
        <div class="stat-value font-normal" >{{ $statValue }}</div>
    @endisset

    @isset($statDesc)
        <div {{$attributes->merge(['class' => 'stat-desc '.$textContentClass])}}>{{ $statDesc }}</div>
    @endisset
</div>
