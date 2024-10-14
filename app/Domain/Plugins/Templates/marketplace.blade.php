@extends($layout)

@section('content')

    <x-global::content.pageheader :icon="'fa fa-puzzle-piece'">
        <h1>App Marketplace</h1>
    </x-global::content.pageheader>

    @displayNotification()

    <div class="maincontent">

        @include('plugins::includes.plugintabs',  ["currentUrl" => "marketplace"])

       <div class="maincontentinner">

           <div class="w-full"
                hx-get="{{ BASE_URL }}/hx/plugins/marketplaceplugins/getlist"
                hx-trigger="load"
                hx-target="#pluginList"
                hx-indicator=".htmx-indicator, .htmx-loaded-content"
                hx-swap="outerHTML"
           >
                @include('plugins::includes.pluginlist', [])
           </div>

       </div>

    </div>

@endsection
