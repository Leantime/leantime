@props([
    'icon' => 'fa fa-home',
    'leadingVisual' => null,
    'headline' => null,
    'subtitle' => null,
])

@php
    $resolvedIcon = $leadingVisual ?? $icon;
@endphp

@dispatchEvent('beforePageHeaderOpen')

<div {{ $attributes->merge([ 'class' => 'pageheader' ]) }}>

    @dispatchEvent('afterPageHeaderOpen')

    <div class="pageicon"><span class="{{ $resolvedIcon }}"></span></div>

    <div class="pagetitle">
        @if($subtitle)
            <small>{{ $subtitle }}</small>
        @endif
        @if($headline)
            <h1>{{ $headline }}</h1>
        @else
            {{ $slot }}
        @endif
    </div>

    @isset($actions)
        <div class="pageheader-actions" style="margin-left: auto; display: flex; align-items: center; gap: 8px;">
            {{ $actions }}
        </div>
    @endisset

    @dispatchEvent('beforePageHeaderClose')

</div>

@dispatchEvent('afterPageHeaderClose')
