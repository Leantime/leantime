@props([
    'plugin'
])

<div class="col-md-4">
    <div class="ticketBox fixed" style="padding-top:0px; overflow: hidden;">
        <div class="row">
            <div class="col-md-12 p-none overflow-hidden mb-m max-h-[150px]">
                <img src="{{ $plugin->getPluginImageData() }}" width="100" height="100" class="rounded ml-base mt-base"/>

                @if($plugin instanceof \Leantime\Domain\Plugins\Models\MarketplacePlugin)
                    <div
                        class="certififed label-default absolute top-[10px] right-[10px] text-primary rounded-full text-sm"
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
                    <h5 class="subtitle">{{ $plugin->name }} {{ $plugin->version ? "(v".$plugin->version.")" : "" }}<br /></h5>
                </div>
            </div>
        @endif
        <div class="row mb-base">
            <div class="col flex flex-col gap-base">
                <x-global::inlineLinks :links="$plugin->getMetadataLinks()" />
                @if (! empty($desc = $plugin->getCardDesc()))
                    <p>{{ $desc }}</p>
                @endif

            </div>
        </div>
        <div class="row border-t border-[var(--main-border-color)] px-base">
            @include($plugin->getControlsView(), ["plugin" => $plugin])
        </div>
    </div>
</div>
