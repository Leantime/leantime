@props([
    'plugins'
])

<div class="tw:w-full">
    <div>
        <div class="tw:grid tw:grid-cols-3 tw:gap-6 sortableTicketList">
            @if (count($plugins) == 0)
                <div class="tw:w-full htmx-loaded-content">
                    <x-global::undrawSvg image="undraw_empty_cart_co35.svg" headline="Out of Stock">
                       Due to a global bit shortage our plugins are currently out of stock. We are working hard to get more stock in as soon as possible.
                    </x-global::undrawSvg>
                </div>
            @else

                @foreach($plugins as $key => $pluginCategory)
                    @if($key !== 'plugins')
                        <div class="tw:col-span-3">

                            <h1 style="border-bottom:1px solid rgba(0, 0, 0, 0.3); margin-bottom:10px; padding-bottom:10px;"><strong>{!! $pluginCategory['name'] !!}</strong></h1>
                            <p style="">{!! $pluginCategory['description'] !!}</p> <br />

                        </div>
                        @each('plugins::partials.plugin', $pluginCategory['plugins'], 'plugin')
                        <div class="tw:col-span-3" style="margin-bottom:20px;">

                        </div>
                    @endif
                @endforeach
            @endif
        </div>
    </div>
</div>
