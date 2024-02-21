<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'includeTitle' => true,
    'allProjects' => [],
    'background' => ''
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'includeTitle' => true,
    'allProjects' => [],
    'background' => ''
]); ?>
<?php foreach (array_filter(([
    'includeTitle' => true,
    'allProjects' => [],
    'background' => ''
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<div id="myProjectsWidget"
     hx-get="<?php echo e(BASE_URL); ?>/widgets/myProjects/get"
     hx-trigger="click from:.favoriteClick"
     hx-target="#myProjectsWidget"
     hx-swap="outerHTML transition:true">
    <?php if(count($allProjects) == 0): ?>
            <br /><br />
            <div class='center'>
                <div style='width:70%' class='svgContainer'>
                    <?php echo e(__('notifications.not_assigned_to_any_project')); ?>

                    <?php if($login::userIsAtLeast($roles::$manager)): ?>
                        <br /><br />
                        <a href='<?php echo e(BASE_URL); ?>/projects/newProject' class='btn btn-primary'><?php echo e(__('link.new_project')); ?></a>
                    <?php endif; ?>
                </div>
            </div>
    <?php endif; ?>
    <div class="clearall"></div>

    <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'global::components.accordion','data' => ['id' => 'myProjectWidget-favorites','class' => ''.e($background).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('global::accordion'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'myProjectWidget-favorites','class' => ''.e($background).'']); ?>
         <?php $__env->slot('title', null, []); ?> 
            ⭐ My Favorites
         <?php $__env->endSlot(); ?>
         <?php $__env->slot('content', null, []); ?> 

            <div class="row">
                <?php
                    $hasFavorites = false;
                ?>
                <?php $__currentLoopData = $allProjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($project['isFavorite'] == true): ?>
                        <div class="col-md-4">
                            <?php echo $__env->make("projects::partials.projectCard", ["project" => $project, "type" => $type], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                        </div>
                        <?php
                            $hasFavorites = true;
                        ?>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php if($hasFavorites === false): ?>
                    You don't have any favorites. 😿
                <?php endif; ?>
            </div>
         <?php $__env->endSlot(); ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>


    <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'global::components.accordion','data' => ['id' => 'myProjectWidget-otherProjects','class' => ''.e($background).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('global::accordion'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'myProjectWidget-otherProjects','class' => ''.e($background).'']); ?>
         <?php $__env->slot('title', null, []); ?> 
            🗂️ All Assigned Projects
         <?php $__env->endSlot(); ?>
         <?php $__env->slot('content', null, []); ?> 

            <div class="row">
                <?php $__currentLoopData = $allProjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($project['isFavorite'] == false): ?>

                        <div class="col-md-4">
                            <?php echo $__env->make("projects::partials.projectCard", ["project" => $project, "type" => $type], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                        </div>

                    <?php endif; ?>

                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
         <?php $__env->endSlot(); ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>

</div>

<?php $tpl->dispatchTplEvent('afterMyProjectBox'); ?>
<?php /**PATH /home/lucas/code/leantime/app/Domain/Widgets/Templates/partials/myProjects.blade.php ENDPATH**/ ?>