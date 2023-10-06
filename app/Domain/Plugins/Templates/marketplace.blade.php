@extends($layout)

@section('content')

<x-global::pageheader :icon="'fa fa-plug'">
    <h1>Plugin Marketplace</h1>
</x-global::pageheader>

<div class="maincontent">

    <div class="maincontentinner">

        <div class="tw-p-4 tw-flex tw-flex-wrap tw-gap-4">

            @if (empty($plugins))

                <div class="tw-w-full tw-text-center">
                    <h2 class="tw-text-2xl">No plugins found</h2>
                </div>

            @else

                @each('plugins::marketplace.plugin', $plugins['data'], 'plugin')

            @endif

        </div>

    </div>

</div>

@endsection
