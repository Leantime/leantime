<?php

defined('RESTRICTED') or die('Restricted access');
$allCanvas = $this->get("allCanvas");
$canvasLabels = $this->get("canvasLabels");
$canvasTitle = "";

//All states >0 (<1 is archive)
$numberofColumns = count($this->get('canvasLabels'));
$size = floor((100 / $numberofColumns) * 100) / 100;

//get canvas title
foreach ($this->get('allCanvas') as $canvasRow) {
    if ($canvasRow["id"] == $this->get('currentCanvas')) {
        $canvasTitle = $canvasRow["title"];
        break;
    }
}

?>

<div class="pageheader">
    <div class="pageicon"><i class="far fa-lightbulb"></i></div>
    <div class="pagetitle">
        <h5><?php $this->e($_SESSION['currentProjectClient'] . " // " . $_SESSION['currentProjectName']); ?></h5>
        <?php if (count($allCanvas) > 0) {?>
            <span class="dropdown dropdownWrapper headerEditDropdown">
        <a href="javascript:void(0)" class="dropdown-toggle btn btn-transparent" data-toggle="dropdown"><i class="fa-solid fa-ellipsis-v"></i></a>
        <ul class="dropdown-menu editCanvasDropdown">
            <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
                <li><a href="javascript:void(0)" class="editCanvasLink "><?=$this->__("links.icon.edit") ?></a></li>
                <li><a href="<?=BASE_URL ?>/ideas/delCanvas/<?php echo $this->get('currentCanvas');?>" class="delete"><?php echo $this->__("links.icon.delete") ?></a></li>
            <?php } ?>
        </ul>
        </span>
        <?php } ?>
        <h1><?php echo $this->__("headlines.idea_management") ?>
            //
            <?php if (count($allCanvas) > 0) {?>
                <span class="dropdown dropdownWrapper">
                <a href="javascript:void(0);" class="dropdown-toggle header-title-dropdown" data-toggle="dropdown" style="max-width:200px;">
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
                        echo "<li><a href='" . BASE_URL . "/ideas/showBoards/" . $canvasRow["id"] . "'>" . $this->escape($canvasRow["title"]) . "</a></li>";
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
            <div class="col-md-4">
                <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
                    <?php if (count($this->get('allCanvas')) > 0) { ?>
                    <a href="<?=BASE_URL ?>/ideas/ideaDialog?type=idea" class="ideaModal  btn btn-primary" id="customersegment"><span
                                class="far fa-lightbulb"></span><?php echo $this->__("buttons.add_idea"); ?></a>

                    <?php }
                }?>
            </div>

            <div class="col-md-4 center">

            </div>
            <div class="col-md-4">
                <div class="pull-right">
                    <div class="btn-group viewDropDown">
                        <button class="btn btn-default dropdown-toggle" data-toggle="dropdown"><?=$this->__("buttons.idea_kanban") ?> <?=$this->__("links.view") ?></button>
                        <ul class="dropdown-menu">
                            <li><a href="<?=BASE_URL ?>/ideas/showBoards" ><?php echo $this->__("buttons.idea_wall") ?></a></li>
                            <li><a href="<?=BASE_URL ?>/ideas/advancedBoards" class="active"><?php echo $this->__("buttons.idea_kanban") ?></a></li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>

        <div class="clearfix"></div>
        <?php if (count($this->get('allCanvas')) > 0) { ?>
            <div id="sortableIdeaKanban" class="sortableTicketList">

                <div class="row-fluid">

                    <?php foreach ($this->get('canvasLabels') as $key => $statusRow) {?>
                    <div class="column" style="width:<?=$size?>%;">

                        <h4 class="widgettitle title-primary">
                            <?php if ($login::userIsAtLeast($roles::$manager)) { ?>
                                <a href="<?=BASE_URL ?>/setting/editBoxLabel?module=idealabels&label=<?=$key?>"
                                   class="editLabelModal editHeadline"><i class="fas fa-edit"></i></a>
                            <?php } ?>
                            <?php $this->e($statusRow["name"]); ?>
                        </h4>

                        <div class="contentInner status_<?=$key?>">

                            <?php foreach ($this->get('canvasItems') as $row) { ?>
                                <?php if ($row["box"] == $key) { ?>
                                    <div class="ticketBox moveable" id="item_<?php echo $row["id"]; ?>">

                                        <div class="row">
                                            <div class="col-md-12">

                                                <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
                                                    <div class="inlineDropDownContainer" style="float:right;">

                                                        <a href="javascript:void(0);" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
                                                            <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                                        </a>
                                                <?php } ?>

                                                 <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
                                                        &nbsp;&nbsp;&nbsp;
                                                        <ul class="dropdown-menu">
                                                            <li class="nav-header"><?php echo $this->__("subtitles.edit"); ?></li>
                                                            <li><a href="<?=BASE_URL ?>/ideas/ideaDialog/<?php echo $row["id"];?>" class="ideaModal" data="item_<?php echo $row["id"];?>"> <?php echo $this->__("links.edit_canvas_item"); ?></a></li>
                                                            <li><a href="<?=BASE_URL ?>/ideas/delCanvasItem/<?php echo $row["id"]; ?>" class="delete ideaModal" data="item_<?php echo $row["id"];?>"> <?php echo $this->__("links.delete_canvas_item"); ?></a></li>

                                                        </ul>
                                                    </div>
                                                 <?php } ?>

                                                <h4><a href="<?=BASE_URL ?>/ideas/ideaDialog/<?php echo $row["id"]; ?>" class="ideaModal"
                                                       data="item_<?php echo $row["id"]; ?>"><?php $this->e($row["description"]); ?></a></h4>

                                                <div class="mainIdeaContent">
                                                    <?=$this->escapeMinimal($row["data"]) ?>
                                                </div>

                                                <div class="clearfix" style="padding-bottom: 8px;"></div>

                                                <div class="dropdown ticketDropdown userDropdown noBg show right lastDropdown dropRight">
                                                    <a class="dropdown-toggle f-left" href="javascript:void(0);" role="button" id="userDropdownMenuLink<?=$row['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                    <span class="text">
                                                                        <?php if ($row["authorFirstname"] != "") {
                                                                            echo "<span id='userImage" . $row['id'] . "'><img src='" . BASE_URL . "/api/users?profileImage=" . $row['author'] . "' width='25' style='vertical-align: middle;'/></span><span id='user" . $row['id'] . "'></span>";
                                                                        } else {
                                                                            echo "<span id='userImage" . $row['id'] . "'><img src='" . BASE_URL . "/api/users?profileImage=false' width='25' style='vertical-align: middle;'/></span><span id='user" . $row['id'] . "'></span>";
                                                                        }?>
                                                                    </span>

                                                    </a>
                                                    <ul class="dropdown-menu" aria-labelledby="userDropdownMenuLink<?=$row['id']?>">
                                                        <li class="nav-header border"><?=$this->__("dropdown.choose_user")?></li>

                                                        <?php foreach ($this->get('users') as $user) {
                                                            echo"<li class='dropdown-item'>
                                                                    <a href='javascript:void(0);' data-label='" . sprintf($this->__("text.full_name"), $this->escape($user["firstname"]), $this->escape($user['lastname'])) . "' data-value='" . $row['id'] . "_" . $user['id'] . "_" . $user['profileId'] . "' id='userStatusChange" . $row['id'] . $user['id'] . "' ><img src='" . BASE_URL . "/api/users?profileImage=" . $user['id'] . "' width='25' style='vertical-align: middle; margin-right:5px;'/>" . sprintf($this->__("text.full_name"), $this->escape($user["firstname"]), $this->escape($user['lastname'])) . "</a>";
                                                            echo"</li>";
                                                        }?>
                                                    </ul>
                                                </div>

                                                <div class="pull-right" style="margin-right:10px;">

                                                    <a href="<?=BASE_URL ?>/ideas/ideaDialog/<?php echo $row["id"]; ?>"
                                                       class="ideaModal" data="item_<?=$row['id'] ?>"
                                                        <?php echo $row['commentCount'] == 0 ? 'style="color: grey;"' : '' ?>>
                                                        <span class="fas fa-comments"></span></a> <small><?=$row['commentCount'] ?></small>

                                                </div>

                                            </div>
                                        </div>

                                        <?php if ($row['milestoneHeadline'] != '') { ?>
                                            <br/>
                                            <hr/>
                                            <div class="row">

                                                <div class="col-md-5">
                                                    <?php $this->e(substr($row['milestoneHeadline'], 0, 10)); ?>[...]
                                                </div>
                                                <div class="col-md-7" style="text-align:right">
                                                    <?=sprintf($this->__("text.percent_complete"), $row['percentDone'])?>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="progress">
                                                        <div class="progress-bar progress-bar-success" role="progressbar"
                                                             aria-valuenow="<?php echo $row['percentDone']; ?>" aria-valuemin="0"
                                                             aria-valuemax="100" style="width: <?php echo $row['percentDone']; ?>%">
                                                            <span class="sr-only"><?=sprintf($this->__("text.percent_complete"), $row['percentDone'])?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php } ?>

                                    </div>
                                <?php } ?>
                            <?php } ?>

                        </div>

                    </div>

                    <?php } ?>

                </div>
            </div>
            <div class="clearfix"></div>

        <?php } else { ?>
            <br/><br/>
            <div class='center'>
                <div style='width:50%' class='svgContainer'>
                    <?php echo file_get_contents(ROOT . "/images/svg/undraw_new_ideas_jdea.svg"); ?>
                </div>

                <br/><h4><?php echo $this->__("headlines.have_an_idea") ?></h4><br/>
                <?php echo $this->__("subtitles.start_collecting_ideas") ?><br/><br/>
                <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
                <a href="javascript:void(0);"
                   class="addCanvasLink btn btn-primary"><?php echo $this->__("buttons.start_new_idea_board") ?></a>
                <?php } ?>
            </div>

        <?php } ?>
        <!-- Modals -->


        <div class="modal fade bs-example-modal-lg" id="addCanvas">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form action="" method="post">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h4 class="modal-title"><?php echo $this->__("headlines.start_new_idea_board") ?></h4>
                        </div>
                        <div class="modal-body">
                            <label><?php echo $this->__("label.topic_idea_board") ?></label>
                            <input type="text" name="canvastitle"
                                   placeholder="<?php echo $this->__("input.placeholders.name_for_idea_board") ?>"
                                   style="width:90%"/>


                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default"
                                    data-dismiss="modal"><?php echo $this->__("buttons.close") ?></button>
                            <input type="submit" class="btn btn-default"
                                   value="<?php echo $this->__("buttons.create_board") ?>" name="newCanvas"/>
                        </div>
                    </form>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->

        <div class="modal fade bs-example-modal-lg" id="editCanvas">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form action="" method="post">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h4 class="modal-title"><?php echo $this->__("headlines.edit_board_name") ?></h4>
                        </div>
                        <div class="modal-body">
                            <label><?php echo $this->__("label.title_idea_board") ?></label>
                            <input type="text" name="canvastitle" value="<?php $this->e($canvasTitle); ?>"
                                   style="width:90%"/>


                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default"
                                    data-dismiss="modal"><?php echo $this->__("buttons.close") ?></button>
                            <input type="submit" class="btn btn-default" value="<?php echo $this->__("buttons.save") ?>"
                                   name="editCanvas"/>
                        </div>
                    </form>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->


    </div>
</div>

<script type="text/javascript">

    jQuery(document).ready(function () {

        leantime.ideasController.initBoardControlModal();
        leantime.ideasController.setKanbanHeights();

        <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
        var ideaStatusList = [<?php foreach ($canvasLabels as $key => $statusRow) {
            echo "'" . $key . "',";
                              }?>];
            leantime.ideasController.initIdeaKanban(ideaStatusList);
            leantime.ideasController.initUserDropdown();
        <?php } else { ?>
            leantime.generalController.makeInputReadonly(".maincontentinner");

        <?php } ?>


    });

</script>
