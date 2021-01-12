<?php

defined('RESTRICTED') or die('Restricted access');
$allCanvas = $this->get("allCanvas");
$canvasLabels = $this->get("canvasLabels");
$canvasTitle = "";
?>

<div class="pageheader">
   <div class="pageicon"><i class="far fa-hand-spock"></i></div>
   <div class="pagetitle">
       <h5><?php $this->e($_SESSION['currentProjectClient']." // ". $_SESSION['currentProjectName']); ?></h5>
       <h1><?=$this->__('headline.retrospective') ?></h1>
   </div>
</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner">
    <?php echo $this->displayNotification(); ?>

        <div class="row">
            <div class="col-md-4">

            </div>

            <div class="col-md-4 center">
                <span class="currentSprint">
                    <form action="" method="post">
                        <?php if(count($this->get('allCanvas')) > 0) {?>
                        <select name="searchCanvas" class="mainSprintSelector" onchange="form.submit()">
                            <?php
                            $lastClient = "";
                            $i=0;
                            foreach($this->get('allCanvas') as $canvasRow){ ?>

                                <?php echo"<option value='".$canvasRow["id"]."'";
                                if($this->get('currentCanvas') == $canvasRow["id"]) {
                                    $canvasTitle = $canvasRow["title"];
                                    echo" selected='selected' ";
                                }
                                echo">".$canvasRow["title"]."</option>";

                                ?>

                            <?php }     ?>
                        </select><br />
                        <small><a href="javascript:void(0)" class="addCanvasLink "><?=$this->__('links.create_plan'); ?></a></small> |
                        <small><a href="javascript:void(0)" class="editCanvasLink "><?=$this->__('links.edit_board'); ?></a></small>
                        <?php } ?>
                    </form>

                    </span>
            </div>
            <div class="col-md-4">
                <div class="pull-right">
                    <div class="btn-group mt-1 mx-auto" role="group">

                    </div>

                </div>
            </div>

        </div>

        <div class="clearfix"></div>
    <?php if(count($this->get('allCanvas')) > 0) {?>

        <div id="sortableRetroKanban" class="sortableTicketList disabled" >

            <div class="row-fluid" >

                <div class="column" style="width:33.33%">

                    <h4 class="widgettitle title-primary">
                        <?php if ($login::userIsAtLeast("clientManager")) { ?>
                            <a href="<?=BASE_URL ?>/setting/editBoxLabel?module=retrolabels&label=well" class="editLabelModal editHeadline"><i class="fas fa-edit"></i></a>
                        <?php } ?>
                        <?php echo $canvasLabels["well"]; ?>
                    </h4>

                    <div class="contentInner status_well">
                        <?php foreach($this->get('canvasItems') as $row) { ?>
                            <?php if($row["box"] == "well") {?>
                                <div class="ticketBox" id="item_<?php echo $row["id"];?>">

                                    <div class="row">
                                        <div class="col-md-12">

                                            <?php if ($login::userIsAtLeast("developer")) { ?>
                                                <div class="inlineDropDownContainer" style="float:right;">

                                                    <a href="javascript:void(0);" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
                                                        <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                                    </a>
                                                    <ul class="dropdown-menu">
                                                        <li class="nav-header"><?php echo $this->__("subtitles.edit"); ?></li>
                                                        <li><a href="<?=BASE_URL ?>/retrospectives/retroDialog/<?php echo $row["id"];?>" class="retroModal" data="item_<?php echo $row["id"];?>"> <?php echo $this->__("links.edit_canvas_item"); ?></a></li>
                                                        <li><a href="<?=BASE_URL ?>/retrospectives/delCanvasItem/<?php echo $row["id"]; ?>" class="delete retroModal" data="item_<?php echo $row["id"];?>"> <?php echo $this->__("links.delete_canvas_item"); ?></a></li>

                                                    </ul>
                                                </div>
                                            <?php } ?>

                                            <h4><a href="<?=BASE_URL ?>/retrospectives/retroDialog/<?php echo $row["id"];?>" class="retroModal"  data="item_<?php echo $row["id"];?>"><?php echo $row["description"];?></a></h4>

                                            <div class="mainIdeaContent">
                                                <?php  $this->e($row["data"]); ?>
                                            </div>

                                            <div class="clearfix" style="padding-bottom: 8px;"></div>

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

                                    <?php if($row['milestoneHeadline'] != '') {?>
                                        <br /> <hr />
                                        <div class="row">

                                            <div class="col-md-5" >
                                                <?php echo substr($row['milestoneHeadline'], 0, 10); ?>[...]
                                            </div>
                                            <div class="col-md-7" style="text-align:right">
                                                <?=sprintf($this->__("text.percent_complete"), $row['percentDone'])?>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="progress">
                                                    <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?php echo $row['percentDone']; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $row['percentDone']; ?>%">
                                                        <span class="sr-only"><?=sprintf($this->__("text.percent_complete"), $row['percentDone'])?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>

                                </div>
                            <?php } ?>
                        <?php } ?>
                        <br />
                        <a href="<?=BASE_URL ?>/retrospectives/retroDialog?type=well" class="retroModal" id="well"><?=$this->__('links.add_more');?></a>
                    </div>


                </div>

                <div class="column" style="width:33.33%">

                    <h4 class="widgettitle title-primary">
                        <?php if ($login::userIsAtLeast("clientManager")) { ?>
                            <a href="<?=BASE_URL ?>/setting/editBoxLabel?module=retrolabels&label=notwell" class="editLabelModal editHeadline"><i class="fas fa-edit"></i></a>
                        <?php } ?>
                        <?php echo $canvasLabels["notwell"]; ?>
                    </h4>

                    <div class="contentInner status_notwell">
                        <?php foreach($this->get('canvasItems') as $row) { ?>
                            <?php if($row["box"] == "notwell") {?>
                                <div class="ticketBox" id="item_<?php echo $row["id"];?>">

                                    <div class="row">
                                        <div class="col-md-12">

                                            <?php if ($login::userIsAtLeast("developer")) { ?>
                                                <div class="inlineDropDownContainer" style="float:right;">

                                                    <a href="javascript:void(0)" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
                                                        <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                                    </a>
                                                    <ul class="dropdown-menu">
                                                        <li class="nav-header"><?php echo $this->__("subtitles.edit"); ?></li>
                                                        <li><a href="<?=BASE_URL ?>/retrospectives/retroDialog/<?php echo $row["id"];?>" class="retroModal" data="item_<?php echo $row["id"];?>"> <?php echo $this->__("links.edit_canvas_item"); ?></a></li>
                                                        <li><a href="<?=BASE_URL ?>/retrospectives/delCanvasItem/<?php echo $row["id"]; ?>" class="delete retroModal" data="item_<?php echo $row["id"];?>"> <?php echo $this->__("links.delete_canvas_item"); ?></a></li>

                                                    </ul>
                                                </div>
                                            <?php } ?>

                                            <h4><a href="/retrospectives/retroDialog/<?php echo $row["id"];?>" class="retroModal"  data="item_<?php echo $row["id"];?>"><?php echo $row["description"];?></a></h4>

                                            <div class="mainIdeaContent">
                                                <?php  $this->e($row["data"]); ?>
                                            </div>

                                            <div class="clearfix" style="padding-bottom: 8px;"></div>

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

                                    <?php if($row['milestoneHeadline'] != '') {?>
                                        <br /> <hr />
                                        <div class="row">

                                            <div class="col-md-5" >
                                                <?php echo substr($row['milestoneHeadline'], 0, 10); ?>[...]
                                            </div>
                                            <div class="col-md-7" style="text-align:right">
                                                <?=sprintf($this->__("text.percent_complete"), $row['percentDone'])?>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="progress">
                                                    <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?php echo $row['percentDone']; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $row['percentDone']; ?>%">
                                                        <span class="sr-only"><?=sprintf($this->__("text.percent_complete"), $row['percentDone'])?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>

                                </div>
                            <?php } ?>
                        <?php } ?>
                        <br />
                        <a href="<?=BASE_URL ?>/retrospectives/retroDialog?type=notwell" class="retroModal" id="well"><?=$this->__('links.add_more');?></a>
                    </div>


                </div>

                <div class="column" style="width:33.33%">

                    <h4 class="widgettitle title-primary">
                        <?php if ($login::userIsAtLeast("clientManager")) { ?>
                            <a href="<?=BASE_URL ?>/setting/editBoxLabel?module=retrolabels&label=startdoing" class="editLabelModal editHeadline"><i class="fas fa-edit"></i></a>
                        <?php } ?>
                        <?php echo $canvasLabels["startdoing"]; ?>
                    </h4>

                    <div class="contentInner status_startdoing">
                        <?php foreach($this->get('canvasItems') as $row) { ?>
                            <?php if($row["box"] == "startdoing") {?>
                                <div class="ticketBox" id="item_<?php echo $row["id"];?>">

                                    <div class="row">
                                        <div class="col-md-12">

                                            <?php if ($login::userIsAtLeast("developer")) { ?>
                                                <div class="inlineDropDownContainer" style="float:right;">

                                                    <a href="javascript:void(0);" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
                                                        <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                                    </a>
                                                    <ul class="dropdown-menu">
                                                        <li class="nav-header"><?php echo $this->__("subtitles.edit"); ?></li>
                                                        <li><a href="/retrospectives/retroDialog/<?php echo $row["id"];?>" class="retroModal" data="item_<?php echo $row["id"];?>"> <?php echo $this->__("links.edit_canvas_item"); ?></a></li>
                                                        <li><a href="/retrospectives/delCanvasItem/<?php echo $row["id"]; ?>" class="delete retroModal" data="item_<?php echo $row["id"];?>"> <?php echo $this->__("links.delete_canvas_item"); ?></a></li>

                                                    </ul>
                                                </div>
                                            <?php } ?>

                                            <h4><a href="<?=BASE_URL ?>/retrospectives/retroDialog/<?php echo $row["id"];?>" class="retroModal"  data="item_<?php echo $row["id"];?>"><?php echo $row["description"];?></a></h4>

                                            <div class="mainIdeaContent">
                                                <?php  $this->e($row["data"]); ?>
                                            </div>

                                            <div class="clearfix" style="padding-bottom: 8px;"></div>

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

                                    <?php if($row['milestoneHeadline'] != '') {?>
                                        <br /> <hr />
                                        <div class="row">

                                            <div class="col-md-5" >
                                                <?php echo substr($row['milestoneHeadline'], 0, 10); ?>[...]
                                            </div>
                                            <div class="col-md-7" style="text-align:right">
                                                <?=sprintf($this->__("text.percent_complete"), $row['percentDone'])?>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="progress">
                                                    <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?php echo $row['percentDone']; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $row['percentDone']; ?>%">
                                                        <span class="sr-only"><?=sprintf($this->__("text.percent_complete"), $row['percentDone'])?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>

                                </div>
                            <?php } ?>
                        <?php } ?>
                        <br />
                        <a href="<?=BASE_URL ?>/retrospectives/retroDialog?type=startdoing" class="retroModal" id="startdoing"><?=$this->__('links.add_more');?></a>
                    </div>

                </div>



            </div>




        </div>

        <div class="clearfix"></div>

        <?php if ($login::userIsAtLeast("clientManager")) { ?>
            <br />
            <a href="<?=BASE_URL ?>/retrospectives/delCanvas/<?php echo $this->get('currentCanvas')?>" class="delete right"><?=$this->__('links.delete_board') ?></a>
        <?php } ?>

    <?php } else {

         echo "<br /><br /><div class='center'>";
        echo"<div style='width:50%' class='svgContainer'>";
            echo file_get_contents(ROOT."/images/svg/undraw_team_spirit_hrr4.svg");
        echo"</div>";

        ?>

        <br/><h4><?php echo $this->__("headline.no_retrospectives_yet") ?></h4><br/>
        <?php echo $this->__("subtitles.start_retro_and_discuss_improvements") ?><br/><br/>
        <a href="javascript:void(0)"
           class="addCanvasLink btn btn-primary"><?php echo $this->__("buttons.start_retrospective") ?></a>
        </div>

    <?php
    }
    ?>

        <!-- Modals -->


        <div class="modal fade bs-example-modal-lg" id="addCanvas">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form action="" method="post">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h4 class="modal-title"><?php echo $this->__("headline.start_a_new_retrospective") ?></h4>
                        </div>
                        <div class="modal-body">
                            <label><?php echo $this->__("label.title_retrospective_board") ?></label>
                            <input type="text" name="canvastitle" placeholder="<?php echo $this->__("input.placeholders.you_can_use_milestone_or_sprint") ?>" style="width:90%"/>


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
                            <label><?php echo $this->__("label.title_retrospective_board") ?></label>
                            <input type="text" name="canvastitle" value="<?php $this->e($canvasTitle); ?>" style="width:90%"/>


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

        leantime.retroController.initModals();
        leantime.retroController.initBoardControlModal();
        leantime.retroController.initUserDropdown();
        leantime.retroController.setKanbanHeights();

        <?php
        if(isset($_SESSION['userdata']['settings']["modals"]["retrospectives"]) === false || $_SESSION['userdata']['settings']["modals"]["retrospectives"] == 0) {     ?>
        leantime.helperController.showHelperModal("retrospectives");
        <?php
        //Only show once per session
        $_SESSION['userdata']['settings']["modals"]["retrospectives"] = 1;
        } ?>

        <?php if(isset($_GET['showRetroModal'])) {
            if($_GET['showRetroModal'] == "") {
                $modalUrl = "&type=well";
            }else{
                $modalUrl = "/".(int)$_GET['showRetroModal'];
            }
            ?>

            jQuery(document).ready(function(){
                leantime.retroController.openModalManually("<?=BASE_URL ?>/retrospectives/retroDialog<?php echo $modalUrl; ?>");
                window.history.pushState({},document.title, '<?=BASE_URL ?>/retrospectives/showBoards');
            });

        <?php } ?>

    });

</script>
