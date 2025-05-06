<div class="grid-stack-item" {{ $attributes }}>
    <div class="grid-stack-item-content {{ ($background == "default") ? "maincontentinner" : $background  }} tw-p-none">
        <div class="tw-flex tw-flex-col tw-h-full {{ ($background == "default") ? "tw-pb-l" : "" }}">
            @if(empty($fixed))
            <div class="stickyHeader" style="padding:15px; height:50px;  width:100%;">

                <div class="grid-handler-top tw-h-[30px] tw-cursor-grab tw-float-left tw-mr-sm">
                    <i class="fa-solid fa-grip-vertical"></i>
                </div>

                @if($name != '' && $noTitle == false)
                    <h5 class="subtitle tw-pb-m tw-float-left tw-mr-sm">{{ __($name) }}</h5>
                @endif
                <div class="inlineDropDownContainer tw-float-right">
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
            <div class="widgetContent {{ ($background == "default") ? 'tw-px-m' : '' }}">
                {{ $slot }}
            </div>
        </div>
        <div class="clear"></div>
    </div>
</div>
