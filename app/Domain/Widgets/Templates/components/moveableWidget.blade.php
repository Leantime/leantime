
<div class="grid-stack-item" {{ $attributes }}>
    <div class="grid-stack-item-content maincontentinner tw-p-none tw-overflow-hidden">
        <div class="grid-handler-top tw-w-full tw-h-[40px] tw-cursor-grab tw-group tw-absolute tw-top-0 tw-z-10">
            <a href="javascript:void(0);" class="tw-hidden tw-float-right tw-p-[18px] tw-text-xl group-hover:tw-block">
                <i class="fa fa-ellipsis-v"></i>
            </a>
        </div>
        <div class="tw-px-m tw-py-l tw-h-full tw-overflow-auto">
            {{ $slot }}
        </div>
    </div>
</div>
