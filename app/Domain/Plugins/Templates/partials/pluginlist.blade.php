@props([
    'plugins'
])

<div class="tw-w-full row">
    <div class="col-lg-12">
        <div class="row sortableTicketList">
            @if (count($plugins) == 0)
                <div class="tw-w-full htmx-loaded-content">
                    <x-global::undrawSvg image="undraw_empty_cart_co35.svg" headline="Out of Stock">
                       Due to a global bit shortage our plugins are currently out of stock. We are working hard to get more stock in as soon as possible.
                    </x-global::undrawSvg>
                </div>
            @else
                @each('plugins::partials.plugin', $plugins, 'plugin')
            @endif
        </div>
    </div>
</div>
