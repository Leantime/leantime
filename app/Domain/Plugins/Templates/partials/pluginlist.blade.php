
<div id="pluginList" hx-get="{{ BASE_URL }}/hx/plugins/marketplaceplugins/getlist" hx-trigger="load"
     hx-target="#mainProjectSelector"
     hx-indicator=".htmx-indicator, .htmx-loaded-content"
     hx-swap="outerHTML">

    @if (empty($plugins))

        <div class="tw-w-full tw-text-center htmx-loaded-content">
            <h2 class="tw-text-2xl">No plugins found</h2>
        </div>

    @else

        @each('plugins::marketplace.plugin', $plugins['data'], 'plugin')

    @endif

</div>

<div class="htmx-indicator tw-ml-m tw-mr-m tw-pt-l">
    <x-global::loadingText type="plugincard" count="5" includeHeadline="false"/>
</div>
