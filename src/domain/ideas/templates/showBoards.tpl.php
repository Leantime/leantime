<?php
defined('RESTRICTED') or die('Restricted access');
$allCanvas = $this->get("allCanvas");
$canvasTitle = "";
$canvasLabels = $this->get('canvasLabels');
?>

<div class="pageheader">
    <div class="pageicon"><i class="far fa-lightbulb"></i></div>
    <div class="pagetitle">
        <h5><?php $this->e($_SESSION['currentProjectClient'] . " // " . $_SESSION['currentProjectName']); ?></h5>
        <h1><?php echo $this->__("headlines.ideas") ?></h1>
    </div>
</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner" id="ideaBoards">
        <?php echo $this->displayNotification(); ?>

        <div class="row">
            <div class="col-md-4">
                <?php if (count($this->get('allCanvas')) > 0) { ?>
                    <a href="<?=BASE_URL ?>/ideas/ideaDialog?type=idea" class="ideaModal  btn btn-primary" id="customersegment"><span
                                class="far fa-lightbulb"></span><?php echo $this->__("buttons.add_idea") ?></a>
                <?php } ?>
            </div>

            <div class="col-md-4 center">
                <span class="currentSprint">
                    <form action="" method="post">
                        <?php if (count($this->get('allCanvas')) > 0) { ?>
                            <select data-placeholder="<?php echo $this->__("input.placeholders.filter_by_sprint") ?>"
                                    name="searchCanvas"
                                    class="mainSprintSelector" onchange="form.submit()">
                            <?php
                            $lastClient = "";
                            $i = 0;
                            foreach ($this->get('allCanvas') as $canvasRow) { ?>

                                <?php echo "<option value='" . $canvasRow["id"] . "'";
                                if ($this->get('currentCanvas') == $canvasRow["id"]) {
                                    $canvasTitle = $canvasRow["title"];
                                    echo " selected='selected' ";
                                }
                                echo ">" . $this->escape($canvasRow["title"]) . "</option>"; ?>

                            <?php } ?>
                        </select><br/>
                            <small><a href="javascript:void(0)"
                                      class="addCanvasLink"><?php echo $this->__("links.create_idea_board") ?></a></small> |
                         <small><a href="javascript:void(0)"
                                   class="editCanvasLink "><?php echo $this->__("links.edit_idea_board") ?></a></small>
                        <?php } ?>
                    </form>

                    </span>
            </div>
            <div class="col-md-4">
                <div class="pull-right">
                    <div class="btn-group viewDropDown">
                        <button class="btn dropdown-toggle" data-toggle="dropdown"><?=$this->__("buttons.idea_wall") ?> <?=$this->__("links.view") ?></button>
                        <ul class="dropdown-menu">
                            <li><a href="<?=BASE_URL ?>/ideas/showBoards" class="active"><?php echo $this->__("buttons.idea_wall") ?></a></li>
                            <li><a href="<?=BASE_URL ?>/ideas/advancedBoards" class=""><?php echo $this->__("buttons.idea_kanban") ?></a></li>
                        </ul>
                    </div>

                </div>
            </div>

        </div>

        <div class="clearfix"></div>
        <?php if (count($this->get('allCanvas')) > 0) { ?>

            <div id="ideaMason" class="sortableTicketList">


                <?php foreach ($this->get('canvasItems') as $row) { ?>

                    <div class="ticketBox" id="item_<?php echo $row["id"]; ?>" data-value="<?php echo $row["id"]; ?>">

                        <div class="row">
                            <div class="col-md-12">

                                <?php  if ($login::userIsAtLeast("developer")) { ?>
                                    <div class="inlineDropDownContainer" style="float:right;">

                                        <a href="javascript:void(0);" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
                                            <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                        </a>
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
                                    <?php echo($row["data"]); ?>
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
                                        <li class="nav-header border"><?=$this->__("dropdown.choose_status")?></li>

                                        <?php foreach($canvasLabels as $key=>$label){
                                            echo"<li class='dropdown-item'>
                                                <a href='javascript:void(0);' class='".$label['class']."' data-label='".$this->escape($label['name'])."' data-value='".$row['id']."_".$key."_".$label['class']."' id='ticketStatusChange".$row['id'].$key."' >".$this->escape($label['name'])."</a>";
                                            echo"</li>";
                                        }?>
                                    </ul>
                                </div>

                                <div class="dropdown ticketDropdown userDropdown noBg show right lastDropdown dropRight">
                                    <a class="dropdown-toggle f-left" href="javascript:void(0);" role="button" id="userDropdownMenuLink<?=$row['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                    <span class="text">
                                                                        <?php if($row["authorFirstname"] != ""){
                                                                            echo "<span id='userImage".$row['id']."'><img src='".BASE_URL."/api/users?profileImage=".$row['authorProfileId']."' width='25' style='vertical-align: middle;'/></span><span id='user".$row['id']."'></span>";
                                                                        }else {
                                                                            echo "<span id='userImage".$row['id']."'><img src='".BASE_URL."/api/users?profileImage=false' width='25' style='vertical-align: middle;'/></span><span id='user".$row['id']."'></span>";
                                                                        }?>
                                                                    </span>

                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="userDropdownMenuLink<?=$row['id']?>">
                                        <li class="nav-header border"><?=$this->__("dropdown.choose_user")?></li>

                                        <?php foreach($this->get('users') as $user){
                                            echo"<li class='dropdown-item'>
                                                                    <a href='javascript:void(0);' data-label='".sprintf( $this->__("text.full_name"), $this->escape($user["firstname"]), $this->escape($user['lastname']))."' data-value='".$row['id']."_".$user['id']."_".$user['profileId']."' id='userStatusChange".$row['id'].$user['id']."' ><img src='".BASE_URL."/api/users?profileImage=".$user['profileId']."' width='25' style='vertical-align: middle; margin-right:5px;'/>".sprintf( $this->__("text.full_name"), $this->escape($user["firstname"]), $this->escape($user['lastname']))."</a>";
                                            echo"</li>";
                                        }?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <?=sprintf($this->__("text.num_comments"), $row['commentCount'])?>

                        <?php if ($row['milestoneHeadline'] != '') {
                            ?>
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


            </div>
            <div class="clearfix"></div>

            <?php  if ($login::userIsAtLeast("clientManager")) { ?>
                <br/>
                <a href="<?=BASE_URL ?>/ideas/delCanvas/<?php echo $this->get('currentCanvas') ?>"
                   class="delete right"><?php echo $this->__("links.delete_board") ?></a>
            <?php } ?>

        <?php } else { ?>

            <br/><br/>
            <div class='center'>
                <div style='width:50%' class='svgContainer'>
                    <?php echo file_get_contents(ROOT . "/images/svg/undraw_new_ideas_jdea.svg"); ?>
                </div>

                <br/><h4><?php echo $this->__("headlines.have_an_idea") ?></h4><br/>
                <?php echo $this->__("subtitles.start_collecting_ideas") ?><br/><br/>
                <a href="javascript:void(0)"
                   class="addCanvasLink btn btn-primary"><?php echo $this->__("buttons.start_new_idea_board") ?></a>
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
                            <input type="text" name="canvastitle" placeholder="<?php echo $this->__("input.placeholders.name_for_idea_board")?>"
                                   style="width:90%"/>


                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default"
                                    data-dismiss="modal"><?php echo $this->__("buttons.close") ?></button>
                            <input type="submit" class="btn btn-default" value="<?php echo $this->__("buttons.create_board")?>" name="newCanvas"/>
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
                            <input type="submit" class="btn btn-default" value="<?php echo $this->__("buttons.save")?>" name="editCanvas"/>
                        </div>
                    </form>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->

    </div>
</div>


<script type="text/javascript">


    jQuery(document).ready(function () {

        leantime.ideasController.initMasonryWall();
        leantime.ideasController.initBoardControlModal();
        leantime.ideasController.initWallImageModals();
        leantime.ideasController.initStatusDropdown();
        leantime.ideasController.initUserDropdown();

        <?php if(isset($_SESSION['userdata']['settings']["modals"]["ideaBoard"]) === false || $_SESSION['userdata']['settings']["modals"]["ideaBoard"] == 0) {     ?>
            leantime.helperController.showHelperModal("ideaBoard");
        <?php
        //Only show once per session
        $_SESSION['userdata']['settings']["modals"]["ideaBoard"] = 1;
        } ?>

        <?php if(isset($_GET['showIdeaModal'])) {
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

