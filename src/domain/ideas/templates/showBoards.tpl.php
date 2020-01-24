<?php

defined('RESTRICTED') or die('Restricted access');
$allCanvas = $this->get("allCanvas");
$canvasTitle = "";
?>


<style type="text/css">
    #addItem, #editItem {
        display: none;
    }

    #ideaMason .ticketBox {
        width: 250px;

    }

    @media (min-width: 900px) {
        .row-fluid .span2,
        .row-fluid .span3 {
            margin-left: 0.5%;
            width: 19.6%;
        }
    }


    .modal-body {
        max-height: 550px;
    }

    .modalTextArea {
        width: 100%;
    }

    .sortableTicketList .ticketBox {
        cursor: default;
    }
</style>
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
                    <a href="/ideas/ideaDialog&type=idea" class="ideaModal  btn btn-primary" id="customersegment"><span
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
                    <div class="btn-group mt-1 mx-auto" role="group">
                        <a href="/ideas/showBoards"
                           class="btn btn-sm btn-secondary active"><?php echo $this->__("buttons.idea_wall") ?></a>
                        <a href="/ideas/advancedBoards"
                           class="btn btn-sm btn-secondary "><?php echo $this->__("buttons.idea_kanban") ?></a>
                    </div>

                </div>
            </div>

        </div>

        <div class="clearfix"></div>
        <?php if (count($this->get('allCanvas')) > 0) { ?>

            <div id="ideaMason" class="sortableTicketList">


                <?php foreach ($this->get('canvasItems') as $row) { ?>

                    <div class="ticketBox" id="item_<?php echo $row["id"]; ?>">

                        <h4><a href="/ideas/ideaDialog/<?php echo $row["id"]; ?>" class="ideaModal"
                               data="item_<?php echo $row["id"]; ?>"><?php $this->e($row["description"]); ?></a></h4>
                        <br/>
                        <div class="mainIdeaContent">
                            <?php echo nl2br($row["data"]); ?>
                        </div>
                        <br/><br/>


                        <span class="author"><span
                                    class="iconfa-user"></span> <?php $this->e($row["authorFirstname"]); ?> <?php $this->e($row["authorLastname"]); ?></span>&nbsp;
                        <span class="iconfa-comments"></span> <?php echo $row["commentCount"] ?> <?php echo $this->__("text.comments") ?>
                        <br/>Last modified on <?php echo date_format(new DateTime($row["modified"]), "m/d/Y"); ?>
                        <?php if ($row['milestoneHeadline'] != '') { ?>
                            <br/>
                            <hr/>
                            <div class="row">

                                <div class="col-md-5">
                                    <?php $this->e(substr($row['milestoneHeadline'], 0, 10)); ?>[...]
                                </div>
                                <div class="col-md-7" style="text-align:right">
                                    <?php echo $row['percentDone']; ?><?php echo $this->__("text.percent_complete") ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="progress">
                                        <div class="progress-bar progress-bar-success" role="progressbar"
                                             aria-valuenow="<?php echo $row['percentDone']; ?>" aria-valuemin="0"
                                             aria-valuemax="100" style="width: <?php echo $row['percentDone']; ?>%">
                                            <span class="sr-only"><?php echo $row['percentDone']; ?><?php echo $this->__("text.percent_complete") ?>></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>

                <?php } ?>


            </div>
            <div class="clearfix"></div>

            <?php if ($_SESSION['userdata']['role'] == "admin" || $_SESSION['userdata']['role'] == 'manager') { ?>
                <br/>
                <a href="/ideas/delCanvas/<?php echo $this->get('currentCanvas') ?>"
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
                            <input type="submit" class="btn btn-default" value="Create Board" name="newCanvas"/>
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
                            <input type="submit" class="btn btn-default" value="Save" name="editCanvas"/>
                        </div>
                    </form>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->

    </div>
</div>


<script type="text/javascript">


    jQuery(document).ready(function () {

        jQuery(".canvas-select").chosen();

        jQuery(".addItem").click(function () {
            jQuery("#box").val(jQuery(this).attr("id"));
            jQuery('#addItem').modal('show');

        });

        jQuery(".addCanvasLink").click(function () {

            jQuery('#addCanvas').modal('show');

        });

        jQuery(".editCanvasLink").click(function () {

            jQuery('#editCanvas').modal('show');

        });

        var $grid = jQuery('#ideaMason').masonry({
            // options
            itemSelector: '.ticketBox',
            columnWidth: 260
        });

        $grid.imagesLoaded().progress(function () {
            $grid.masonry('layout');

        });

        jQuery('.mainIdeaContent img').each(function () {
            jQuery(this).wrap("<a href='" + jQuery(this).attr("src") + "' class='imageModal'></a>");
        });

        jQuery(".imageModal").nyroModal();

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

        leantime.ideasController.openModalManually("/ideas/ideaDialog<?php echo $modalUrl; ?>");
        window.history.pushState({}, document.title, '/ideas/showBoards');

        <?php } ?>
    });


</script>

