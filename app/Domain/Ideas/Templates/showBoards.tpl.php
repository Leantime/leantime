<?php
defined('RESTRICTED') or die('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$allCanvas = $tpl->get("allCanvas");
$canvasTitle = "";
$canvasLabels = $tpl->get('canvasLabels');

//get canvas title
foreach ($tpl->get('allCanvas') as $canvasRow) {
    if ($canvasRow["id"] == $tpl->get('currentCanvas')) {
        $canvasTitle = $canvasRow["title"];
        break;
    }
}

?>

<div class="pageheader">
    <div class="pageicon"><i class="far fa-lightbulb"></i></div>
    <div class="pagetitle">
        <h5><?php $tpl->e(session("currentProjectClient") ?? '' . " // " . session("currentProjectName")); ?></h5>
        <?php if (count($allCanvas) > 0) {?>
            <span class="dropdown dropdownWrapper headerEditDropdown">
                <a href="javascript:void(0)" class="dropdown-toggle btn btn-transparent" data-toggle="dropdown"><i class="fa-solid fa-ellipsis-v"></i></a>
                <ul class="dropdown-menu editCanvasDropdown ">
                    <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
                        <li><a href="#/ideas/boardDialog/<?php echo $tpl->get('currentCanvas');?>"><?=$tpl->__("links.icon.edit") ?></a></li>
                        <li><a href="<?=BASE_URL ?>/ideas/delCanvas/<?php echo $tpl->get('currentCanvas');?>" class="delete"><?php echo $tpl->__("links.icon.delete") ?></a></li>
                    <?php } ?>
                </ul>
            </span>
        <?php } ?>
        <h1><?php echo $tpl->__("headlines.ideas") ?>
            //
            <?php if (count($allCanvas) > 0) {?>
                <span class="dropdown dropdownWrapper">
                <a href="javascript:void(0);" class="dropdown-toggle header-title-dropdown" data-toggle="dropdown">
                    <?php $tpl->e($canvasTitle); ?>&nbsp;<i class="fa fa-caret-down"></i>
                </a>

                <ul class="dropdown-menu canvasSelector">
                     <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
                         <li><a href="#/ideas/boardDialog"><?=$tpl->__("links.icon.create_new_board") ?></a></li>

                     <?php } ?>
                    <li class="border"></li>
                    <?php
                    $lastClient = "";
                    $i = 0;
                    foreach ($tpl->get('allCanvas') as $canvasRow) {
                        echo "<li><a href='" . BASE_URL . "/ideas/showBoards/" . $canvasRow["id"] . "'>" . $tpl->escape($canvasRow["title"]) . "</a></li>";
                    }
                    ?>
                </ul>
            </span>
            <?php } ?>

        </h1>
    </div>
</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner" id="ideaBoards" style="min-height:350px;">
        <?php echo $tpl->displayNotification(); ?>

        <div class="row">
            <div class="col-md-4">
                <?php  if ($login::userIsAtLeast($roles::$editor)) { ?>
                    <?php if (count($tpl->get('allCanvas')) > 0) { ?>
                        <a href="#/ideas/ideaDialog?type=idea" class="btn btn-primary" id="customersegment"><span
                                    class="far fa-lightbulb"></span><?php echo $tpl->__("buttons.add_idea") ?></a>
                    <?php } ?>
                <?php } ?>
            </div>

            <div class="col-md-4 center">

            </div>
            <div class="col-md-4">
                <div class="pull-right">
                    <div class="btn-group viewDropDown">
                        <button class="btn dropdown-toggle" data-toggle="dropdown"><?=$tpl->__("buttons.idea_wall") ?> <?=$tpl->__("links.view") ?></button>
                        <ul class="dropdown-menu">
                            <li><a href="<?=BASE_URL ?>/ideas/showBoards" class="active"><?php echo $tpl->__("buttons.idea_wall") ?></a></li>
                            <li><a href="<?=BASE_URL ?>/ideas/advancedBoards" class=""><?php echo $tpl->__("buttons.idea_kanban") ?></a></li>
                        </ul>
                    </div>

                </div>
            </div>

        </div>

        <div class="clearfix"></div>

        <?php if (count($tpl->get('allCanvas')) > 0) { ?>
            <div id="ideaMason" class="sortableTicketList" style="padding-top:10px;">


                <?php foreach ($tpl->get('canvasItems') as $row) { ?>
                    <div class="ticketBox" id="item_<?php echo $row["id"]; ?>" data-value="<?php echo $row["id"]; ?>">

                        <div class="row">
                            <div class="col-md-12">

                                <?php  if ($login::userIsAtLeast($roles::$editor)) { ?>
                                    <div class="inlineDropDownContainer" style="float:right;">

                                        <a href="javascript:void(0);" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
                                            <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                        </a>
                                        &nbsp;&nbsp;&nbsp;
                                        <ul class="dropdown-menu">
                                            <li class="nav-header"><?php echo $tpl->__("subtitles.edit"); ?></li>
                                            <li><a href="#/ideas/ideaDialog/<?php echo $row["id"];?>" class="" data="item_<?php echo $row["id"];?>"> <?php echo $tpl->__("links.edit_canvas_item"); ?></a></li>
                                            <li><a href="#/ideas/delCanvasItem/<?php echo $row["id"]; ?>" class="delete" data="item_<?php echo $row["id"];?>"> <?php echo $tpl->__("links.delete_canvas_item"); ?></a></li>

                                        </ul>
                                    </div>
                                <?php } ?>

                                <h4><a href="#/ideas/ideaDialog/<?php echo $row["id"]; ?>"
                                       data="item_<?php echo $row["id"]; ?>"><?php $tpl->e($row["description"]); ?></a></h4>

                                <div class="mainIdeaContent">
                                    <div class="kanbanCardContent">

                                        <div class="kanbanContent" style="margin-bottom: 20px">
                                            <?=$tpl->escapeMinimal($row["data"]) ?>
                                        </div>


                                    </div>


                                </div>

                                <div class="clearfix" style="padding-bottom: 8px;"></div>

                                <div class="dropdown ticketDropdown statusDropdown show firstDropdown colorized">
                                    <a class="dropdown-toggle f-left status <?=$canvasLabels[$row['box']]['class'] ?> " href="javascript:void(0);" role="button" id="statusDropdownMenuLink<?=$row['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                    <span class="text"><?php
                                                                    echo $canvasLabels[$row['box']]['name'];
                                                                    ?>
                                                                    </span>
                                        &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="statusDropdownMenuLink<?=$row['id']?>">
                                        <li class="nav-header border"><?=$tpl->__("dropdown.choose_status")?></li>

                                        <?php foreach ($canvasLabels as $key => $label) {
                                            echo"<li class='dropdown-item'>
                                                <a href='javascript:void(0);' class='" . $label['class'] . "' data-label='" . $tpl->escape($label['name']) . "' data-value='" . $row['id'] . "_" . $key . "_" . $label['class'] . "' id='ticketStatusChange" . $row['id'] . $key . "' >" . $tpl->escape($label['name']) . "</a>";
                                            echo"</li>";
                                        }?>
                                    </ul>
                                </div>


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
                                        <li class="nav-header border"><?=$tpl->__("dropdown.choose_user")?></li>

                                        <?php foreach ($tpl->get('users') as $user) {
                                            echo"<li class='dropdown-item'>
                                                                    <a href='javascript:void(0);' data-label='" . sprintf($tpl->__("text.full_name"), $tpl->escape($user["firstname"]), $tpl->escape($user['lastname'])) . "' data-value='" . $row['id'] . "_" . $user['id'] . "_" . $user['profileId'] . "' id='userStatusChange" . $row['id'] . $user['id'] . "' ><img src='" . BASE_URL . "/api/users?profileImage=" . $user['id'] . "' width='25' style='vertical-align: middle; margin-right:5px;'/>" . sprintf($tpl->__("text.full_name"), $tpl->escape($user["firstname"]), $tpl->escape($user['lastname'])) . "</a>";
                                            echo"</li>";
                                        }?>
                                    </ul>
                                </div>

                                <div class="pull-right" style="margin-right:10px;">

                                    <a href="#/ideas/ideaDialog/<?php echo $row["id"]; ?>"
                                       class="" data="item_<?=$row['id'] ?>"
                                        <?php echo $row['commentCount'] == 0 ? 'style="color: grey;"' : '' ?>>
                                        <span class="fas fa-comments"></span></a> <small><?=$row['commentCount'] ?></small>

                                </div>

                            </div>
                        </div>

                        <?php if ($row['milestoneHeadline'] != '') { ?>
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

                <?php } ?>



            </div>
            <?php if (count($tpl->get('canvasItems')) == 0) { ?>
                <div class='center'>
                    <div style='width:30%' class='svgContainer'>
                        <?php echo file_get_contents(ROOT . "/dist/images/svg/undraw_new_ideas_jdea.svg"); ?>
                    </div>

                    <h3><?php echo $tpl->__("headlines.have_an_idea") ?></h3><br />
                    <?php echo $tpl->__("subtitles.start_collecting_ideas") ?><br/><br/>
                </div>
            <?php } ?>
            <div class="clearfix"></div>

        <?php } else { ?>
            <br/><br/>
            <div class='center'>
                <div style='width:30%' class='svgContainer'>
                    <?php echo file_get_contents(ROOT . "/dist/images/svg/undraw_new_ideas_jdea.svg"); ?>
                </div>

                <h3><?php echo $tpl->__("headlines.have_an_idea") ?></h3><br />
                <?php echo $tpl->__("subtitles.start_collecting_ideas") ?><br/><br/>
                <?php  if ($login::userIsAtLeast($roles::$editor)) { ?>
                <a href="javascript:void(0)"
                   class="addCanvasLink btn btn-primary"><?php echo $tpl->__("links.icon.create_new_board") ?></a>
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
                            <h4 class="modal-title"><?php echo $tpl->__("headlines.start_new_idea_board") ?></h4>
                        </div>
                        <div class="modal-body">
                            <label><?php echo $tpl->__("label.topic_idea_board") ?></label>
                            <input type="text" name="canvastitle" placeholder="<?php echo $tpl->__("input.placeholders.name_for_idea_board")?>"
                                   style="width:90%"/>


                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default"
                                    data-dismiss="modal"><?php echo $tpl->__("buttons.close") ?></button>
                            <input type="submit" class="btn btn-default" value="<?php echo $tpl->__("buttons.create_board")?>" name="newCanvas"/>
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
                            <h4 class="modal-title"><?php echo $tpl->__("headlines.edit_board_name") ?></h4>
                        </div>
                        <div class="modal-body">
                            <label><?php echo $tpl->__("label.title_idea_board") ?></label>
                            <input type="text" name="canvastitle" value="<?php $tpl->e($canvasTitle); ?>"
                                   style="width:90%"/>


                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default"
                                    data-dismiss="modal"><?php echo $tpl->__("buttons.close") ?></button>
                            <input type="submit" class="btn btn-default" value="<?php echo $tpl->__("buttons.save")?>" name="editCanvas"/>
                        </div>
                    </form>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->

        <div class="clearfix"></div>

    </div>
</div>


<script type="text/javascript">


    jQuery(document).ready(function () {

        //new SlimSelect({ select: '#searchCanvas' });

        leantime.ideasController.initMasonryWall();
        leantime.ideasController.initBoardControlModal();
        leantime.ideasController.initWallImageModals();

        <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
            leantime.ideasController.initStatusDropdown();
            leantime.ideasController.initUserDropdown();
        <?php } else { ?>
        leantime.authController.makeInputReadonly(".maincontentinner");

        <?php } ?>

        <?php if (isset($_GET['showIdeaModal'])) {
            if ($_GET['showIdeaModal'] == "") {
                $modalUrl = "&type=idea";
            } else {
                $modalUrl = "/" . (int)$_GET['showIdeaModal'];
            }
            ?>

        leantime.ideasController.openModalManually("<?=BASE_URL ?>/ideas/ideaDialog<?php echo $modalUrl; ?>");
        window.history.pushState({}, document.title, '<?=BASE_URL ?>/ideas/showBoards');

        <?php } ?>
    });

</script>

