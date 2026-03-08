@php
    $clients = $tpl->get('clients');
    $currentClient = $tpl->get('currentClient');
    $currentClientName = $tpl->get('currentClientName');
@endphp

@dispatchEvent('beforePageHeaderOpen')

<x-globals::layout.page-header icon="work" headline="{{ __('headlines.my_projects') }}">
    @dispatchEvent('afterPageHeaderOpen')
    @dispatchEvent('beforePageHeaderClose')
</x-globals::layout.page-header>

@dispatchEvent('afterPageHeaderClose')
