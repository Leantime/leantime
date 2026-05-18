<div class="grid-stack-item" {{ $attributes }}>
    <div class="grid-stack-item-content {{ ($background == "default") ? "maincontentinner" : $background  }} tw-p-none">
        <div class="tw-flex tw-flex-col tw-h-full {{ ($background == "default") ? "tw-pb-l" : "" }}">
            @if(empty($fixed))
            <div class="stickyHeader tw-flex tw-items-center tw-gap-2" style="padding:10px 14px; height:42px; width:100%;">

                <div class="grid-handler-top tw-h-[26px] tw-cursor-grab tw-flex tw-items-center" style="color:var(--primary-font-color); opacity:.35;">
                    <i class="fa-solid fa-grip-vertical" style="font-size:12px;"></i>
                </div>

                @if($name != '' && $noTitle == false)
                    <h5 class="subtitle tw-flex-1" style="margin:0; font-size:var(--base-font-size); font-weight:600;">{{ __($name) }}</h5>
                @else
                    <span class="tw-flex-1"></span>
                @endif

                <div class="inlineDropDownContainer">
                    <a href="javascript:void(0);" class="dropdown-toggle ticketDropDown editHeadline btn btn-link btn-round-icon" data-toggle="dropdown" style="opacity:.5;">
                        <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                    </a>
                    <ul class="dropdown-menu pull-right">
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
