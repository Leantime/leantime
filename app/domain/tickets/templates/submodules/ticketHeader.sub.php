<?php

$currentUrlPath = BASE_URL . "/". str_replace(".", "/", \leantime\core\frontcontroller::getCurrentRoute());

$currentSprintId = $this->get("currentSprint");
$searchCriteria = $this->get("searchCriteria");
$searchSprint = $searchCriteria['sprint'] ?? '';
$sprints        = $this->get("sprints");

$sprint = false;

if($currentSprintId == 'all') {
    $sprint = new \leantime\domain\models\sprints();
    $sprint->id = 'all';
    $sprint->name = $this->__("links.all_todos");
}

if($currentSprintId == 'backlog') {
    $sprint = new \leantime\domain\models\sprints();
    $sprint->id = 'backlog';
    $sprint->name = $this->__("links.backlog");
}

if(is_array($this->get('sprints'))) {
    foreach ($this->get('sprints') as $sprintRow) {
        if ($sprintRow->id == $currentSprintId) {
            $sprint = $sprintRow;
            break;
        }
    }
}

?>

<?php $this->dispatchTplEvent('beforePageHeaderOpen'); ?>
<div class="pageheader">
    <?php $this->dispatchTplEvent('afterPageHeaderOpen'); ?>
    <div class="pageicon">
        <span class="fa fa-fw fa-thumb-tack"></span>
    </div>
    <div class="pagetitle">
        <h5><?php $this->e($_SESSION['currentProjectClient'] . " // " . $_SESSION['currentProjectName'] ?? ''); ?></h5>

        <?php  if (($this->get('sprints') !== false) && ($this->get('sprints') !== null) && count($this->get('sprints'))  > 0) {?>
            <span class="dropdown dropdownWrapper headerEditDropdown">
                <a href="javascript:void(0)" class="dropdown-toggle btn btn-transparent" data-toggle="dropdown"><i class="fa-solid fa-ellipsis-v"></i></a>
                <ul class="dropdown-menu editCanvasDropdown">
                    <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
                        <li><a href="#/sprints/editSprint/<?=$this->get("currentSprint")?>"><?=$this->__("link.edit_sprint") ?></a></li>
                    <?php } ?>
                </ul>
            </span>
        <?php } ?>

        <h1>
            <?=$this->__("headlines.todos"); ?>
            <?php  if (($this->get('sprints') !== false) && ($this->get('sprints') !== null) && count($this->get('sprints'))  > 0) {?>

            //
            <span class="dropdown dropdownWrapper">
                <a href="javascript:void(0)" class="dropdown-toggle header-title-dropdown" data-toggle="dropdown">
                    <?php
                    if ($sprint !== false) {
                        $this->e($sprint->name);
                    } else {
                        $this->__('label.select_board');
                    } ?>
                    <i class="fa fa-caret-down"></i>
                </a>

                <ul class="dropdown-menu">
                    <li><a class="wikiModal inlineEdit" href="<?=CURRENT_URL ?>/sprint/editSprint/"><?=$this->__("links.add_sprint") ?></a></li>
                    <li class='nav-header border'></li>
                    <li>
                        <a href="javascript:void(0);" onclick="jQuery('#sprintSelect').val('all'); leantime.ticketsController.initTicketSearchUrlBuilder('<?=$currentUrlPath; ?>')"><?=$this->__("links.all_todos") ?></a>
                    </li>
                    <li>
                        <a href="javascript:void(0);" onclick="jQuery('#sprintSelect').val('backlog'); leantime.ticketsController.initTicketSearchUrlBuilder('<?=$currentUrlPath; ?>')"><?=$this->__("links.backlog") ?></a>
                    </li>
                    <?php foreach ($this->get('sprints') as $sprintRow) {   ?>
                        <li>
                            <a href="javascript:void(0);" onclick="jQuery('#sprintSelect').val(<?=$sprintRow->id?>); leantime.ticketsController.initTicketSearchUrlBuilder('<?=$currentUrlPath; ?>')"><?=$this->escape($sprintRow->name)?><br /><small><?=sprintf($this->__("label.date_from_date_to"), $this->getFormattedDateString($sprintRow->startDate), $this->getFormattedDateString($sprintRow->endDate));?></small></a>
                        </li>
                    <?php } ?>
                </ul>
            </span>
            <?php } ?>
        </h1>
        <input type="hidden" name="sprintSelect" id="sprintSelect" value="<?=$currentSprintId?>" />
    </div>
    <?php $this->dispatchTplEvent('beforePageHeaderClose'); ?>
</div><!--pageheader-->
<?php $this->dispatchTplEvent('afterPageHeaderClose'); ?>
