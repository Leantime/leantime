<?php

use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Sprints\Models\Sprints;

$currentUrlPath = BASE_URL . "/" . str_replace(".", "/", Frontcontroller::getCurrentRoute());

$currentSprintId = $tpl->get("currentSprint");
$searchCriteria = $tpl->get("searchCriteria");
$searchSprint = $searchCriteria['sprint'] ?? '';
$sprints        = $tpl->get("sprints");

$sprint = false;

$currentSprintId = $currentSprintId == '' ? "all" : $currentSprintId;
if ($currentSprintId == 'all') {
    $sprint = new Sprints();
    $sprint->id = 'all';
    $sprint->name = $tpl->__("links.all_todos");
}

if ($currentSprintId == 'backlog') {
    $sprint = new Sprints();
    $sprint->id = 'backlog';
    $sprint->name = $tpl->__("links.backlog");
}

if (is_array($tpl->get('sprints'))) {
    foreach ($tpl->get('sprints') as $sprintRow) {
        if ($sprintRow->id == $currentSprintId) {
            $sprint = $sprintRow;
            break;
        }
    }
}

?>

<?php $tpl->dispatchTplEvent('beforePageHeaderOpen'); ?>
<div class="pageheader">
    <?php $tpl->dispatchTplEvent('afterPageHeaderOpen'); ?>
    <div class="pageicon">
        <span class="fa fa-fw fa-thumb-tack"></span>
    </div>
    <div class="pagetitle">
        <h5><?php $tpl->e(session("currentProjectClient") ?? '' . " // " . session("currentProjectName") ?? ''); ?></h5>

        <?php  if (
            ($tpl->get('currentSprint') !== false)
                && ($tpl->get('currentSprint') !== null)
                && count($tpl->get('sprints'))  > 0
                && $currentSprintId != 'all'
                && $currentSprintId != 'backlog'
) {?>
            <span class="dropdown dropdownWrapper headerEditDropdown">
                <a href="javascript:void(0)" class="dropdown-toggle btn btn-transparent" data-toggle="dropdown"><i class="fa-solid fa-ellipsis-v"></i></a>
                <ul class="dropdown-menu editCanvasDropdown">
                    <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
                        <li><a href="#/sprints/editSprint/<?=$tpl->get("currentSprint")?>"><?=$tpl->__("link.edit_sprint") ?></a></li>
                        <li><a href="#/sprints/delSprint/<?=$tpl->get("currentSprint")?>" class="delete"><?=$tpl->__("links.delete_sprint") ?></a></li>
                    <?php } ?>
                </ul>
            </span>
        <?php } ?>

        <h1>
            <?=$tpl->__("headlines.todos"); ?>
            //
            <span class="dropdown dropdownWrapper">
                <a href="javascript:void(0)" class="dropdown-toggle header-title-dropdown" data-toggle="dropdown">
                    <?php
                    if ($sprint !== false) {
                        $tpl->e($sprint->name);
                    } else {
                        $tpl->__('label.select_sprint');
                    } ?>
                    <i class="fa fa-caret-down"></i>
                </a>

                <ul class="dropdown-menu">
                    <li><a class="wikiModal inlineEdit" href="#/sprints/editSprint/"><i class="fa-solid fa-plus"></i> <?=$tpl->__("links.create_sprint_no_icon") ?></a></li>
                    <li class='nav-header border'></li>
                    <li>
                        <a href="javascript:void(0);" onclick="jQuery('#sprintSelect').val('all'); leantime.ticketsController.initTicketSearchUrlBuilder('<?=$currentUrlPath; ?>')"><?=$tpl->__("links.all_todos") ?></a>
                    </li>
                    <li>
                        <a href="javascript:void(0);" onclick="jQuery('#sprintSelect').val('backlog'); leantime.ticketsController.initTicketSearchUrlBuilder('<?=$currentUrlPath; ?>')"><?=$tpl->__("links.backlog") ?></a>
                    </li>
                    <?php foreach ($tpl->get('sprints') as $sprintRow) {   ?>
                        <li>
                            <a href="javascript:void(0);" onclick="jQuery('#sprintSelect').val(<?=$sprintRow->id?>); leantime.ticketsController.initTicketSearchUrlBuilder('<?=$currentUrlPath; ?>')"><?=$tpl->escape($sprintRow->name)?><br /><small><?=sprintf($tpl->__("label.date_from_date_to"), format($sprintRow->startDate)->date(), format($sprintRow->endDate)->date());?></small></a>
                        </li>
                    <?php } ?>
                </ul>
            </span>

        </h1>
        <input type="hidden" name="sprintSelect" id="sprintSelect" value="<?=$currentSprintId?>" />
    </div>
    <?php $tpl->dispatchTplEvent('beforePageHeaderClose'); ?>
</div><!--pageheader-->
<?php $tpl->dispatchTplEvent('afterPageHeaderClose'); ?>
