<div class="grid-stack-item" {{ $attributes }}>
    <div class="grid-stack-item-content {{ ($background == "default") ? "maincontentinner" : $background  }} tw:p-none">
        <div class="tw:flex tw:flex-col tw:h-full {{ ($background == "default") ? "tw:pb-l" : "" }}">
            @if(empty($fixed))
            <div class="stickyHeader">

                <div class="grid-handler-top">
                    <x-global::elements.icon name="drag_indicator" />
                </div>

                @if($name != '' && $noTitle == false)
                    <h5 class="subtitle">{{ __($name) }}</h5>
                @else
                    <div style="flex:1;"></div>
                @endif
                <div class="widget-header-actions"></div>
                <x-globals::actions.dropdown-menu>
                    <li><a href="javascript:void(0)" class="fitContent"><x-global::elements.icon name="open_in_full" /> Resize to fit content</a></li>
                    @if(empty($alwaysVisible))
                        <li><a href="javascript:void(0)" class="removeWidget"><x-global::elements.icon name="visibility_off" /> Hide</a></li>
                    @endif
                </x-globals::actions.dropdown-menu>
            </div>
            @endif
            <span class="clearall"></span>
            <div class="widgetContent {{ ($background == "default") ? 'tw:px-m' : '' }}">
                {{ $slot }}
            </div>
        </div>
        <div class="clear"></div>
    </div>
</div>
