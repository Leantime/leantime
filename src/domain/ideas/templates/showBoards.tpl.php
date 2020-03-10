<?php

defined('RESTRICTED') or die('Restricted access');
$allCanvas = $this->get("allCanvas");
$canvasTitle = "";
?>


  <style type="text/css">
  #addItem, #editItem {
      display:none;
  }

  #ideaMason .ticketBox {
      width:250px;

  }
  
  @media (min-width:900px) {
      .row-fluid .span2,
      .row-fluid .span3 {
          margin-left: 0.5%;
        width: 19.6%;
      }
  }

  
     .modal-body {
         max-height:550px;    
     }
     
     .modalTextArea {
         width:100%;    
     }

    .sortableTicketList .ticketBox {
        cursor:default;
    }
  </style>
 <div class="pageheader">           
    <div class="pageicon"><i class="far fa-lightbulb"></i></div>
    <div class="pagetitle">
        <h5><?php $this->e($_SESSION['currentProjectClient']." // ". $_SESSION['currentProjectName']); ?></h5>
        <h1>Ideas</h1>
    </div>
</div><!--pageheader-->
           
<div class="maincontent">
    <div class="maincontentinner" id="ideaBoards">
    <?php echo $this->displayNotification(); ?>

        <div class="row">
            <div class="col-md-4">
                <?php if(count($this->get('allCanvas')) > 0) {?>
                    <a href="<?=BASE_URL ?>/ideas/ideaDialog?type=idea" class="ideaModal  btn btn-primary" id="customersegment"><span class="far fa-lightbulb" ></span> Add Idea</a>
                <?php } ?>
            </div>

            <div class="col-md-4 center">
                <span class="currentSprint">
                    <form action="" method="post">
                        <?php if(count($this->get('allCanvas')) > 0) {?>
                            <select data-placeholder="Filter by Sprint..." name="searchCanvas" class="mainSprintSelector" onchange="form.submit()">
                            <?php
                            $lastClient = "";
                            $i=0;
                            foreach($this->get('allCanvas') as $canvasRow){ ?>

                                <?php echo"<option value='".$canvasRow["id"]."'";
                                if($this->get('currentCanvas') == $canvasRow["id"]) {
                                    $canvasTitle = $canvasRow["title"];
                                    echo" selected='selected' ";
                                }
                                echo">".$this->escape($canvasRow["title"])."</option>"; ?>

                            <?php }     ?>
                        </select><br />
                         <small><a href="javascript:void(0)" class="addCanvasLink"><i class="fa fa-plus"></i> Create New Board</a></small> |
                         <small><a href="javascript:void(0)" class="editCanvasLink "><i class="fa fa-edit"></i> Edit Board</a></small>
                        <?php } ?>
                    </form>

                    </span>
            </div>
            <div class="col-md-4">
                <div class="pull-right">
                    <div class="btn-group mt-1 mx-auto" role="group">
                        <a href="<?=BASE_URL ?>/ideas/showBoards" class="btn btn-sm btn-secondary active"><i class="fas fa-columns"></i> Idea Wall</a>
                        <a href="<?=BASE_URL ?>/ideas/advancedBoards" class="btn btn-sm btn-secondary "><i class='iconfa-list'></i> Idea Kanban</a>
                    </div>

                </div>
            </div>

        </div>

        <div class="clearfix"></div>             
    <?php if(count($this->get('allCanvas')) > 0) {?>

        <div id="ideaMason" class="sortableTicketList">


            <?php foreach($this->get('canvasItems') as $row) { ?>

                    <div class="ticketBox" id="item_<?php echo $row["id"];?>">

                        <h4><a href="<?=BASE_URL ?>/ideas/ideaDialog/<?php echo $row["id"];?>" class="ideaModal"  data="item_<?php echo $row["id"];?>"><?php $this->e($row["description"]);?></a></h4>
                        <br />
                        <div class="mainIdeaContent">
                            <?php echo $row["data"];?>
                        </div>
                        <br /><br />




                        <span class="author"><span class="iconfa-user"></span> <?php $this->e($row["authorFirstname"]);?> <?php $this->e($row["authorLastname"]);?></span>&nbsp;
                        <span class="iconfa-comments"></span> <?php echo $row["commentCount"] ?> Comments
                        <br />Last modified on <?php echo date_format(new DateTime($row["modified"]), "m/d/Y");?>
                        <?php if($row['milestoneHeadline'] != '') {?>
                            <br /> <hr />
                            <div class="row">

                                <div class="col-md-5" >
                                    <?php $this->e(substr($row['milestoneHeadline'], 0, 10)); ?>[...]
                                </div>
                                <div class="col-md-7" style="text-align:right">
                                    <?php echo $row['percentDone']; ?>% Complete
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="progress">
                                        <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?php echo $row['percentDone']; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $row['percentDone']; ?>%">
                                            <span class="sr-only"><?php echo $row['percentDone']; ?>% Complete</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>

            <?php } ?>



        </div>
        <div class="clearfix"></div>

        <?php if($_SESSION['userdata']['role'] == "admin" || $_SESSION['userdata']['role'] == 'manager' ){ ?>
            <br />
            <a href="<?=BASE_URL ?>/ideas/delCanvas/<?php echo $this->get('currentCanvas')?>" class="delete right"><i class="fa fa-trash"></i> Delete Board</a>
        <?php } ?>

    <?php } else {

        echo "<br /><br /><div class='center'>";
        echo"<div style='width:50%' class='svgContainer'>";
        echo file_get_contents(ROOT."/images/svg/undraw_new_ideas_jdea.svg");
        echo"</div>";

echo"<br /><h4>Have an idea?</h4><br />
Start collecting all of your brilliant ideas right here.<br /><br /><a href=\"javascript:void(0)\" class=\"addCanvasLink btn btn-primary\"><i class=\"fa fa-plus\"></i> Start a new idea board</a></div>";

    }
    ?>

        <!-- Modals -->


        <div class="modal fade bs-example-modal-lg" id="addCanvas">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form action="" method="post">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h4 class="modal-title">Start a new idea board</h4>
                        </div>
                        <div class="modal-body">
                            <label>What is the topic of your idea board?</label>
                            <input type="text" name="canvastitle" placeholder="A name for your idea board" style="width:90%"/>


                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <input type="submit"  class="btn btn-default" value="Create Board" name="newCanvas" />
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
                            <h4 class="modal-title">Edit Board Name</h4>
                        </div>
                        <div class="modal-body">
                            <label>What is the title of your idea board?</label>
                            <input type="text" name="canvastitle" value="<?php $this->e($canvasTitle); ?>" style="width:90%"/>


                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <input type="submit"  class="btn btn-default" value="Save" name="editCanvas" />
                        </div>
                    </form>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->

    </div>
</div>


<script type="text/javascript">


    jQuery(document).ready(function() {

        jQuery(".canvas-select").chosen();

        jQuery(".addItem").click(function(){
            jQuery("#box").val(jQuery(this).attr("id"));
            jQuery('#addItem').modal('show');

        });

        jQuery(".addCanvasLink").click(function() {

            jQuery('#addCanvas').modal('show');

        });

        jQuery(".editCanvasLink").click(function() {

            jQuery('#editCanvas').modal('show');

        });

        var $grid = jQuery('#ideaMason').masonry({
            // options
            itemSelector: '.ticketBox',
            columnWidth: 260
        });

        $grid.imagesLoaded().progress( function() {
            $grid.masonry('layout');

        });

        jQuery('.mainIdeaContent img').each(function(){
            jQuery(this).wrap("<a href='"+jQuery(this).attr("src")+"' class='imageModal'></a>");
        });

        jQuery(".imageModal").nyroModal();

        <?php if(isset($_SESSION['userdata']['settings']["modals"]["ideaBoard"]) === false || $_SESSION['userdata']['settings']["modals"]["ideaBoard"] == 0) {     ?>
            leantime.helperController.showHelperModal("ideaBoard");
            <?php
            //Only show once per session
            $_SESSION['userdata']['settings']["modals"]["ideaBoard"] = 1;
        } ?>

        <?php if(isset($_GET['showIdeaModal'])) {
            if($_GET['showIdeaModal'] == "") {
                $modalUrl = "&type=idea";
            }else{
                $modalUrl = "/".(int)$_GET['showIdeaModal'];
            }
            ?>

            leantime.ideasController.openModalManually("<?=BASE_URL?>/ideas/ideaDialog<?php echo $modalUrl; ?>");
            window.history.pushState({},document.title, '<?=BASE_URL?>/ideas/showBoards');

        <?php } ?>
    });




</script>

