<div class="grid-stack-item" {{ $attributes }}>
    <div class="grid-stack-item-content {{ ($background == "default") ? "maincontentinner" : $background  }} p-none">
        <div class="{{ ($background == "default") ? "pb-l" : "" }}">
            <div class="stickyHeader" style="padding:15px; height:50px;  width:100%;">
                <div class="grid-handler-top h-[40px] cursor-grab float-left mr-sm">
                    <i class="fa-solid fa-grip-vertical"></i>
                </div>
                @if($name != '' && $noTitle == false)
                    <h5 class="subtitle pb-m float-left mr-sm">{{ __($name) }}</h5>
                @endif
                <div class="inlineDropDownContainer float-right">
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
            <span class="clearall"></span>
            <div class="widgetContent {{ ($background == "default") ? 'px-m' : '' }}">
                {{ $slot }}
            </div>
        </div>
        <div class="clear"></div>
    </div>
</div>
