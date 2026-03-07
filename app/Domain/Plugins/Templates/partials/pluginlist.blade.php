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
                <div style="margin-bottom: 30px;">
                    <h5 class="subtitle" style="margin-bottom: 5px;">
                        <strong>{!! $pluginCategory['name'] !!}</strong>
                    </h5>
                    <p style="font-size: var(--font-size-s); color: var(--secondary-font-color); margin-bottom: 15px;">
                        {!! $pluginCategory['description'] !!}
                    </p>

                    @each('plugins::partials.plugin', $pluginCategory['plugins'], 'plugin')
                </div>
            @endif
        @endforeach
    @endif
</div>
