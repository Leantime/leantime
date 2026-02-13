@props([
    'plugins'
])

<div class="tw:w-full row">
    <div class="col-lg-12">
        <div class="row sortableTicketList">
            @if (count($plugins) == 0)
                <div class="tw:w-full htmx-loaded-content">
                    <x-global::undrawSvg image="undraw_empty_cart_co35.svg" headline="Out of Stock">
                       Due to a global bit shortage our plugins are currently out of stock. We are working hard to get more stock in as soon as possible.
                    </x-global::undrawSvg>
                </div>
            @else

                @foreach($plugins as $key => $pluginCategory)
                    @if($key !== 'plugins')
                        <div class="col-md-12">

                            <h1 style="border-bottom:1px solid rgba(0, 0, 0, 0.3); margin-bottom:10px; padding-bottom:10px;"><strong>{!! $pluginCategory['name'] !!}</strong></h1>
                            <p style="">{!! $pluginCategory['description'] !!}</p> <br />

                        </div>
                        @each('plugins::partials.plugin', $pluginCategory['plugins'], 'plugin')
                        <div class="col-md-12" style="margin-bottom:20px;">

                        </div>
                    @endif
                @endforeach
            @endif
        </div>
    </div>
</div>
