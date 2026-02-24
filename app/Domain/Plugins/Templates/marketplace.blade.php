@extends($layout)

@section('content')

    <x-globals::layout.page-header :icon="'fa fa-puzzle-piece'">
        <h1>App Marketplace</h1>
    </x-globals::layout.page-header>

    @displayNotification()

    <div class="maincontent">

        @include('plugins::partials.plugintabs',  ["currentUrl" => "marketplace"])

       <div class="maincontentinner">

           <div class="tw:w-full"
                hx-get="{{ BASE_URL }}/hx/plugins/marketplaceplugins/getlist"
                hx-trigger="load"
                hx-target="#pluginList"
                hx-indicator=".htmx-indicator, .htmx-loaded-content"
                hx-swap="outerHTML"
           >
               <div id="pluginList" aria-live="polite">
                   <div class="htmx-indicator tw:ml-m tw:mr-m tw:pt-l" role="status">
                       <x-globals::feedback.skeleton type="plugincard" count="5" includeHeadline="false"/>
                   </div>
               </div>
           </div>

       </div>

    </div>

@endsection
