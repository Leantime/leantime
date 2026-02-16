<div class="grid-stack-item" {{ $attributes }}>
    <div class="grid-stack-item-content {{ ($background == "default") ? "maincontentinner" : $background  }} tw:p-none">
        <div class="tw:flex tw:flex-col tw:h-full {{ ($background == "default") ? "tw:pb-l" : "" }}">
            @if(empty($fixed))
            <div class="stickyHeader" style="display:flex; align-items:center; gap:10px; padding:10px 15px; min-height:46px; width:100%;">

                <div class="grid-handler-top" style="cursor:grab; flex-shrink:0; display:flex; align-items:center;">
                    <i class="fa-solid fa-grip-vertical"></i>
                </div>

                @if($name != '' && $noTitle == false)
                    <h5 class="subtitle" style="flex:1; margin:0; line-height:1.2;">{{ __($name) }}</h5>
                @else
                    <div style="flex:1;"></div>
                @endif
                <div class="widget-header-actions" style="flex-shrink:0; display:flex; align-items:center; gap:2px;"></div>
                <div class="inlineDropDownContainer" style="flex-shrink:0; display:flex; align-items:center;">
                    <a href="javascript:void(0);" class="dropdown-toggle ticketDropDown editHeadline" data-toggle="dropdown">
                        <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="javascript:void(0)" class="fitContent"><i class="fa-solid fa-up-right-and-down-left-from-center"></i> Resize to fit content</a></li>
                        @if(empty($alwaysVisible))
                            <li><a href="javascript:void(0)" class="removeWidget"><i class="fa fa-eye-slash"></i> Hide</a></li>
                        @endif
                    </ul>
                </div>
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
