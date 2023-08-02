<?php

/**
 * showCanvasTop.inc template - Top part of the main canvas page
 *
 * Required variables:
 * - $canvasName       Name of current canvas
 */

$canvasTitle = '';
$allCanvas = $this->get('allCanvas');
$canvasIcon = $this->get('canvasIcon');
$canvasTypes = $this->get('canvasTypes');
$statusLabels = $statusLabels ?? $this->get('statusLabels');
$relatesLabels = $relatesLabels ?? $this->get('relatesLabels');
$dataLabels = $this->get('dataLabels');
$disclaimer = $this->get('disclaimer');
$canvasItems = $this->get('canvasItems');

$filter['status'] = $_GET['filter_status'] ?? ($_SESSION['filter_status'] ?? 'all');
$_SESSION['filter_status'] = $filter['status'];
$filter['relates'] = $_GET['filter_relates'] ?? ($_SESSION['filter_relates'] ?? 'all');
$_SESSION['filter_relates'] = $filter['relates'];

//get canvas title
foreach ($this->get('allCanvas') as $canvasRow) {
    if ($canvasRow["id"] == $this->get('currentCanvas')) {
        $canvasTitle = $canvasRow["title"];
        break;
    }
}

?>
<style>
  .canvas-row { margin-left: 0px; margin-right: 0px;}
  .canvas-title-only { border-radius: var(--box-radius-small); }
  h4.canvas-element-title-empty { background: white !important; border-color: white !important; }
  div.canvas-element-center-middle { text-align: center; }
</style>

 <div class="pageheader">
    <div class="pageicon"><span class='fa <?=$canvasIcon ?>'></span></div>
    <div class="pagetitle">
        <h5><?php $this->e($_SESSION['currentProjectClient'] . " // " . $_SESSION['currentProjectName']); ?></h5>
        <?php if (count($allCanvas) > 0) {?>
        <span class="dropdown dropdownWrapper headerEditDropdown">
        <a href="javascript:void(0)" class="dropdown-toggle btn btn-transparent" data-toggle="dropdown"><i class="fa-solid fa-ellipsis-v"></i></a>
        <ul class="dropdown-menu editCanvasDropdown">
            <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
                <li><a href="javascript:void(0)" class="editCanvasLink "><?=$this->__("links.icon.edit") ?></a></li>
                <li><a href="javascript:void(0)" class="cloneCanvasLink "><?=$this->__("links.icon.clone") ?></a></li>
                <li><a href="javascript:void(0)" class="mergeCanvasLink "><?=$this->__("links.icon.merge") ?></a></li>
                <li><a href="javascript:void(0)" class="importCanvasLink "><?=$this->__("links.icon.import") ?></a></li>
            <?php } ?>
            <li><a href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/export/<?php echo $this->get('currentCanvas');?>"><?=$this->__("links.icon.export") ?></a></li>
            <li><a href="javascript:window.print();"><?=$this->__("links.icon.print") ?></a></li>
            <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
                <li><a href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/delCanvas/<?php echo $this->get('currentCanvas');?>" class="delete"><?php echo $this->__("links.icon.delete") ?></a></li>
            <?php } ?>
        </ul>
        </span>
        <?php } ?>
        <h1><?=$this->__("headline.$canvasName.board") ?> //
            <?php if (count($allCanvas) > 0) {?>
            <span class="dropdown dropdownWrapper">
                <a href="javascript:void(0);" class="dropdown-toggle header-title-dropdown" data-toggle="dropdown">
                    <?php $this->e($canvasTitle); ?>&nbsp;<i class="fa fa-caret-down"></i>
                </a>

                <ul class="dropdown-menu canvasSelector">
                     <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
                         <li><a href="javascript:void(0)" class="addCanvasLink"><?=$this->__("links.icon.create_new_board") ?></a></li>

                     <?php } ?>
                    <li class="border"></li>
                    <?php
                    $lastClient = "";
                    $i = 0;
                    foreach ($this->get('allCanvas') as $canvasRow) {
                        echo "<li><a href='" . BASE_URL . "/" . $canvasName . "canvas/showCanvas/" . $canvasRow["id"] . "'>" . $this->escape($canvasRow["title"]) . "</a></li>";
                    }
                    ?>
                </ul>
            </span>
            <?php } ?>
        </h1>
    </div>
</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner">

        <?php echo $this->displayNotification(); ?>

        <div class="row">
            <div class="col-md-3">

                <?php if ($login::userIsAtLeast($roles::$editor) && count($canvasTypes) == 1 && count($allCanvas) > 0) { ?>
                    <a href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/editCanvasItem?type=<?php echo $elementName; ?>"
                       class="<?=$canvasName ?>CanvasModal btn btn-primary" id="<?php echo $elementName; ?>"><?=$this->__('links.add_new_canvas_item' . $canvasName) ?></a>
                <?php } ?>

            </div>

            <div class="col-md-6 center">

            </div>

            <div class="col-md-3">
                <div class="pull-right">
                    <div class="btn-group viewDropDown">
                        <?php if (count($allCanvas) > 0 && !empty($statusLabels)) {?>
                            <?php if ($filter['status'] == 'all') { ?>
                                <button class="btn dropdown-toggle" data-toggle="dropdown"><i class="fas fa-filter"></i> <?=$this->__("status.all") ?> <?=$this->__("links.view") ?></button>
                            <?php } else { ?>
                                <button class="btn dropdown-toggle" data-toggle="dropdown"><i class="fas fa-fw <?=$this->__($statusLabels[$filter['status']]['icon']) ?>"></i> <?=$statusLabels[$filter['status']]['title'] ?> <?=$this->__("links.view") ?></button>
                            <?php } ?>
                            <ul class="dropdown-menu">
                                <li><a href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/showCanvas?filter_status=all" <?php if ($filter['status'] == 'all') {
                                    ?>class="active" <?php
                                             } ?>><i class="fas fa-globe"></i> <?=$this->__("status.all") ?></a></li>
                                <?php foreach ($statusLabels as $key => $data) { ?>
                                     <li><a href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/showCanvas?filter_status=<?=$key ?>" <?php if ($filter['status'] == $key) {
                                            ?>class="active" <?php
                                                  } ?>><i class="fas fa-fw <?=$data['icon'] ?>"></i> <?=$data['title'] ?></a></li>
                                <?php } ?>
                            </ul>
                        <?php } ?>
                    </div>

                    <div class="btn-group viewDropDown">
                        <?php if (count($allCanvas) > 0 && !empty($relatesLabels)) {?>
                            <?php if ($filter['relates'] == 'all') { ?>
                                <button class="btn dropdown-toggle" data-toggle="dropdown"><i class="fas fa-fw fa-globe"></i> <?=$this->__("relates.all") ?> <?=$this->__("links.view") ?></button>
                            <?php } else { ?>
                                <button class="btn dropdown-toggle" data-toggle="dropdown"><i class="fas fa-fw <?=$this->__($relatesLabels[$filter['relates']]['icon']) ?>"></i> <?=$relatesLabels[$filter['relates']]['title'] ?> <?=$this->__("links.view") ?></button>
                            <?php } ?>
                            <ul class="dropdown-menu">
                                <li><a href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/showCanvas?filter_relates=all" <?php if ($filter['relates'] == 'all') {
                                    ?>class="active" <?php
                                             } ?>><i class="fas fa-globe"></i> <?=$this->__("relates.all") ?></a></li>
                                <?php foreach ($relatesLabels as $key => $data) { ?>
                                     <li><a href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/showCanvas?filter_relates=<?=$key ?>" <?php if ($filter['relates'] == $key) {
                                            ?>class="active" <?php
                                                  } ?>><i class="fas fa-fw <?=$data['icon'] ?>"></i> <?=$data['title'] ?></a></li>
                                <?php } ?>
                            </ul>
                        <?php } ?>
                    </div>

                </div>
            </div>

        </div>

        <div class="clearfix"></div>
