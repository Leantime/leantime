@props([
    'icon' => 'home',
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

    <div class="pageicon">
        @if(str_contains($resolvedIcon, 'fa-') || str_starts_with($resolvedIcon, 'fa '))
            <span class="{{ $resolvedIcon }}"></span>
        @else
            <x-global::elements.icon :name="$resolvedIcon" />
        @endif
    </div>

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
