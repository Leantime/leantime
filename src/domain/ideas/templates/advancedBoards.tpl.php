<?php

defined('RESTRICTED') or die('Restricted access');
$allCanvas = $this->get("allCanvas");
$canvasLabels = $this->get("canvasLabels");
$canvasTitle = "";
?>

 <script type="text/javascript">
    
  
  jQuery(window).bind("load", function () {
          jQuery(".loading").fadeOut();
        jQuery(".filterBar .row-fluid").css("opacity", "1");
        
        var maxHeight = 0;
        
        jQuery("#firstRow .bgColumn").each(function(){
            if(jQuery(this).height() > maxHeight){
                maxHeight = jQuery(this).height();
            }
        });
        
        jQuery("#firstRow .contentInner").css("height", (maxHeight+100));
        
      
  });
      
  jQuery(function() {

    jQuery( "#sortableBacklog" ).disableSelection();
    
    jQuery(".canvas-select").chosen();
    
    jQuery(".ticketBox").hover(function(){
        jQuery(this).css("background", "#f9f9f9");
    },function(){
        jQuery(this).css("background", "#ffffff");
    });
    
    jQuery(".contentInner").sortable({
        connectWith: ".contentInner",
        items: "> .moveable",
        tolerance: 'pointer',
         placeholder: "ui-state-highlight",
         forcePlaceholderSize: true,
        cancel: ".portlet-toggle",
        start: function (event, ui) {
            ui.item.addClass('tilt');
            tilt_direction(ui.item);
        },
        stop: function (event, ui) {
            ui.item.removeClass("tilt");
            jQuery("html").unbind('mousemove', ui.item.data("move_handler"));
            ui.item.removeData("move_handler");
        },
        update: function (event, ui) {
            
             // POST to server using $.post or $.ajax
            jQuery.ajax({
                type: 'POST',
                url: leantime.appUrl+'/ideas/advancedBoards&raw=true&sort=true',
                data:
                            {
                            <?php foreach($this->get('canvasLabels') as $key => $statusRow){ ?>
                                <?php echo $key ?>: jQuery(".contentInner.status_<?php echo $key ?>").sortable('serialize'),
                            <?php } ?>
                        statusX: ""
                }
                });
                                    
        }
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
    
    jQuery( ".portlet" )
        .addClass( "ui-widget ui-widget-content ui-helper-clearfix ui-corner-all" )
        .find( ".portlet-header" )
        .addClass( "ui-widget-header ui-corner-all" )
        .prepend( "<span class='ui-icon ui-icon-minusthick portlet-toggle'></span>");
    
    jQuery( ".portlet-toggle" ).click(function() {
        var icon = jQuery( this );
        icon.toggleClass( "ui-icon-minusthick ui-icon-plusthick" );
        icon.closest( ".portlet" ).find( ".portlet-content" ).toggle();
    });
    
    
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


        <?php if(isset($_SESSION['userdata']['settings']["modals"]["advancedBoards"]) === false || $_SESSION['userdata']['settings']["modals"]["advancedBoards"] == 0) {     ?>
      leantime.helperController.showHelperModal("advancedBoards");
            <?php
            //Only show once per session
            $_SESSION['userdata']['settings']["modals"]["advancedBoards"] = 1;
        } ?>
       
  });
  
  </script>
  <style type="text/css">
  #addItem, #editItem {
      display:none;
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
     
    .tilt.right {
        transform: rotate(3deg);
        -moz-transform: rotate(3deg);
        -webkit-transform: rotate(3deg);
    }
    .tilt.left {
        transform: rotate(-3deg);
        -moz-transform: rotate(-3deg);
        -webkit-transform: rotate(-3deg);
    }

      .column {
          box-sizing: border-box;
          height:auto;
          width:100%;
          
      }
      
      .column.full {
          width:100%;
      }
      
      .bgColumn {
          background:#f0f0f0;
          border:1px solid #ccc;
          padding:0px;
      }
      
      .column .contentInner {
          
          padding:10px 5px;
          min-height:200px;
          overflow:auto;
      }
      
      .column.full .contentInner {
          min-height:100px;
      }
      
      .ticketBox:hover {
          background:#f9f9f9;
      }
      
      .ui-state-highlight {
          background:#aaa;
          border:1px dotted #eee;
          visibility:visible;
      }
      
    .portlet {
        margin: 0 1em 1em 0;
        padding: 0.3em;
    }
    .portlet-header {
        padding: 0.2em 0.3em;
        margin-bottom: 0.5em;
        position: relative;
    }
    .portlet-toggle {
        position: absolute;
        top: 50%;
        right: 0;
        margin-top: -8px;
    }
    .portlet-content {
        padding: 0.4em;
    }
    .portlet-placeholder {
        border: 1px dotted black;
        margin: 0 1em 1em 0;
        height: 50px;
    }

      .reducePadding {
        padding-right:0px;
      }
  </style>
 <div class="pageheader">           
    <div class="pageicon"><i class="far fa-lightbulb"></i></div>
    <div class="pagetitle">
        <h5><?php $this->e($_SESSION['currentProjectClient']." // ". $_SESSION['currentProjectName']); ?></h5>
        <h1>Idea Management</h1>
    </div>
</div><!--pageheader-->
           
<div class="maincontent">
    <div class="maincontentinner">
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
                                    echo" selected='selected' ";
                                    $canvasTitle = $canvasRow["title"];
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

                       <a href="<?=BASE_URL ?>/ideas/showBoards" class="btn btn-sm btn-secondary"><i class="fas fa-columns"></i> Idea Wall</a>
                       <a href="<?=BASE_URL ?>/ideas/advancedBoards" class="btn btn-sm btn-secondary active"><i class='iconfa-list'></i> Idea Kanban</a>

                    </div>

                </div>
            </div>

        </div>

        <div class="clearfix"></div>             
    <?php if(count($this->get('allCanvas')) > 0) {?>

        <div id="sortableBacklog" class="sortableTicketList" style="padding-top:10px;">
            
            <div class="row" id="firstRow">

                <div class="col-md-2 reducePadding">
                    <div class="column bgColumn">
                        <h4 class="widgettitle title-primary">
                            <?php if ($_SESSION['userdata']['role'] == 'admin' || $_SESSION['userdata']['role'] == 'manager' ) { ?>
                                <a href="<?=BASE_URL ?>/setting/editBoxLabel?module=idealabels&label=idea" class="editLabelModal editHeadline"><i class="fas fa-edit"></i></a>
                            <?php } ?>
                            <?php $this->e($canvasLabels["idea"]); ?>
                        </h4>
                        <div class="contentInner status_idea">
                            <?php foreach($this->get('canvasItems') as $row) { ?>
                                <?php if($row["box"] == "idea" || $row["box"] == "0") {?>
                                    <div class="ticketBox moveable" id="item_<?php echo $row["id"];?>">

                                        <h4><a href="<?=BASE_URL ?>/ideas/ideaDialog/<?php echo $row["id"];?>" class="ideaModal"  data="item_<?php echo $row["id"];?>"><?php $this->e($row["description"]);?></a></h4>
                                        <br />
                                        <?php echo nl2br($row["data"]);?>
                                        <br /><br />



                                        <span class="author"><span class="iconfa-user"></span> <?php $this->e($row["authorFirstname"]);?> <?php $this->e($row["authorLastname"]);?></span>&nbsp;
                                        <span class="iconfa-comments"></span> <?php echo $row["commentCount"] ?> Comments
                                        <br />Last modified on <?php echo date_format(new DateTime($row["modified"]), "m/d/Y");?>
                                        <?php if($row['milestoneHeadline'] != '') {?>
                                            <br /> <hr />
                                            <div class="row">

                                                <div class="col-md-5" >
                                                    <?php $this->e(substr($row['milestoneHeadline'], 0, 10)); ?> [...]
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
                            <?php } ?>
                            <br />
                            <a href="<?=BASE_URL ?>/ideas/ideaDialog?type=idea" class="ideaModal" id="customersegment"><span class="iconfa iconfa-plus"></span> Add More</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-2 reducePadding">
                    <div class="column bgColumn">
                        <h4 class="widgettitle title-primary">
                            <?php if ($_SESSION['userdata']['role'] == 'admin' || $_SESSION['userdata']['role'] == 'manager' ) { ?>
                                <a href="<?=BASE_URL ?>/setting/editBoxLabel?module=idealabels&label=research" class="editLabelModal editHeadline"><i class="fas fa-edit"></i></a>
                            <?php } ?>
                            <?php echo $canvasLabels["research"]; ?>
                        </h4>
                        <div class="contentInner status_research">
                            <?php foreach($this->get('canvasItems') as $row) { ?>
                                <?php if($row["box"] == "research") {?>
                                    <div class="ticketBox moveable" id="item_<?php echo $row["id"];?>">


                                        <h4><a href="<?=BASE_URL ?>/ideas/ideaDialog/<?php echo $row["id"];?>" class="ideaModal" data="item_<?php echo $row["id"];?>"><?php echo $row["description"];?></a></h4>
                                        <br />
                                        <?php echo nl2br($row["data"]);?>
                                        <br /><br />



                                        <span class="author"><span class="iconfa-user"></span> <?php echo $row["authorFirstname"];?> <?php echo $row["authorLastname"];?></span>&nbsp;
                                        <span class="iconfa-comments"></span> <?php echo $row["commentCount"] ?> Comments
                                        <br />Last modified on <?php echo date_format(new DateTime($row["modified"]), "m/d/Y");?>
                                        <?php if($row['milestoneHeadline'] != '') {?>
                                            <br /><hr />
                                            <div class="row">

                                                <div class="col-md-5" >
                                                    <?php echo substr($row['milestoneHeadline'], 0, 10); ?>[...]
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
                            <?php } ?>
                            <br />
                            <a href="<?=BASE_URL ?>/ideas/ideaDialog?type=research" class="ideaModal" id="customersegment"><span class="iconfa iconfa-plus"></span> Add More</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-2 reducePadding">
                    <div class="column bgColumn">
                        <h4 class="widgettitle title-primary">
                            <?php if ($_SESSION['userdata']['role'] == 'admin' || $_SESSION['userdata']['role'] == 'manager' ) { ?>
                                <a href="<?=BASE_URL ?>/setting/editBoxLabel?module=idealabels&label=prototype" class="editLabelModal editHeadline"><i class="fas fa-edit"></i></a>
                            <?php } ?>
                            <?php echo $canvasLabels["prototype"]; ?>
                        </h4>
                        <div class="contentInner status_prototype">
                            <?php foreach($this->get('canvasItems') as $row) { ?>
                                <?php if($row["box"] == "prototype") {?>
                                    <div class="ticketBox moveable" id="item_<?php echo $row["id"];?>">


                                        <h4><a href="<?=BASE_URL ?>/ideas/ideaDialog/<?php echo $row["id"];?>" class="ideaModal"  data="item_<?php echo $row["id"];?>"><?php echo $row["description"];?></a></h4>
                                        <br />
                                        <?php echo nl2br($row["data"]);?>
                                        <br /><br />




                                        <span class="author"><span class="iconfa-user"></span> <?php echo $row["authorFirstname"];?> <?php echo $row["authorLastname"];?></span>&nbsp;
                                        <span class="iconfa-comments"></span> <?php echo $row["commentCount"] ?> Comments
                                        <br />Last modified on <?php echo date_format(new DateTime($row["modified"]), "m/d/Y");?>
                                        <?php if($row['milestoneHeadline'] != '') {?>
                                            <br /><hr />
                                            <div class="row">

                                                <div class="col-md-5" >
                                                    <?php echo substr($row['milestoneHeadline'], 0, 10); ?>[...]
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
                            <?php } ?>
                            <br />
                            <a href="<?=BASE_URL ?>/ideas/ideaDialog?type=prototype" class="ideaModal" id="customersegment"><span class="iconfa iconfa-plus"></span> Add More</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-2 reducePadding">

                            <div class="column bgColumn">
                                <h4 class="widgettitle title-primary">
                                    <?php if ($_SESSION['userdata']['role'] == 'admin' || $_SESSION['userdata']['role'] == 'manager' ) { ?>
                                        <a href="<?=BASE_URL ?>/setting/editBoxLabel?module=idealabels&label=validation" class="editLabelModal editHeadline"><i class="fas fa-edit"></i></a>
                                    <?php } ?>
                                    <?php echo $canvasLabels["validation"]; ?>
                                </h4>
                                <div class="contentInner status_validation">
                                    <?php foreach($this->get('canvasItems') as $row) { ?>
                                        <?php if($row["box"] == "validation") {?>
                                            <div class="ticketBox moveable" id="item_<?php echo $row["id"];?>">


                                                <h4><a href="<?=BASE_URL ?>/ideas/ideaDialog/<?php echo $row["id"];?>" class="ideaModal"  data="item_<?php echo $row["id"];?>"><?php echo $row["description"];?></a></h4>
                                                <br />
                                                <?php echo nl2br($row["data"]);?>
                                                <br /><br />
                                                <span class="author"><span class="iconfa-user"></span> <?php echo $row["authorFirstname"];?> <?php echo $row["authorLastname"];?></span>&nbsp;
                                                <span class="iconfa-comments"></span> <?php echo $row["commentCount"] ?> Comments
                                                <br />Last modified on <?php echo date_format(new DateTime($row["modified"]), "m/d/Y");?>
                                                <?php if($row['milestoneHeadline'] != '') {?>
                                                    <br /><hr />
                                                    <div class="row">

                                                        <div class="col-md-5" >
                                                            <?php echo substr($row['milestoneHeadline'], 0, 10); ?>[...]
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
                                    <?php } ?>
                                    <br />
                                    <a href="<?=BASE_URL ?>/ideas/ideaDialog?type=validation" class="ideaModal" id="customersegment"><span class="iconfa iconfa-plus"></span> Add More</a>
                                </div>
                            </div>

                </div>

                <div class="col-md-2 reducePadding">

                            <div class="column bgColumn">
                                <h4 class="widgettitle title-primary">
                                    <?php if ($_SESSION['userdata']['role'] == 'admin' || $_SESSION['userdata']['role'] == 'manager' ) { ?>
                                        <a href="<?=BASE_URL ?>/setting/editBoxLabel?module=idealabels&label=implemented" class="editLabelModal editHeadline"><i class="fas fa-edit"></i></a>
                                    <?php } ?>
                                    <?php echo $canvasLabels["implemented"]; ?>
                                </h4>
                                <div class="contentInner status_implemented">
                                    <?php foreach($this->get('canvasItems') as $row) { ?>
                                        <?php if($row["box"] == "implemented") {?>
                                            <div class="ticketBox moveable" id="item_<?php echo $row["id"];?>">


                                                <h4><a href="<?=BASE_URL ?>/ideas/ideaDialog/<?php echo $row["id"];?>" class="ideaModal"  data="item_<?php echo $row["id"];?>"><?php echo $row["description"];?></a></h4>
                                                <br />
                                                <?php echo nl2br($row["data"]);?>
                                                <br /><br />




                                                <span class="author"><span class="iconfa-user"></span> <?php echo $row["authorFirstname"];?> <?php echo $row["authorLastname"];?></span>&nbsp;
                                                <span class="iconfa-comments"></span> <?php echo $row["commentCount"] ?> Comments
                                                <br />Last modified on <?php echo date_format(new DateTime($row["modified"]), "m/d/Y");?>
                                                <?php if($row['milestoneHeadline'] != '') {?>
                                                    <br /><hr />
                                                    <div class="row">

                                                        <div class="col-md-5" >
                                                            <?php echo substr($row['milestoneHeadline'], 0, 10); ?>[...]
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
                                    <?php } ?>
                                    <br />
                                    <a href="<?=BASE_URL ?>/ideas/ideaDialog?type=implemented" class="ideaModal" id="customersegment"><span class="iconfa iconfa-plus"></span> Add More</a>
                                </div>
                            </div>
                </div>

                <div class="col-md-2 ">

                    <div class="column bgColumn">
                        <h4 class="widgettitle title-primary">
                            <?php if ($_SESSION['userdata']['role'] == 'admin' || $_SESSION['userdata']['role'] == 'manager' ) { ?>
                                <a href="<?=BASE_URL ?>/setting/editBoxLabel?module=idealabels&label=deferred" class="editLabelModal editHeadline"><i class="fas fa-edit"></i></a>
                            <?php } ?>
                            <?php echo $canvasLabels["deferred"]; ?>
                        </h4>
                        <div class="contentInner status_deferred">
                            <?php foreach($this->get('canvasItems') as $row) { ?>
                                <?php if($row["box"] == "deferred") {?>
                                    <div class="ticketBox moveable" id="item_<?php echo $row["id"];?>">


                                        <h4><a href="<?=BASE_URL ?>/ideas/ideaDialog/<?php echo $row["id"];?>" class="ideaModal"  data="item_<?php echo $row["id"];?>"><?php echo $row["description"];?></a></h4>
                                        <br />
                                        <?php echo nl2br($row["data"]);?>
                                        <br /><br />




                                        <span class="author"><span class="iconfa-user"></span> <?php echo $row["authorFirstname"];?> <?php echo $row["authorLastname"];?></span>&nbsp;
                                        <span class="iconfa-comments"></span> <?php echo $row["commentCount"] ?> Comments
                                        <br />Last modified on <?php echo date_format(new DateTime($row["modified"]), "m/d/Y");?>
                                        <?php if($row['milestoneHeadline'] != '') {?>
                                            <br /><hr />
                                            <div class="row">

                                                <div class="col-md-5" >
                                                    <?php echo substr($row['milestoneHeadline'], 0, 10); ?>[...]
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
                            <?php } ?>
                            <br />
                            <a href="<?=BASE_URL ?>/ideas/ideaDialog?type=deferred" class="ideaModal" id="customersegment"><span class="iconfa iconfa-plus"></span> Add More</a>
                        </div>
                    </div>
                </div>

            </div>    
            

        </div>
        <div class="clearfix"></div>

        <?php if($_SESSION['userdata']['role'] == "admin" || $_SESSION['userdata']['role'] == 'manager'){ ?>
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
                            <label>What is the title of your  idea board?</label>
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
        
