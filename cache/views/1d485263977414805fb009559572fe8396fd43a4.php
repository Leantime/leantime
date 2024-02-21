<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'redirect' => 'dashboard/show',
    'currentProject'
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'redirect' => 'dashboard/show',
    'currentProject'
]); ?>
<?php foreach (array_filter(([
    'redirect' => 'dashboard/show',
    'currentProject'
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<div class="dropdown-menu projectselector" id="mainProjectSelector">

        <?php if($menuType == 'project' || $menuType == 'default'): ?>
            <div class="head">
                <span class="sub"><?php echo e(__("menu.current_project")); ?></span><br />
                <span class="title"><?php echo e($_SESSION['currentProjectName']); ?></span>
            </div>
        <?php else: ?>
            <div class="projectSelectorFooter" style="border:none; border-bottom:1px solid var(--main-border-color)">
            <ul class="selectorList projectList">
                <li>
                    <a href="<?php echo e(BASE_URL); ?>/projects/showMy"><strong><i class="fa-solid fa-house-flag"></i> Open Project Hub</strong></a>
                </li>

                <?php if($login::userIsAtLeast("manager")): ?>
                    <?php $tpl->dispatchTplEvent('beforeProjectCreateLink'); ?>
                    <li><a href="#/projects/createnew">
                            <span class="fancyLink">
                                <?php echo __('menu.create_something_new'); ?>

                            </span>
                        </a>
                    </li>
                    <?php $tpl->dispatchTplEvent('afterProjectCreateLink'); ?>
                <?php endif; ?>

            </ul>
            </div>
        <?php endif; ?>


    <div class="tabbedwidget tab-primary projectSelectorTabs">
        <ul class="tabs">
            <li><a href="#myProjects"><?php echo e(__('menu.projectselector.my_projects')); ?></a></li>
            <li><a href="#favorites"><?php echo e(__('menu.projectselector.favorites')); ?></a></li>
            <li><a href="#recentProjects"><?php echo e(__('menu.projectselector.recent')); ?></a></li>
            <li><a href="#allProjects"><?php echo e(__('menu.projectselector.all_projects')); ?></a></li>
        </ul>

        <div id="myProjects" class="scrollingTab">
            <?php echo $__env->make('menu::partials.projectListFilter', ['clients' => $clients, 'projectSelectFilter' => $projectSelectFilter], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <ul class="selectorList projectList htmx-loaded-content">
                <?php if($projectSelectFilter["groupBy"] == "client"): ?>
                    <?php echo $__env->make('menu::partials.clientGroup', ['projects' => $allAssignedProjects, 'parent' => 0, 'level'=> 0, "prefix" => "myClientProjects", "currentProject"=>$currentProject], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <?php elseif($projectSelectFilter["groupBy"] == "structure"): ?>
                    <?php echo $__env->make('menu::partials.projectGroup', ['projects' => $projectHierarchy, 'parent' => 0, 'level'=> 0, "prefix" => "myProjects", "currentProject"=>$currentProject], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <?php else: ?>
                    <?php echo $__env->make('menu::partials.noGroup', ['projects' => $allAssignedProjects, "currentProject"=>$currentProject], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <?php endif; ?>
            </ul>
        </div>
        <div id="allProjects" class="scrollingTab">
            <?php echo $__env->make('menu::partials.projectListFilter', ['clients' => $clients, 'projectSelectFilter' => $projectSelectFilter], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <ul class="selectorList projectList htmx-loaded-content">
                <?php if($projectSelectFilter["groupBy"] == "client"): ?>
                    <?php echo $__env->make('menu::partials.clientGroup', ['projects' => $allAvailableProjects, 'parent' => 0, 'level'=> 0, "prefix" => "allClientProjects", "currentProject"=>$currentProject], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <?php elseif($projectSelectFilter["groupBy"] == "structure"): ?>
                    <?php echo $__env->make('menu::partials.projectGroup', ['projects' => $allAvailableProjectsHierarchy, 'parent' => 0, 'level'=> 0, "prefix" => "allProjects", "currentProject"=>$currentProject], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <?php else: ?>
                    <?php echo $__env->make('menu::partials.noGroup', ['projects' => $allAvailableProjects, "currentProject"=>$currentProject], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <?php endif; ?>
            </ul>
        </div>
        <div id="recentProjects" class="scrollingTab">
            <ul class="selectorList projectList">
                <?php if(count($recentProjects) >= 1): ?>
                    <?php echo $__env->make('menu::partials.noGroup', ['projects' => $recentProjects], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <?php else: ?>
                    <li class='nav-header'></li>
                    <li><span class='info'>
                        <?php echo e(__("menu.you_dont_have_projects")); ?>

                        </span>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
        <div id="favorites" class="scrollingTab">
            <ul class="selectorList projectList">
                <?php if(count($favoriteProjects) >= 1): ?>
                    <?php echo $__env->make('menu::partials.noGroup', ['projects' => $favoriteProjects], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <?php else: ?>
                    <li><span class='info'>
                        <?php echo e(__("text.you_have_not_favorited_any_projects")); ?>

                        </span>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

            <?php if($menuType == 'project' || $menuType == 'default'): ?>
        <div class="projectSelectorFooter">
            <ul class="selectorList projectList">

                <?php if($login::userIsAtLeast("manager")): ?>
                    <?php $tpl->dispatchTplEvent('beforeProjectCreateLink'); ?>
                    <li><a href="#/projects/createnew">
                            <span class="fancyLink">
                                <?php echo __('menu.create_something_new'); ?>

                            </span>
                        </a>
                    </li>
                    <?php $tpl->dispatchTplEvent('afterProjectCreateLink'); ?>
                <?php endif; ?>


                    <li>
                        <a href="<?php echo e(BASE_URL); ?>/projects/showMy"><i class="fa-solid fa-circle-nodes"></i> Project Hub</a>
                    </li>

            </ul>
        </div>

            <?php endif; ?>

</div>

<script>
    jQuery(document).ready(function () {
        leantime.menuController.initProjectSelector();
    });
</script>
<?php /**PATH /home/lucas/code/leantime/app/Domain/Menu/Templates/partials/projectSelector.blade.php ENDPATH**/ ?>