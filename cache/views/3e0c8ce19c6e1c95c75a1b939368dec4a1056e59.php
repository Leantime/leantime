<?php $__env->startSection('content'); ?>

<div class="maincontent" id="gridBoard" style="margin-top:0px; opacity:0;">

    <?php echo $tpl->displayNotification(); ?>


    <div class="grid-stack">

        <?php $__currentLoopData = $dashboardGrid; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $widget): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

            <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'widgets::components.moveableWidget','data' => ['gsX' => ''.e($widget->gridX).'','gsY' => ''.e($widget->gridY).'','gsH' => ''.e($widget->gridHeight).'','gsW' => ''.e($widget->gridWidth).'','gsMinW' => ''.e($widget->gridMinWidth).'','gsMinH' => ''.e($widget->gridMinHeight).'','background' => ''.e($widget->widgetBackground).'','noTitle' => ''.e($widget->noTitle).'','name' => ''.e($widget->name).'','alwaysVisible' => ''.e($widget->alwaysVisible).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('widgets::moveableWidget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['gs-x' => ''.e($widget->gridX).'','gs-y' => ''.e($widget->gridY).'','gs-h' => ''.e($widget->gridHeight).'','gs-w' => ''.e($widget->gridWidth).'','gs-min-w' => ''.e($widget->gridMinWidth).'','gs-min-h' => ''.e($widget->gridMinHeight).'','background' => ''.e($widget->widgetBackground).'','noTitle' => ''.e($widget->noTitle).'','name' => ''.e($widget->name).'','alwaysVisible' => ''.e($widget->alwaysVisible).'']); ?>
                <div hx-get="<?php echo e($widget->widgetUrl); ?>"
                     hx-trigger="<?php echo e($widget->widgetTrigger); ?>"
                     id="<?php echo e($widget->id); ?>"
                    hx-swap="#<?php echo e($widget->id); ?> transition:true">
                    <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'global::components.loadingText','data' => ['type' => ''.e($widget->widgetLoadingIndicator).'','count' => '1','includeHeadline' => 'true']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('global::loadingText'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => ''.e($widget->widgetLoadingIndicator).'','count' => '1','includeHeadline' => 'true']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
                </div>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>

        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>

<script>
<?php $tpl->dispatchTplEvent('scripts.afterOpen'); ?>

jQuery(document).ready(function() {

    leantime.widgetController.initGrid();

    <?php if($completedOnboarding === false): ?>
        leantime.helperController.firstLoginModal();
    <?php endif; ?>

    <?php if($completedOnboarding == "1" && (isset($_SESSION['userdata']['settings']["modals"]["dashboard"]) === false || $_SESSION['userdata']['settings']["modals"]["dashboard"] == 0)): ?>
        leantime.helperController.showHelperModal("dashboard", 500, 700);

        <?php if(!isset($_SESSION['userdata']['settings']["modals"])): ?>
            <?php ($_SESSION['userdata']['settings']["modals"] = array()); ?>
        <?php endif; ?>

        <?php if(!isset($_SESSION['userdata']['settings']["modals"]["dashboard"])): ?>
            <?php ($_SESSION['userdata']['settings']["modals"]["dashboard"] = 1); ?>
        <?php endif; ?>
    <?php endif; ?>
});
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make($layout, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/lucas/code/leantime/app/Domain/Dashboard/Templates/home.blade.php ENDPATH**/ ?>