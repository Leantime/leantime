@extends($layout)

@section('content')

    <x-global::pageheader :icon="'fa fa-puzzle-piece'">
        <h1>My Apps</h1>
    </x-global::pageheader>

    @displayNotification()

    <div class="maincontent">

        @include('plugins::partials.plugintabs',  ["currentUrl" => "installed"])

        <div class="maincontentinner">

            <div class="row">
                <div class="col-lg-12">
                    <h5 class="subtitle">
                        {{ __("text.installed_plugins") }}
                    </h5>
                    <div class="row sortableTicketList">
                        @foreach($tpl->get("installedPlugins") as $installedPlugins)
                               @include('plugins::marketplace.plugin', ["plugin" => $installedPlugins])
                        @endforeach
                        @if ($tpl->get("installedPlugins") === false || count($tpl->get("installedPlugins")) == 0)
                                {{ __("text.no_plugins_installed") }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
