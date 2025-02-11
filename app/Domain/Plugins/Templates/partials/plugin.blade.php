@props([
    'plugin'
])

<div class="col-md-4">
    <div class="ticketBox fixed" style="padding-top:0px; overflow: hidden;">
        <div class="row">
            <div class="col-md-12 tw-p-none tw-overflow-hidden tw-mb-m tw-max-h-[150px]">
                <img src="{{ $plugin->getPluginImageData() }}" width="100" height="100" class="tw-rounded tw-ml-base tw-mt-base"/>

                @if($plugin instanceof \Leantime\Domain\Plugins\Models\MarketplacePlugin)
                    <div
                        class="certififed label-default tw-absolute tw-top-[10px] tw-right-[10px] tw-text-primary tw-rounded-full tw-text-sm"
                        data-tippy-content="{{ __('marketplace.certified_tooltip') }}"
                    >
                        <i class="fa fa-certificate"></i>
                        Certified
                    </div>
                @endif
            </div>
        </div>
        @if (! empty($plugin->name))
            <div class="row">
                <div class="col-md-12">
                    <h5 class="subtitle">{!! $plugin->name !!} {{ $plugin->version ? "(v".$plugin->version.")" : "" }}<br /></h5>
                </div>
            </div>
        @endif
        <div class="row tw-mb-base">
            <div class="col tw-flex tw-flex-col tw-gap-base">
                <x-global::inlineLinks :links="$plugin->getMetadataLinks()" />
                @if (! empty($desc = $plugin->getCardDesc()))
                    <p>{{ $desc }}</p>
                @endif

            </div>
        </div>
        <div class="row tw-border-t tw-border-[var(--main-border-color)] tw-px-base">
            @include($plugin->getControlsView(), ["plugin" => $plugin])
        </div>
    </div>
</div>
