@extends($layout)

@section('content')

    <x-global::pageheader :icon="'fa fa-puzzle-piece'">
        <h1>App Marketplace</h1>
    </x-global::pageheader>

    @displayNotification()

    <div class="maincontent">

        @include('plugins::partials.plugintabs',  ["currentUrl" => "marketplace"])

       <div class="maincontentinner">

           <div class="tw-w-full"
                hx-get="{{ BASE_URL }}/hx/plugins/marketplaceplugins/getlist"
                hx-trigger="load"
                hx-target="#pluginList"
                hx-indicator=".htmx-indicator, .htmx-loaded-content"
                hx-swap="outerHTML"
           >
                @include('plugins::partials.pluginlist', [])
           </div>

       </div>

    </div>

@endsection
