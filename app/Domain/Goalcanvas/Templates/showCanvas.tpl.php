<?php

/**
 * Template
 */

use Leantime\Domain\Comments\Repositories\Comments;

defined('RESTRICTED') or die('Restricted access');

foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$canvasName = 'goal';
$elementName = 'goal';

?>

<?php

$canvasTitle = '';
$allCanvas = $tpl->get('allCanvas');
$canvasIcon = $tpl->get('canvasIcon');
$canvasTypes = $tpl->get('canvasTypes');
$statusLabels = $statusLabels ?? $tpl->get('statusLabels');
$relatesLabels = $relatesLabels ?? $tpl->get('relatesLabels');
$dataLabels = $tpl->get('dataLabels');
$disclaimer = $tpl->get('disclaimer');
$canvasItems = $tpl->get('canvasItems');

$filter['status'] = $_GET['filter_status'] ?? (session("filter_status") ?? 'all');
session(["filter_status" => $filter['status']]);
$filter['relates'] = $_GET['filter_relates'] ?? (session("filter_relates") ?? 'all');
session(["filter_relates" => $filter['relates']]);

//get canvas title
foreach ($tpl->get('allCanvas') as $canvasRow) {
    if ($canvasRow["id"] == $tpl->get('currentCanvas')) {
        $canvasTitle = $canvasRow["title"];
        break;
    }
}

$tpl->assign('canvasTitle', $canvasTitle);

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
        <h5><?php $tpl->e(session("currentProjectClient") . " // " . session("currentProjectName")); ?></h5>
        <?php if (count($allCanvas) > 0) {?>
            <span class="dropdown dropdownWrapper headerEditDropdown">
        <a href="javascript:void(0)" class="dropdown-toggle btn btn-transparent" data-toggle="dropdown"><i class="fa-solid fa-ellipsis-v"></i></a>
        <ul class="dropdown-menu editCanvasDropdown">
            <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
                <li><a href="#/goalcanvas/bigRock/<?=$tpl->get('currentCanvas')?>"><?=$tpl->__("links.icon.edit") ?></a></li>
                <li><a href="javascript:void(0)" class="cloneCanvasLink "><?=$tpl->__("links.icon.clone") ?></a></li>
                <li><a href="javascript:void(0)" class="mergeCanvasLink "><?=$tpl->__("links.icon.merge") ?></a></li>
                <li><a href="javascript:void(0)" class="importCanvasLink "><?=$tpl->__("links.icon.import") ?></a></li>
            <?php } ?>
            <li><a href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/export/<?php echo $tpl->get('currentCanvas');?>"><?=$tpl->__("links.icon.export") ?></a></li>
            <li><a href="javascript:window.print();"><?=$tpl->__("links.icon.print") ?></a></li>
            <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
                <li><a href="#/<?=$canvasName ?>canvas/delCanvas/<?php echo $tpl->get('currentCanvas');?>" class="delete"><?php echo $tpl->__("links.icon.delete") ?></a></li>
            <?php } ?>
        </ul>
        </span>
        <?php } ?>
        <h1><?=$tpl->__("headline.$canvasName.board") ?> //
            <?php if (count($allCanvas) > 0) {?>
                <span class="dropdown dropdownWrapper">
                <a href="javascript:void(0);" class="dropdown-toggle header-title-dropdown" data-toggle="dropdown">
                    <?php $tpl->e($canvasTitle); ?>&nbsp;<i class="fa fa-caret-down"></i>
                </a>

                <ul class="dropdown-menu canvasSelector">
                     <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
                         <li><a href="#/goalcanvas/bigRock"><?=$tpl->__("links.icon.create_new_bigrock") ?></a></li>

                     <?php } ?>
                    <li class="border"></li>
                    <?php
                    $lastClient = "";
                    $i = 0;
                    foreach ($tpl->get('allCanvas') as $canvasRow) {
                        echo "<li><a href='" . BASE_URL . "/" . $canvasName . "canvas/showCanvas/" . $canvasRow["id"] . "'>" . $tpl->escape($canvasRow["title"]) . "</a></li>";
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

        <?php echo $tpl->displayNotification(); ?>

        <div class="row">
            <div class="col-md-3">

                <?php if ($login::userIsAtLeast($roles::$editor) && count($canvasTypes) == 1 && count($allCanvas) > 0) { ?>
                    <a href="#/<?=$canvasName ?>canvas/editCanvasItem?type=<?php echo $elementName; ?>"
                       class="btn btn-primary" id="<?php echo $elementName; ?>"><?=$tpl->__('links.add_new_canvas_item' . $canvasName) ?></a>
                <?php } ?>

            </div>

            <div class="col-md-6 center">

            </div>

            <div class="col-md-3">
                <div class="pull-right">
                    <div class="btn-group viewDropDown">
                        <?php if (count($allCanvas) > 0 && !empty($statusLabels)) {?>
                            <?php if ($filter['status'] == 'all') { ?>
                                <button class="btn dropdown-toggle" data-toggle="dropdown"><i class="fas fa-filter"></i> <?=$tpl->__("status.all") ?> <?=$tpl->__("links.view") ?></button>
                            <?php } else { ?>
                                <button class="btn dropdown-toggle" data-toggle="dropdown"><i class="fas fa-fw <?=$tpl->__($statusLabels[$filter['status']]['icon']) ?>"></i> <?=$statusLabels[$filter['status']]['title'] ?> <?=$tpl->__("links.view") ?></button>
                            <?php } ?>
                            <ul class="dropdown-menu">
                                <li><a href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/showCanvas?filter_status=all" <?php if ($filter['status'] == 'all') {
                                    ?>class="active" <?php
                                    } ?>><i class="fas fa-globe"></i> <?=$tpl->__("status.all") ?></a></li>
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
                                <button class="btn dropdown-toggle" data-toggle="dropdown"><i class="fas fa-fw fa-globe"></i> <?=$tpl->__("relates.all") ?> <?=$tpl->__("links.view") ?></button>
                            <?php } else { ?>
                                <button class="btn dropdown-toggle" data-toggle="dropdown"><i class="fas fa-fw <?=$tpl->__($relatesLabels[$filter['relates']]['icon']) ?>"></i> <?=$relatesLabels[$filter['relates']]['title'] ?> <?=$tpl->__("links.view") ?></button>
                            <?php } ?>
                            <ul class="dropdown-menu">
                                <li><a href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/showCanvas?filter_relates=all" <?php if ($filter['relates'] == 'all') {
                                    ?>class="active" <?php
                                    } ?>><i class="fas fa-globe"></i> <?=$tpl->__("relates.all") ?></a></li>
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


    <?php if (count($tpl->get('allCanvas')) > 0) { ?>
        <div id="sortableCanvasKanban" class="sortableTicketList disabled" style="padding-top:15px;">
            <div class="row">
                <div class="col-md-12">
                    <div class="row">
                        <?php foreach ($canvasItems as $row) {
                            $filterStatus = $filter['status'] ?? 'all';
                            $filterRelates = $filter['relates'] ?? 'all';

                            if (
                                $row['box'] === $elementName && ($filterStatus == 'all' ||
                                $filterStatus == $row['status']) && ($filterRelates == 'all' ||
                                $filterRelates == $row['relates'])
                            ) {
                                $comments = app()->make(Comments::class);
                                $nbcomments = $comments->countComments(moduleId: $row['id']);
                                ?>
                            <div class="col-md-4">
                                <div class="ticketBox" id="item_<?php echo $row["id"];?>">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="inlineDropDownContainer" style="float:right;">

                                            <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
                                                <a href="javascript:void(0)" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
                                                    <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                                </a>
                                            <?php } ?>


                                            <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
                                                &nbsp;&nbsp;&nbsp;
                                                <ul class="dropdown-menu">
                                                    <li class="nav-header"><?=$tpl->__("subtitles.edit"); ?></li>
                                                    <li><a href="#/<?=$canvasName ?>canvas/editCanvasItem/<?php echo $row["id"];?>"
                                                           data="item_<?php echo $row["id"];?>"> <?=$tpl->__("links.edit_canvas_item"); ?></a></li>
                                                    <li><a href="#/<?=$canvasName ?>canvas/delCanvasItem/<?php echo $row["id"]; ?>"

                                                           data="item_<?php echo $row["id"];?>"> <?=$tpl->__("links.delete_canvas_item"); ?></a></li>
                                                </ul>
                                            <?php } ?>
                                        </div>

                                        <h4><strong>Goal:</strong> <a href="#/<?=$canvasName ?>canvas/editCanvasItem/<?=$row["id"];?>"
                                               data="item_<?=$row['id'] ?>"><?php $tpl->e($row['title']);?></a></h4>
                                        <br />
                                        <strong>Metric:</strong> <?=$tpl->escape($row["description"]) ?>
                                        <br /><br />




                                        <?php

                                        $percentDone = $row["goalProgress"];
                                        $metricTypeFront = '';
                                        $metricTypeBack = '';
                                        if ($row["metricType"] == "percent") {
                                            $metricTypeBack = '%';
                                        } elseif ($row["metricType"] == "currency") {
                                            $metricTypeFront = $tpl->__("language.currency");
                                        }

                                        ?>

                                        <div class="row">
                                            <div class="col-md-4"></div>
                                            <div class="col-md4 center">

                                                <small><?=sprintf($tpl->__("text.percent_complete"), $percentDone); ?></small>
                                            </div>
                                            <div class="col-md-4"></div>
                                        </div>
                                        <div class="progress" style="margin-bottom:0px;">
                                            <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?php echo $percentDone; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $percentDone; ?>%">
                                                <span class="sr-only"><?=sprintf($tpl->__("text.percent_complete"), $percentDone)?></span>
                                            </div>
                                        </div>
                                        <div class="row" style="padding-bottom:0px;">
                                            <div class="col-md-4">
                                                <small>Start:<br /><?=$metricTypeFront . $row["startValue"] . $metricTypeBack ?></small>
                                            </div>
                                            <div class="col-md-4 center">
                                                <small><?=$tpl->__('label.current') ?>:<br /><?=$metricTypeFront . $row["currentValue"] . $metricTypeBack ?></small>
                                            </div>
                                            <div class="col-md-4" style="text-align:right">
                                                <small><?=$tpl->__('label.goal') ?>:<br /><?=$metricTypeFront . $row["endValue"] . $metricTypeBack ?></small>
                                            </div>
                                        </div>

                                        <div class="clearfix" style="padding-bottom: 8px;"></div>

                                        <?php if (!empty($statusLabels)) { ?>
                                            <div class="dropdown ticketDropdown statusDropdown colorized show firstDropdown">
                                                <a class="dropdown-toggle f-left status label-<?=$row['status'] != "" ? $statusLabels[$row['status']]['dropdown'] : ''?>"
                                                   href="javascript:void(0);" role="button"
                                                   id="statusDropdownMenuLink<?=$row['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <span class="text"><?=$row['status'] != "" ? $statusLabels[$row['status']]['title'] : '' ?></span> <i class="fa fa-caret-down"
                                                                                                                              aria-hidden="true"></i>
                                                </a>
                                                <ul class="dropdown-menu" aria-labelledby="statusDropdownMenuLink<?=$row['id']?>">
                                                    <li class="nav-header border"><?=$tpl->__("dropdown.choose_status")?></li>
                                                    <?php foreach ($statusLabels as $key => $data) { ?>
                                                        <?php if ($data['active'] || true) { ?>
                                                            <li class='dropdown-item'>
                                                                <a href="javascript:void(0);" class="label-<?=$data['dropdown'] ?>"
                                                                   data-label='<?=$data["title"] ?>' data-value="<?=$row['id'] . "/" . $key ?>"
                                                                   id="ticketStatusChange<?=$row['id'] . $key ?>"><?=$data['title'] ?></a>
                                                            </li>
                                                        <?php } ?>
                                                    <?php } ?>
                                                </ul>
                                            </div>
                                        <?php } ?>

                                        <?php if (!empty($relatesLabels)) {  ?>
                                            <div class="dropdown ticketDropdown relatesDropdown colorized show firstDropdown">
                                                <a class="dropdown-toggle f-left relates label-<?=$relatesLabels[$row['relates']]['dropdown'] ?>"
                                                   href="javascript:void(0);" role="button"
                                                   id="relatesDropdownMenuLink<?=$row['id']?>" data-toggle="dropdown" aria-haspopup="true"
                                                   aria-expanded="false">
                                                    <span class="text"><?=$relatesLabels[$row['relates']]['title'] ?></span> <i class="fa fa-caret-down"
                                                                                                                                aria-hidden="true"></i>
                                                </a>
                                                <ul class="dropdown-menu" aria-labelledby="relatesDropdownMenuLink<?=$row['id']?>">
                                                    <li class="nav-header border"><?=$tpl->__("dropdown.choose_relates")?></li>
                                                    <?php foreach ($relatesLabels as $key => $data) { ?>
                                                        <?php if ($data['active'] || true) { ?>
                                                            <li class='dropdown-item'>
                                                                <a href="javascript:void(0);" class="label-<?=$data['dropdown'] ?>"
                                                                   data-label='<?=$data["title"] ?>'
                                                                   data-value="<?=$row['id'] . "/" . $key ?>"
                                                                   id="ticketRelatesChange<?=$row['id'] . $key ?>"><?=$data['title'] ?></a>
                                                            </li>
                                                        <?php } ?>
                                                    <?php } ?>
                                                </ul>
                                            </div>
                                        <?php } ?>


                                        <div class="dropdown ticketDropdown userDropdown noBg show right lastDropdown dropRight">
                                            <a class="dropdown-toggle f-left" href="javascript:void(0);" role="button" id="userDropdownMenuLink<?=$row['id']?>"
                                               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <span class="text">
                                                    <?php if ($row["authorFirstname"] != "") {
                                                        echo "<span id='userImage" . $row['id'] . "'>" .
                                                            "<img src='" . BASE_URL . "/api/users?profileImage=" . $row['author'] . "' width='25' " .
                                                            "style='vertical-align: middle;'/></span><span id='user" . $row['id'] . "'></span>";
                                                    } else {
                                                        echo "<span id='userImage" . $row['id'] . "'><img src='" . BASE_URL .
                                                        "/api/users?profileImage=false' width='25' " .
                                                        "style='vertical-align: middle;'/></span><span id='user" . $row['id'] . "'></span>";
                                                    } ?>
                                                </span>
                                            </a>
                                            <ul class="dropdown-menu" aria-labelledby="userDropdownMenuLink<?=$row['id']?>">
                                                <li class="nav-header border"><?=$tpl->__("dropdown.choose_user")?></li>
                                                    <?php foreach ($tpl->get('users') as $user) {
                                                        echo "<li class='dropdown-item'>" .
                                                        "<a href='javascript:void(0);' data-label='" .
                                                        sprintf(
                                                            $tpl->__("text.full_name"),
                                                            $tpl->escape($user["firstname"]),
                                                            $tpl->escape($user['lastname'])
                                                        ) . "' data-value='" . $row['id'] . "_" . $user['id'] . "_" .
                                                        $user['profileId'] . "' id='userStatusChange" . $row['id'] . $user['id'] . "' ><img src='" .
                                                        BASE_URL . "/api/users?profileImage=" . $user['id'] . "' width='25' " .
                                                        "style='vertical-align: middle; margin-right:5px;'/>" .
                                                        sprintf(
                                                            $tpl->__("text.full_name"),
                                                            $tpl->escape($user["firstname"]),
                                                            $tpl->escape($user['lastname'])
                                                        ) . "</a>";
                                                        echo"</li>";
                                                    }?>
                                            </ul>
                                        </div>

                                        <div class="right" style="margin-right:10px;">
                                            <a href="#/<?=$canvasName ?>canvas/editCanvasComment/<?=$row['id'] ?>"
                                               class="commentCountLink" data="item_<?=$row['id'] ?>"><span class="fas fa-comments"></span></a> <small><?=$nbcomments ?></small>
                                        </div>

                                    </div>
                                </div>

                                    <?php if ($row['milestoneHeadline'] != '') {?>
                                        <br/>
                                        <div hx-trigger="load"
                                             hx-indicator=".htmx-indicator"
                                             hx-get="<?=BASE_URL ?>/hx/tickets/milestones/showCard?milestoneId=<?=$row['milestoneId'] ?>">
                                            <div class="htmx-indicator">
                                                <?=$tpl->__("label.loading_milestone") ?>
                                            </div>
                                        </div>
                                    <?php } ?>
                            </div>
                            </div>
                            <?php } ?>
                        <?php } ?>

                    </div>
                    <br />



                </div>
            </div>
        </div>

        <?php if (count($canvasItems) == 0) {
            echo "<br /><br /><div class='center'>";

            echo "<div class='svgContainer'>";
            echo file_get_contents(ROOT . "/dist/images/svg/undraw_design_data_khdb.svg");
            echo "</div>";

            echo"<h3>" . $tpl->__("headlines.goal.analysis") . "</h3>";
            echo "<br />" . $tpl->__("text.goal.helper_content");


            echo"</div>";
        } ?>


        <div class="clearfix"></div>
    <?php } ?>

<?php echo $tpl->viewFactory->make(
    $tpl->getTemplatePath('canvas', 'showCanvasBottom'),
    array_merge($__data, ['canvasName' => 'goal'])
)->render(); ?>
