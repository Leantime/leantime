@dispatchEvent('beforePageHeaderOpen')

<div {{ $attributes->merge([ 'class' => 'pageheader' ]) }}>

    @dispatchEvent('afterPageHeaderOpen')

    <div class="pageicon"><span class="{{ $icon ?? 'fa fa-home'}}"></span></div>

    <div class="pagetitle">
        {{ $slot }}
    </div>

    @dispatchEvent('beforePageHeaderClose')

</div>

@dispatchEvent('afterPageHeaderClose')
