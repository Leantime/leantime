@props([
    'statTitle' => '',
    'statValue' => '',
    'statDesc' => '',
    'trailingVisual' => '',
    'statBg' => '',
])

@php
    $bgClass = $statBg ? 'bg-'.$statBg.' text-'.$statBg.'-content' : '';
    $textContentClass = $statBg ? 'text-'.$statBg.'-content' : '';
@endphp

<div {{$attributes->merge(['class' => 'stat '.$bgClass])}} >
    @if ($trailingVisual)
            <div class="stat-figure" >
            <i class="{{ $trailingVisual }}"></i>
        </div>
    @endif

    @if($statTitle)
        <div {{$attributes->merge(['class' => 'stat-title '.$textContentClass])}}>{{ $statTitle }}</div>
    @endif

    @if($statValue)
        <div class="stat-value" >{{ $statValue }}</div>
    @endif

    @if($statDesc)
        <div {{$attributes->merge(['class' => 'stat-desc '.$textContentClass])}}>{{ $statDesc }}</div>
    @endif
</div>
