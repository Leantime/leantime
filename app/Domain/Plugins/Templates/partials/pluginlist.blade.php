
<div id="pluginList" class="tw-w-full" hx-get="{{ BASE_URL }}/hx/plugins/marketplaceplugins/getlist" hx-trigger="load"
     hx-target="#mainProjectSelector"
     hx-indicator=".htmx-indicator, .htmx-loaded-content"
     hx-swap="outerHTML">

    @if (empty($plugins))

        <div class="tw-w-full htmx-loaded-content">

            <x-global::undrawSvg image="undraw_empty_cart_co35.svg" headline="Out of Stock">
               Due to a global bit shortage our plugins are currently out of stock. We are working hard to get more stock in as soon as possible.
            </x-global::undrawSvg>


        </div>

    @else

        @each('plugins::marketplace.plugin', $plugins['data'], 'plugin')

    @endif

</div>

<div class="htmx-indicator tw-ml-m tw-mr-m tw-pt-l">
    <x-global::loadingText type="plugincard" count="5" includeHeadline="false"/>
</div>
