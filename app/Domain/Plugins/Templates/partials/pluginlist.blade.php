@props([
    'plugins'
])

<div class="tw:w-full">
    @if (count($plugins) == 0)
        <div class="tw:w-full htmx-loaded-content">
            <x-globals::undrawSvg image="undraw_empty_cart_co35.svg" headline="Out of Stock">
               Due to a global bit shortage our plugins are currently out of stock. We are working hard to get more stock in as soon as possible.
            </x-globals::undrawSvg>
        </div>
    @else
        @foreach($plugins as $key => $pluginCategory)
            @if($key !== 'plugins')
                <div class="tw:mb-8">
                    <h5 class="subtitle tw:mb-1">
                        <strong>{!! $pluginCategory['name'] !!}</strong>
                    </h5>
                    <p class="tw:text-[color:var(--secondary-font-color)] tw:mb-4">
                        {!! $pluginCategory['description'] !!}
                    </p>

                    @each('plugins::partials.plugin', $pluginCategory['plugins'], 'plugin')
                </div>
            @endif
        @endforeach
    @endif
</div>
