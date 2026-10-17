@props([
    'plugins'
])

<div class="tw-w-full row">
    <div class="col-lg-12">
        <div class="row sortableTicketList">
            @if (count($plugins) == 0)
                <div class="tw-w-full htmx-loaded-content">
                    <x-global::elements.empty-state image="undraw_no_data_re_kwbl.svg" titleAs="h3" :title="__('headlines.plugins_out_of_stock')"
                        :description="__('text.plugins_out_of_stock')" />
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
