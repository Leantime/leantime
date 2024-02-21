<div class="grid-stack-item" <?php echo e($attributes); ?>>
    <div class="grid-stack-item-content <?php echo e(($background == "default") ? "maincontentinner" : $background); ?> tw-p-none">
        <div class="<?php echo e(($background == "default") ? "tw-pb-l" : ""); ?>">
            <div class="stickyHeader" style="padding:15px; height:50px;  width:100%;">
                <div class="grid-handler-top tw-h-[40px] tw-cursor-grab tw-float-left tw-mr-sm">
                    <i class="fa-solid fa-grip-vertical"></i>
                </div>
                <?php if($name != '' && $noTitle == false): ?>
                    <h5 class="subtitle tw-pb-m tw-float-left tw-mr-sm"><?php echo e(__($name)); ?></h5>
                <?php endif; ?>
                <div class="inlineDropDownContainer tw-float-right">
                    <a href="javascript:void(0);" class="dropdown-toggle ticketDropDown editHeadline" data-toggle="dropdown">
                        <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="javascript:void(0)" class="fitContent"><i class="fa-solid fa-up-right-and-down-left-from-center"></i> Resize to fit content</a></li>

                        <?php if(empty($alwaysVisible)): ?>
                            <li><a href="javascript:void(0)" class="removeWidget"><i class="fa fa-eye-slash"></i> Hide</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <span class="clearall"></span>
            <div class="widgetContent <?php echo e(($background == "default") ? 'tw-px-m' : ''); ?>">
                <?php echo e($slot); ?>

            </div>
        </div>
        <div class="clear"></div>
    </div>
</div>
<?php /**PATH /home/lucas/code/leantime/app/Domain/Widgets/Templates/components/moveableWidget.blade.php ENDPATH**/ ?>