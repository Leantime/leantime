@php
    $clients = $tpl->get('clients');
    $currentClient = $tpl->get('currentClient');
    $currentClientName = $tpl->get('currentClientName');
@endphp

@dispatchEvent('beforePageHeaderOpen')

<div class="pageheader">
    @dispatchEvent('afterPageHeaderOpen')

    <div class="pageicon">
        <span class="fa fa-briefcase"></span>
    </div>
    <div class="pagetitle">
        <h1>{{ __('headlines.my_projects') }}</h1>
    </div>

    @dispatchEvent('beforePageHeaderClose')
</div>

@dispatchEvent('afterPageHeaderClose')
