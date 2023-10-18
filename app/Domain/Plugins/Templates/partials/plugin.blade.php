@props([
    'plugin'
])

<div class="col-md-4">
    <div class="ticketBox fixed" style="padding-top:0px; overflow: hidden;">
        <div class="row">
            <div class="col-md-12 tw-p-none tw-overflow-hidden tw-mb-m tw-text-center tw-max-h-[150px]">
                <img src="{{ $plugin->getPluginImageData() }}" style="max-height:350px"/>

                @if($plugin instanceof \Leantime\Domain\Plugins\Models\MarketplacePlugin)
                    <div class="certififed label-default tw-absolute tw-top-[10px] tw-right-[10px] tw-text-primary tw-rounded-full tw-text-sm"

                         data-tippy-content="This plugin was downloaded from the Leantime Marketplace and is signature verified">
                        <i class="fa fa-certificate"></i>
                        Certified
                    </div>
                @endif
            </div>
        </div>
        @if (! empty($plugin->name))
            <div class="row">
                <div class="col-md-12">
                    <h5 class="subtitle">{{ $plugin->name }}<br /></h5>
                </div>
            </div>
        @endif
        <div class="row" style="margin-bottom:15px;">
            <div class="col tw-flex tw-flex-col tw-gap-4">
                @if (! empty($desc = $plugin->getCardDesc()))
                    <p>{{ $desc }}</p>
                @endif
                <x-global::inlineLinks :links="$plugin->getMetadataLinks()" />
            </div>
        </div>
        <div class="row tw-border-t tw-border-[var(--main-border-color)] tw-px-base">
            @include($plugin->getControlsView(), ["plugin" => $plugin])
        </div>
    </div>
</div>
