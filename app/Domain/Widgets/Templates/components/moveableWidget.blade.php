
<div class="grid-stack-item" {{ $attributes }}>
    <div class="grid-stack-item-content maincontentinner tw-p-none tw-overflow-hidden">
        <div class="grid-handler-top tw-w-full tw-h-[40px] tw-cursor-grab tw-group tw-absolute tw-top-0 tw-z-10">

            <div class="inlineDropDownContainer tw-float-right tw-p-m">

                <a href="javascript:void(0);" class="dropdown-toggle ticketDropDown editHeadline" data-toggle="dropdown">
                    <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                </a>

                <ul class="dropdown-menu">
                    <li><a href="javascript:void(0)" class="removeWidget">Remove</a></li>
                </ul>
            </div>

        </div>
        <div class="tw-px-m tw-py-l tw-h-full tw-overflow-auto">
            {{ $slot }}
        </div>
    </div>
</div>
