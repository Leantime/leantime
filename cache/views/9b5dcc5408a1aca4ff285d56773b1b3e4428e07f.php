<div class="projectListFilter">

    <form
          hx-target="#mainProjectSelector"
          hx-swap="outerHTML"
          hx-trigger="change">
        <i class="fas fa-filter"></i>
        <select data-placeholder="" title=""
                hx-post="<?php echo e(BASE_URL); ?>/hx/menu/projectSelector/update-menu"
                hx-target="#mainProjectSelector"
                hx-swap="outerHTML"
                hx-indicator=".htmx-indicator, .htmx-loaded-content"
                name="client">
            <option value="" data-placeholder="true">All Clients</option>
            <?php $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if($client['id'] > 0): ?>
                    <option value='<?php echo e($client['id']); ?>'
                    <?php if(isset($projectSelectFilter['client']) && $projectSelectFilter['client'] == $client['id']): ?>
                        selected='selected'
                    <?php endif; ?>
                   ><?php echo e($client['name']); ?></option>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <i class="fa-solid fa-diagram-project"></i>
        <select data-placeholder="" name="groupBy"
                hx-post="<?php echo e(BASE_URL); ?>/hx/menu/projectSelector/update-menu"
                hx-target="#mainProjectSelector"
                hx-indicator=".htmx-indicator, .htmx-loaded-content"
                hx-swap="outerHTML">
            <?php $__currentLoopData = $projectSelectGroupOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value='<?php echo e($key); ?>'

                    <?php echo e($projectSelectFilter["groupBy"] == $key ? " selected='selected' " : ""); ?>


                ><?php echo e($group); ?></option>

            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <input type="hidden" name="activeTab" value="" />

    </form>

</div>

<div class="htmx-indicator tw-ml-m tw-mr-m tw-pt-l">
    <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'global::components.loadingText','data' => ['type' => 'project','count' => '5','includeHeadline' => 'false']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('global::loadingText'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'project','count' => '5','includeHeadline' => 'false']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
</div>

<?php /**PATH /home/lucas/code/leantime/app/Domain/Menu/Templates/partials/projectListFilter.blade.php ENDPATH**/ ?>