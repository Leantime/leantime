@props([
    'plugin'
])

<div>
    <div class="ticketBox fixed" style="padding-top:0px; overflow: hidden; margin-bottom: 25px;">
        <div>
            <div class="tw:overflow-hidden tw:mb-m">
                <img src="{{ $plugin->getPluginImageData() }}" width="75" height="75" class="tw:rounded tw:mt-base"/>

                @if($plugin instanceof \Leantime\Domain\Plugins\Models\MarketplacePlugin)
                    <div
                        class="certififed label-default tw:absolute tw:top-[10px] tw:right-[10px] tw:text-primary tw:rounded-full tw:text-sm"
                        data-tippy-content="{{ __('marketplace.certified_tooltip') }}"
                    >
                        <i class="fa fa-certificate"></i>
                        Certified
                    </div>
                @endif
                <div class="clearall"></div>
                <div style="margin-top:10px;">
                    @if (! empty($plugin->name))
                        <strong style="font-size:var(--font-size-l);">{!! $plugin->name !!}</strong> {{ $plugin->version ? "(v".$plugin->version.")" : "" }}<br />
                        <x-globals::inlineLinks :links="$plugin->getMetadataLinks()" />
                    @endif
                </div>
            </div>
        </div>
        <div class="tw:mb-base">
            <div class="tw:flex tw:flex-col tw:gap-base">

                @if (! empty($desc = $plugin->getCardDesc()))
                    <p>{!! $desc !!}</p>
                @endif
                <div class="tw:flex tw:flex-row tw:gap-base">
                    <div class="plugin-price tw:flex-1 tw:content-center" >
                        <strong>{!! $plugin->getPrice() !!}</strong><br />
                    </div>
                    <div class="tw:border-t tw:border-[var(--main-border-color)] tw:px-base align-right tw:flex-1 tw:justify-items-end">
                        @include($plugin->getControlsView(), ["plugin" => $plugin])
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
