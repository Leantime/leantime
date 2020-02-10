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
        cursor:move;
        z-index:5;

    }

    #ideaMason .ticketBox:hover {
        background:#f9f9f9;
    }

    @media (min-width: 900px) {
        .row-fluid .span2,
        .row-fluid .span3 {
            margin-left: 0.5%;
            width: 19.6%;
        }
    }

    .packery-drop-placeholder {
        background:#ddd;
        border: 2px dotted #ccc;
        visibility:visible;
        /* transition position changing */
        -webkit-transition: -webkit-transform 0.2s;
        transition: transform 0.2s;
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

                    <div class="ticketBox" id="item_<?php echo $row["id"]; ?>" data-value="<?php echo $row["id"]; ?>">

                        <h4><a href="/ideas/ideaDialog/<?php echo $row["id"]; ?>" class="ideaModal"
                               data="item_<?php echo $row["id"]; ?>"><?php $this->e($row["description"]); ?></a></h4>
                        <br/>
                        <div class="mainIdeaContent">
                            <?php echo($row["data"]); ?>
                        </div>
                        <br/><br/>


                        <span class="author"><span
                                    class="iconfa-user"></span> <?php $this->e($row["authorFirstname"]); ?> <?php $this->e($row["authorLastname"]); ?></span>&nbsp;
                        <span class="iconfa-comments"></span> <?php echo $row["commentCount"] ?> <?php echo $this->__("text.comments") ?>
                        <br/><?=sprintf($this->__("text.last_modified"), date_format(new DateTime($row["modified"]), $this->__("language.dateformat"))) ?>
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


        var $grid = jQuery('#ideaMason').packery({
            // options
            itemSelector: '.ticketBox',
            columnWidth: 260,
            isResizable: true
        });

        $grid.imagesLoaded().progress(function () {
            $grid.packery('layout');
        });

        var $items = $grid.find('.ticketBox').draggable({
            start: function (event, ui) {
                ui.helper.addClass('tilt');
                tilt_direction(ui.helper);
            },
            stop: function (event, ui) {
                ui.helper.removeClass("tilt");
                jQuery("html").unbind('mousemove', ui.helper.data("move_handler"));
                ui.helper.removeData("move_handler");
            },
        });

        function tilt_direction(item) {
            var left_pos = item.position().left,
                move_handler = function (e) {
                    if (e.pageX >= left_pos) {
                        item.addClass("right");
                        item.removeClass("left");
                    } else {
                        item.addClass("left");
                        item.removeClass("right");
                    }
                    left_pos = e.pageX;
                };
            jQuery("html").bind("mousemove", move_handler);
            item.data("move_handler", move_handler);
        }
        // bind drag events to Packery
        $grid.packery( 'bindUIDraggableEvents', $items );

        function orderItems() {
            var ideaSort = [];

            var itemElems = $grid.packery('getItemElements');
            jQuery( itemElems ).each( function( i, itemElem ) {
                var sortIndex = i + 1;
                var ideaId = jQuery( itemElem ).attr("data-value");
                ideaSort.push({"id":ideaId, "sortIndex":sortIndex});
            });

            // POST to server using $.post or $.ajax
            jQuery.ajax({
                type: 'POST',
                url: '/api/ideas',
                data: {
                    action:"ideaSort",
                    payload: ideaSort
                }

            });
        }


        $grid.on( 'dragItemPositioned',orderItems);


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

