@extends($layout)

@section('content')

    <x-global::pageheader :icon="'fa fa-puzzle-piece'">
        <h1>App Marketplace</h1>
    </x-global::pageheader>

    @displayNotification()

    <div class="maincontent">

        @include('plugins::partials.plugintabs',  ["currentUrl" => "marketplace"])

       <div class="maincontentinner">

           <div class="tw-p-4 tw-flex tw-flex-wrap tw-gap-4">

               @include('plugins::partials.pluginlist', [])

           </div>

       </div>

    </div>

@endsection
