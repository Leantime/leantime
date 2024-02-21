<ul class="level-0 noGroup">
    <?php $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

        <?php if(
           !isset($_SESSION['userdata']["projectSelectFilter"]['client'])
           || $_SESSION['userdata']["projectSelectFilter"]['client'] == $project["clientId"]
           || $_SESSION['userdata']["projectSelectFilter"]['client'] == 0
           || $_SESSION['userdata']["projectSelectFilter"]['client'] == ""
           ): ?>

            <li class="projectLineItem hasSubtitle <?php echo e($_SESSION['currentProject'] ?? 0  == $project['id'] ? "active" : ''); ?>" >
                <?php echo $__env->make('menu::partials.projectLink', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <div class="clear"></div>
            </li>

        <?php endif; ?>

    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</ul>
<?php /**PATH /home/lucas/code/leantime/app/Domain/Menu/Templates/partials/noGroup.blade.php ENDPATH**/ ?>