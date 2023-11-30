
<div class="grid-stack-item" {{ $attributes }}>
    <div class="grid-stack-item-content {{ ($background == "default") ? "maincontentinner" : $background  }} tw-p-none">
        <div class="{{ ($background == "default") ? "tw-px-m tw-py-l" : ""  }}">
            <div class="grid-handler-top tw-w-full tw-h-[40px] tw-cursor-grab tw-group tw-absolute tw-top-0 tw-left-0 tw-z-10">

            <div class="inlineDropDownContainer tw-float-right tw-p-m">

                <a href="javascript:void(0);" class="dropdown-toggle ticketDropDown editHeadline" data-toggle="dropdown">
                    <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                </a>

                <ul class="dropdown-menu">
                    <li><a href="javascript:void(0)" class="fitContent"><i class="fa-solid fa-up-right-and-down-left-from-center"></i> Resize to fit content</a></li>
                    <li><a href="javascript:void(0)" class="removeWidget"><i class="fa fa-trash"></i> Remove</a></li>
                </ul>
            </div>

        </div>
            {{ $slot }}
        </div>
        <div class="clear"></div>
    </div>
</div>
