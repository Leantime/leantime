<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'project' => [],
    'type' => 'simple'
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'project' => [],
    'type' => 'simple'
]); ?>
<?php foreach (array_filter(([
    'project' => [],
    'type' => 'simple'
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<?php ( $percentDone = format($project['progress']['percent'])->decimal()); ?>

<div class="projectBox">
    <div class="row" >
        <div class="col-md-12 fixed">
            <div class="row tw-pb-sm">
                <div class="col-md-10">
                    <a href="<?php echo e(BASE_URL); ?>/dashboard/show?projectId=<?php echo e($project['id']); ?>">
                        <span class="projectAvatar">
                            <?php if(isset($projectTypeAvatars[$project["type"]]) && $projectTypeAvatars[$project["type"]] != "avatar"): ?>
                                <span class="<?php echo e($projectTypeAvatars[$project["type"]]); ?>"></span>
                            <?php else: ?>
                                <img src='<?php echo e(BASE_URL); ?>/api/projects?projectAvatar=<?php echo e($project["id"]); ?>&v=<?php echo e(format($project['modified'])->timestamp()); ?>' />
                            <?php endif; ?>
                        </span>
                        <?php if($project["clientName"] != ''): ?>
                            <small><?php echo e($project["clientName"]); ?></small><br />
                        <?php else: ?>
                            <small><?php echo e(__('projectType.'.$project["type"] ?? 'project')); ?></small><br />
                        <?php endif; ?>
                        <strong><?php echo e($project['name']); ?> <i class="fa-solid fa-up-right-from-square"></i></strong>
                    </a>
                </div>
                <div class="col-md-2 tw-text-right">
                    <a  href="javascript:void(0);"
                        onclick="leantime.projectsController.favoriteProject(<?php echo e($project['id']); ?>, this)"
                        class="favoriteClick favoriteStar pull-right margin-right <?php echo e($project['isFavorite'] ? 'isFavorite' : ''); ?> tw-mr-[5px]"
                        data-tippy-content="<?php echo e(__('label.favorite_tooltip')); ?>">
                            <i class="<?php echo e($project['isFavorite'] ? 'fa-solid' : 'fa-regular'); ?> fa-star"></i>
                    </a>
                </div>
            </div>

            <?php if($type != "simple"): ?>
                <div class="row">
                    <div class="col-md-7">
                        <?php echo e(__("subtitles.project_progress")); ?>

                    </div>
                    <div class="col-md-5" style="text-align:right">
                        <?php echo e(sprintf(__("text.percent_complete"), $percentDone)); ?>

                    </div>
                </div>


                    <div class="progress">
                        <div class="progress-bar progress-bar-success"
                             role="progressbar"
                             aria-valuenow="<?php echo e($percentDone); ?>"
                             aria-valuemin="0"
                             aria-valuemax="100"
                             style="width: <?php echo e($percentDone); ?>%">
                            <span class="sr-only"><?php echo e(sprintf(__("text.percent_complete"), $percentDone)); ?></span>
                        </div>
                    </div>



                <div class="row">
                    <div class="col-md-12">
                        <?php if($project['status'] !== null && $project['status'] != ''): ?>
                            <span class="label label-<?php echo e($project['status']); ?>">
                                <?php echo e(__("label.project_status_" . $project['status'])); ?>

                            </span><br />
                        <?php else: ?>
                            <span class="label label-grey"><?php echo e(__("label.no_status")); ?></span><br />
                        <?php endif; ?>
                    </div>
                </div>
                <br />
                <div class="row">
                    <div class="col-md-12">


                        <div class="team">
                            <?php $__currentLoopData = $project['team']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="commentImage" style="margin-right:-10px;">
                                    <img
                                        style=""
                                        src="<?php echo e(BASE_URL); ?>/api/users?profileImage=<?php echo e($member['id']); ?>&v=<?php echo e(format($member['modified'])->timestamp()); ?>" data-tippy-content="<?php echo e($member['firstname'] . ' ' . $member['lastname']); ?>" />
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <div class="clearall"></div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php /**PATH /home/lucas/code/leantime/app/Domain/Projects/Templates/partials/projectCard.blade.php ENDPATH**/ ?>