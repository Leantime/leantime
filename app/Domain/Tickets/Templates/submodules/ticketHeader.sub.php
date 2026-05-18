<?php

use Leantime\Core\Controller\Frontcontroller;

$currentUrlPath = BASE_URL.'/'.str_replace('.', '/', Frontcontroller::getCurrentRoute());

$currentSprintId = $tpl->get('currentSprint');
$searchCriteria = $tpl->get('searchCriteria');
$searchSprint = $searchCriteria['sprint'] ?? '';
$sprints = $tpl->get('sprints');
$currentSprintId = 'all';

?>

<?php $tpl->dispatchTplEvent('beforePageHeaderOpen'); ?>
<div class="pageheader">
    <?php $tpl->dispatchTplEvent('afterPageHeaderOpen'); ?>
    <div class="pageicon">
        <span class="fa fa-fw fa-thumb-tack"></span>
    </div>
    <div class="pagetitle">
        <h5><?php $tpl->e(session('currentProjectClient') ?? ''.' // '.session('currentProjectName') ?? ''); ?></h5>

        <h1>
            <?= $tpl->__('headlines.todos'); ?>
        </h1>
        <input type="hidden" name="sprintSelect" id="sprintSelect" value="<?= $currentSprintId?>" />
    </div>
    <?php $tpl->dispatchTplEvent('beforePageHeaderClose'); ?>
</div><!--pageheader-->
<?php $tpl->dispatchTplEvent('afterPageHeaderClose'); ?>
