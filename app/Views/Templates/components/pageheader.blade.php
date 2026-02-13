@props([
    'icon' => 'fa fa-home',
    'headline' => null,
])

@dispatchEvent('beforePageHeaderOpen')

<div {{ $attributes->merge([ 'class' => 'pageheader' ]) }}>

    @dispatchEvent('afterPageHeaderOpen')

    <div class="pageicon"><span class="{{ $icon }}"></span></div>

    <div class="pagetitle">
        @if($headline)
            <h1>{{ $headline }}</h1>
        @else
            {{ $slot }}
        @endif
    </div>

    @dispatchEvent('beforePageHeaderClose')

</div>

@dispatchEvent('afterPageHeaderClose')
