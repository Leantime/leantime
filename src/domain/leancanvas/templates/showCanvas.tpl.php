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
        
        jQuery("#firstRow > div").each(function(){
            if(jQuery(this).height() > maxHeight){
                maxHeight = jQuery(this).height();
            }
        });
        
        jQuery("#firstRow > div").css("height", maxHeight);
        
      
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
                url: leantime.appUrl+'#',
                data: 
                {
                    
                    problem: jQuery(".contentInner.status_problem").sortable('serialize'),

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
    
    
                                            
    jQuery(".editItem").click(function(){
        
        var item = "#"+jQuery(this).attr("data");
        var description = jQuery(item).find(".description").val();
        var assumptions = jQuery(item).find(".assumptions").val();
        var data = jQuery(item).find(".data").val();
        var conclusion = jQuery(item).find(".conclusion").val();
        var box = jQuery(item).find(".box").val();
        var status = jQuery(item).find(".status").val();
        var id = jQuery(item).find(".itemId").val();
        
        jQuery('#editItem').find(".description").val(description);
        jQuery('#editItem').find(".assumptions").val(assumptions);
        jQuery('#editItem').find(".data").val(data);
        jQuery('#editItem').find(".conclusion").val(conclusion);
        jQuery('#editItem').find(".box").val(box);
        jQuery('#editItem').find(".itemId").val(id);
        jQuery('#editItem').find(".status").val(status);
        jQuery(".delLink").attr("href", "/leancanvas/delCanvasItem/"+id);
        
        jQuery('#editItem').modal('show');
        
        
        
        
    });


    jQuery(".addCanvasLink").click(function() {

        jQuery('#addCanvas').modal('show');

    });

      jQuery(".editCanvasLink").click(function() {

          jQuery('#editCanvas').modal('show');

      });


        <?php if(isset($_SESSION['userdata']['settings']["modals"]["fullLeanCanvas"]) === false || $_SESSION['userdata']['settings']["modals"]["fullLeanCanvas"] == 0) {     ?>
      leantime.helperController.showHelperModal("fullLeanCanvas");
            <?php
            //Only show once per session
            $_SESSION['userdata']['settings']["modals"]["fullLeanCanvas"] = 1;
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
  </style>
 <div class="pageheader">           
    <div class="pageicon"><span class="fas fa-flask"></span></div>
    <div class="pagetitle">
        <h5><?php $this->e($_SESSION['currentProjectClient']." // ". $_SESSION['currentProjectName']); ?></h5>
        <h1>Lean Canvas</h1>
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
                                echo">".$canvasRow["title"]."</option>"; ?>

                            <?php }     ?>
                        </select><br />
                            <small><a href="javascript:void(0)" class="addCanvasLink"><i class="fa fa-plus"></i> Create new Plan</a></small> |
                            <small><a href="javascript:void(0)" class="editCanvasLink "><i class="fa fa-edit"></i> Edit Board</a></small>
                        <?php } ?>
                    </form>

                    </span>
            </div>
            <div class="col-md-4">
                <div class="pull-right">
                    <div class="btn-group mt-1 mx-auto" role="group">
                        <a href="<?=BASE_URL ?>/leancanvas/simpleCanvas" class="btn btn-sm btn-secondary "><i class="fas fa-columns"></i> Simple Canvas</a>
                        <a href="<?=BASE_URL ?>/leancanvas/showCanvas" class="btn btn-sm btn-secondary active"><i class='fas fa-graduation-cap'></i> Full Lean Canvas</a>
                    </div>

                </div>
            </div>

        </div>

        <div class="clearfix"></div>             
    <?php if(count($this->get('allCanvas')) > 0) {?>
        <div id="sortableBacklog" class="sortableTicketList" style="padding-top:10px;">
            
            <div class="row-fluid" id="firstRow">

                <div class="span3 bgColumn">
                    <div class="column">
                        <h4 class="widgettitle title-primary">

                            <?php if ($_SESSION['userdata']['role'] == 'admin' || $_SESSION['userdata']['role'] == 'manager' ) { ?>
                                <a href="<?=BASE_URL ?>/setting/editBoxLabel?module=researchlabels&label=problem" class="editLabelModal editHeadline"><i class="fas fa-edit"></i></a>
                            <?php } ?>

                            <?php echo $canvasLabels["problem"]; ?>
                        </h4>
                        <div class="contentInner status_problem">
        <?php foreach($this->get('canvasItems') as $row) { ?>
            <?php if($row["box"] == "problem") {?>
                                        
                                        <div class="ticketBox" id="item_<?php echo $row["id"];?>">
                                            
                                            <div class="pull-right">
                                                <span id="status" class="f-left label label-<?php echo $row["status"];?>" style="margin-left:0px;">
                <?php 
                if($row["status"] == "danger") { echo "Not validated yet"; 
                } elseif($row["status"] == "info") { echo "Validated and it's false";
                } elseif($row["status"] == "success") { echo "Validated and it's true";
                }
                ?>
                                                </span>    
                                            </div>
                                            <h4><a href="<?=BASE_URL ?>/leancanvas/editCanvasItem/<?php echo $row["id"];?>" class="canvasModal" data="item_<?php echo $row["id"];?>"><?php echo $row["description"];?></a></h4>
                                            <br />
                                            
                                            <input type="hidden" value="<?php echo $row["description"];?>" name="description" class="description"/>
                                            <input type="hidden" value="<?php echo $row["assumptions"];?>" name="assumptions" class="assumptions"/>
                                            <input type="hidden" value="<?php echo $row["data"];?>" name="data" class="data"/>
                                            <input type="hidden" value="<?php echo $row["conclusion"];?>" name="conclusion" class="conclusion"/>
                                            <input type="hidden" value="<?php echo $row["box"];?>" name="box" class="box"/>
                                            <input type="hidden" value="<?php echo $row["status"];?>" name="status" class="status"/>
                                            <input type="hidden" value="<?php echo $row["id"];?>" name="itemId" class="itemId"/>
                                            
                                            
                                            
                                            <span class="author"><span class="iconfa-user"></span> <?php echo $row["authorFirstname"];?> <?php echo $row["authorLastname"];?></span>
                                            &nbsp;
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
                            <a href="<?=BASE_URL ?>/leancanvas/editCanvasItem?type=problem" class="canvasModal" id="problem"><span class="iconfa iconfa-plus"></span> Add New</a>
                        </div>
                    </div>            
                </div>
                
                <div class="span2 bgColumn">
                    <div class="row-fluid">
                        <div class="span12">
                            <div class="column">
                                <h4 class="widgettitle title-primary">
                                    <?php if ($_SESSION['userdata']['role'] == 'admin' || $_SESSION['userdata']['role'] == 'manager' ) { ?>
                                        <a href="<?=BASE_URL ?>/setting/editBoxLabel?module=researchlabels&label=solution" class="editLabelModal editHeadline"><i class="fas fa-edit"></i></a>
                                    <?php } ?>

                                    <?php echo $canvasLabels["solution"]; ?>
                                </h4>
                                <div class="contentInner status_problem">
                                    <?php foreach($this->get('canvasItems') as $row) { ?>
                                        <?php if($row["box"] == "solution") {?>
                                            <div class="ticketBox" id="item_<?php echo $row["id"];?>">
                                            
                                            <div class="pull-right">
                                                <span id="status" class="f-left label label-<?php echo $row["status"];?>" style="margin-left:0px;">
                                            <?php 
                                            if($row["status"] == "danger") { echo "Not validated yet"; 
                                            } elseif($row["status"] == "info") { echo "Validated and it's false";
                                            } elseif($row["status"] == "success") { echo "Validated and it's true";
                                            }
                                            ?>
                                                </span>    
                                            </div>
                                            <h4><a href="<?=BASE_URL ?>/leancanvas/editCanvasItem/<?php echo $row["id"];?>" class="canvasModal"  data="item_<?php echo $row["id"];?>"><?php echo $row["description"];?></a></h4>
                                            <br />
                                            
                                            <input type="hidden" value="<?php echo $row["description"];?>" name="description" class="description"/>
                                            <input type="hidden" value="<?php echo $row["assumptions"];?>" name="assumptions" class="assumptions"/>
                                            <input type="hidden" value="<?php echo $row["data"];?>" name="data" class="data"/>
                                            <input type="hidden" value="<?php echo $row["conclusion"];?>" name="conclusion" class="conclusion"/>
                                            <input type="hidden" value="<?php echo $row["box"];?>" name="box" class="box"/>
                                            <input type="hidden" value="<?php echo $row["status"];?>" name="status" class="status"/>
                                            <input type="hidden" value="<?php echo $row["id"];?>" name="itemId" class="itemId"/>
                                            
                                            
                                            
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
                                    <a href="<?=BASE_URL ?>/leancanvas/editCanvasItem?type=solution"  class="canvasModal" id="solution"><span class="iconfa iconfa-plus"></span> Add New</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row-fluid">
                        <div class="span12">
                            <div class="column">
                                <h4 class="widgettitle title-primary">
                                    <?php if ($_SESSION['userdata']['role'] == 'admin' || $_SESSION['userdata']['role'] == 'manager' ) { ?>
                                        <a href="<?=BASE_URL ?>/setting/editBoxLabel?module=researchlabels&label=keymetrics" class="editLabelModal editHeadline"><i class="fas fa-edit"></i></a>
                                    <?php } ?>

                                    <?php echo $canvasLabels["keymetrics"]; ?>
                                </h4>
                                <div class="contentInner status_problem">
                                    <?php foreach($this->get('canvasItems') as $row) { ?>
                                        <?php if($row["box"] == "keymetrics") {?>
                                            <div class="ticketBox" id="item_<?php echo $row["id"];?>">
                                            
                                            <div class="pull-right">
                                                <span id="status" class="f-left label label-<?php echo $row["status"];?>" style="margin-left:0px;">
                                            <?php 
                                            if($row["status"] == "danger") { echo "Not validated yet"; 
                                            } elseif($row["status"] == "info") { echo "Validated and it's false";
                                            } elseif($row["status"] == "success") { echo "Validated and it's true";
                                            }
                                            ?>
                                                </span>    
                                            </div>
                                            <h4><a href="<?=BASE_URL ?>/leancanvas/editCanvasItem/<?php echo $row["id"];?>" class="canvasModal" data="item_<?php echo $row["id"];?>"><?php echo $row["description"];?></a></h4>
                                            <br />
                                            
                                            <input type="hidden" value="<?php echo $row["description"];?>" name="description" class="description"/>
                                            <input type="hidden" value="<?php echo $row["assumptions"];?>" name="assumptions" class="assumptions"/>
                                            <input type="hidden" value="<?php echo $row["data"];?>" name="data" class="data"/>
                                            <input type="hidden" value="<?php echo $row["conclusion"];?>" name="conclusion" class="conclusion"/>
                                            <input type="hidden" value="<?php echo $row["box"];?>" name="box" class="box"/>
                                            <input type="hidden" value="<?php echo $row["status"];?>" name="status" class="status"/>
                                            <input type="hidden" value="<?php echo $row["id"];?>" name="itemId" class="itemId"/>
                                            
                                            
                                            
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
                                    <a href="<?=BASE_URL ?>/leancanvas/editCanvasItem?type=keymetrics" class="canvasModal" id="keymetrics"><span class="iconfa iconfa-plus"></span>  Add New</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="span2 bgColumn">
                    <div class="column">
                        <h4 class="widgettitle title-primary">
                            <?php if ($_SESSION['userdata']['role'] == 'admin' || $_SESSION['userdata']['role'] == 'manager' ) { ?>
                                <a href="<?=BASE_URL ?>/setting/editBoxLabel?module=researchlabels&label=uniquevalue" class="editLabelModal editHeadline"><i class="fas fa-edit"></i></a>
                            <?php } ?>

                            <?php echo $canvasLabels["uniquevalue"]; ?>
                        </h4>
                        <div class="contentInner status_uniquevalue">
        <?php foreach($this->get('canvasItems') as $row) { ?>
            <?php if($row["box"] == "uniquevalue") {?>
                                    <div class="ticketBox" id="item_<?php echo $row["id"];?>">
                                            
                                            <div class="pull-right">
                                                <span id="status" class="f-left label label-<?php echo $row["status"];?>" style="margin-left:0px;">
                <?php 
                if($row["status"] == "danger") { echo "Not validated yet"; 
                } elseif($row["status"] == "info") { echo "Validated and it's false";
                } elseif($row["status"] == "success") { echo "Validated and it's true";
                }
                ?>
                                                </span>    
                                            </div>
                                            <h4><a href="<?=BASE_URL ?>/leancanvas/editCanvasItem/<?php echo $row["id"];?>" class="canvasModal"  data="item_<?php echo $row["id"];?>"><?php echo $row["description"];?></a></h4>
                                            <br />
                                            
                                            <input type="hidden" value="<?php echo $row["description"];?>" name="description" class="description"/>
                                            <input type="hidden" value="<?php echo $row["assumptions"];?>" name="assumptions" class="assumptions"/>
                                            <input type="hidden" value="<?php echo $row["data"];?>" name="data" class="data"/>
                                            <input type="hidden" value="<?php echo $row["conclusion"];?>" name="conclusion" class="conclusion"/>
                                            <input type="hidden" value="<?php echo $row["box"];?>" name="box" class="box"/>
                                            <input type="hidden" value="<?php echo $row["status"];?>" name="status" class="status"/>
                                            <input type="hidden" value="<?php echo $row["id"];?>" name="itemId" class="itemId"/>
                                            
                                            
                                            
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
                            <a href="<?=BASE_URL ?>/leancanvas/editCanvasItem?type=uniquevalue" class="canvasModal" id="uniquevalue"><span class="iconfa iconfa-plus"></span>  Add New</a>
                        </div>
                    </div>            
                </div>
                
                <div class="span2 bgColumn">
                    <div class="row-fluid">
                        <div class="span12">
                            <div class="column">
                                <h4 class="widgettitle title-primary">
                                    <?php if ($_SESSION['userdata']['role'] == 'admin' || $_SESSION['userdata']['role'] == 'manager' ) { ?>
                                        <a href="<?=BASE_URL ?>/setting/editBoxLabel?module=researchlabels&label=unfairadvantage" class="editLabelModal editHeadline"><i class="fas fa-edit"></i></a>
                                    <?php } ?>

                                    <?php echo $canvasLabels["unfairadvantage"]; ?>
                                </h4>
                                <div class="contentInner status_problem">
                                    <?php foreach($this->get('canvasItems') as $row) { ?>
                                        <?php if($row["box"] == "unfairadvantage") {?>
                                            <div class="ticketBox" id="item_<?php echo $row["id"];?>">
                                            
                                            <div class="pull-right">
                                                <span id="status" class="f-left label label-<?php echo $row["status"];?>" style="margin-left:0px;">
                                            <?php 
                                            if($row["status"] == "danger") { echo "Not validated yet"; 
                                            } elseif($row["status"] == "info") { echo "Validated and it's false";
                                            } elseif($row["status"] == "success") { echo "Validated and it's true";
                                            }
                                            ?>
                                                </span>    
                                            </div>
                                            <h4><a href="<?=BASE_URL ?>/leancanvas/editCanvasItem/<?php echo $row["id"];?>" class="canvasModal"  data="item_<?php echo $row["id"];?>"><?php echo $row["description"];?></a></h4>
                                            <br />
                                            
                                            <input type="hidden" value="<?php echo $row["description"];?>" name="description" class="description"/>
                                            <input type="hidden" value="<?php echo $row["assumptions"];?>" name="assumptions" class="assumptions"/>
                                            <input type="hidden" value="<?php echo $row["data"];?>" name="data" class="data"/>
                                            <input type="hidden" value="<?php echo $row["conclusion"];?>" name="conclusion" class="conclusion"/>
                                            <input type="hidden" value="<?php echo $row["box"];?>" name="box" class="box"/>
                                            <input type="hidden" value="<?php echo $row["status"];?>" name="status" class="status"/>
                                            <input type="hidden" value="<?php echo $row["id"];?>" name="itemId" class="itemId"/>
                                            
                                            
                                            
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
                                    <a href="<?=BASE_URL ?>/leancanvas/editCanvasItem?type=unfairadvantage" class="canvasModal" id="unfairadvantage"><span class="iconfa iconfa-plus"></span>  Add New</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row-fluid">
                        <div class="span12">
                            <div class="column">
                                <h4 class="widgettitle title-primary">
                                    <?php if ($_SESSION['userdata']['role'] == 'admin' || $_SESSION['userdata']['role'] == 'manager' ) { ?>
                                        <a href="<?=BASE_URL ?>/setting/editBoxLabel?module=researchlabels&label=channels" class="editLabelModal editHeadline"><i class="fas fa-edit"></i></a>
                                    <?php } ?>

                                    <?php echo $canvasLabels["channels"]; ?>
                                </h4>
                                <div class="contentInner status_problem">
                                    <?php foreach($this->get('canvasItems') as $row) { ?>
                                        <?php if($row["box"] == "channels") {?>
                                            <div class="ticketBox" id="item_<?php echo $row["id"];?>">
                                            
                                            <div class="pull-right">
                                                <span id="status" class="f-left label label-<?php echo $row["status"];?>" style="margin-left:0px;">
                                            <?php 
                                            if($row["status"] == "danger") { echo "Not validated yet"; 
                                            } elseif($row["status"] == "info") { echo "Validated and it's false";
                                            } elseif($row["status"] == "success") { echo "Validated and it's true";
                                            }
                                            ?>
                                                </span>    
                                            </div>
                                            <h4><a href="<?=BASE_URL ?>/leancanvas/editCanvasItem/<?php echo $row["id"];?>" class="canvasModal"  data="item_<?php echo $row["id"];?>"><?php echo $row["description"];?></a></h4>
                                            <br />
                                            
                                            <input type="hidden" value="<?php echo $row["description"];?>" name="description" class="description"/>
                                            <input type="hidden" value="<?php echo $row["assumptions"];?>" name="assumptions" class="assumptions"/>
                                            <input type="hidden" value="<?php echo $row["data"];?>" name="data" class="data"/>
                                            <input type="hidden" value="<?php echo $row["conclusion"];?>" name="conclusion" class="conclusion"/>
                                            <input type="hidden" value="<?php echo $row["box"];?>" name="box" class="box"/>
                                            <input type="hidden" value="<?php echo $row["status"];?>" name="status" class="status"/>
                                            <input type="hidden" value="<?php echo $row["id"];?>" name="itemId" class="itemId"/>
                                            
                                            
                                            
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
                                    <a href="<?=BASE_URL ?>/leancanvas/editCanvasItem?type=channels" class="canvasModal" id="channels"><span class="iconfa iconfa-plus"></span> Add New</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="span3 bgColumn">
                    <div class="column">
                        <h4 class="widgettitle title-primary">
                            <?php if ($_SESSION['userdata']['role'] == 'admin' || $_SESSION['userdata']['role'] == 'manager' ) { ?>
                                <a href="<?=BASE_URL ?>/setting/editBoxLabel?module=researchlabels&label=customersegment" class="editLabelModal editHeadline"><i class="fas fa-edit"></i></a>
                            <?php } ?>

                            <?php echo $canvasLabels["customersegment"]; ?>
                        </h4>
                        <div class="contentInner status_uniquevalue">
        <?php foreach($this->get('canvasItems') as $row) { ?>
            <?php if($row["box"] == "customersegment") {?>
                                        <div class="ticketBox" id="item_<?php echo $row["id"];?>">
                                            
                                            <div class="pull-right">
                                                <span id="status" class="f-left label label-<?php echo $row["status"];?>" style="margin-left:0px;">
                <?php 
                if($row["status"] == "danger") { echo "Not validated yet"; 
                } elseif($row["status"] == "info") { echo "Validated and it's false";
                } elseif($row["status"] == "success") { echo "Validated and it's true";
                }
                ?>
                                                </span>    
                                            </div>
                                            <h4><a href="<?=BASE_URL ?>/leancanvas/editCanvasItem/<?php echo $row["id"];?>" class="canvasModal"  data="item_<?php echo $row["id"];?>"><?php echo $row["description"];?></a></h4>
                                            <br />
                                            
                                            <input type="hidden" value="<?php echo $row["description"];?>" name="description" class="description"/>
                                            <input type="hidden" value="<?php echo $row["assumptions"];?>" name="assumptions" class="assumptions"/>
                                            <input type="hidden" value="<?php echo $row["data"];?>" name="data" class="data"/>
                                            <input type="hidden" value="<?php echo $row["conclusion"];?>" name="conclusion" class="conclusion"/>
                                            <input type="hidden" value="<?php echo $row["box"];?>" name="box" class="box"/>
                                            <input type="hidden" value="<?php echo $row["status"];?>" name="status" class="status"/>
                                            <input type="hidden" value="<?php echo $row["id"];?>" name="itemId" class="itemId"/>
                                            
                                            
                                            
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
                            <a href="<?=BASE_URL ?>/leancanvas/editCanvasItem?type=customersegment" class="canvasModal" id="customersegment"><span class="iconfa iconfa-plus"></span> Add New</a>
                        </div>
                    </div>        
                </div>
                
            </div>    
            
            <div class="row-fluid" style="margin-top:10px;">
                <div class="span6 bgColumn">
                    <div class="column full">
                        <h4 class="widgettitle title-primary">
                            <?php if ($_SESSION['userdata']['role'] == 'admin' || $_SESSION['userdata']['role'] == 'manager' ) { ?>
                                <a href="<?=BASE_URL ?>/setting/editBoxLabel?module=researchlabels&label=cost" class="editLabelModal editHeadline"><i class="fas fa-edit"></i></a>
                            <?php } ?>

                            <?php echo $canvasLabels["cost"]; ?>
                        </h4>
                        <div class="contentInner full status_uniquevalue">
        <?php foreach($this->get('canvasItems') as $row) { ?>
            <?php if($row["box"] == "cost") {?>
                                        <div class="ticketBox" id="item_<?php echo $row["id"];?>">
                                            
                                            <div class="pull-right">
                                                <span id="status" class="f-left label label-<?php echo $row["status"];?>" style="margin-left:0px;">
                <?php 
                if($row["status"] == "danger") { echo "Not validated yet"; 
                } elseif($row["status"] == "info") { echo "Validated and it's false";
                } elseif($row["status"] == "success") { echo "Validated and it's true";
                }
                ?>
                                                </span>    
                                            </div>
                                            <h4><a href="<?=BASE_URL ?>/leancanvas/editCanvasItem/<?php echo $row["id"];?>" class="canvasModal"  data="item_<?php echo $row["id"];?>"><?php echo $row["description"];?></a></h4>
                                            <br />
                                            
                                            <input type="hidden" value="<?php echo $row["description"];?>" name="description" class="description"/>
                                            <input type="hidden" value="<?php echo $row["assumptions"];?>" name="assumptions" class="assumptions"/>
                                            <input type="hidden" value="<?php echo $row["data"];?>" name="data" class="data"/>
                                            <input type="hidden" value="<?php echo $row["conclusion"];?>" name="conclusion" class="conclusion"/>
                                            <input type="hidden" value="<?php echo $row["box"];?>" name="box" class="box"/>
                                            <input type="hidden" value="<?php echo $row["status"];?>" name="status" class="status"/>
                                            <input type="hidden" value="<?php echo $row["id"];?>" name="itemId" class="itemId"/>
                                            
                                            
                                            
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
                            <a href="<?=BASE_URL ?>/leancanvas/editCanvasItem?type=cost" class="canvasModal" id="cost"><span class="iconfa iconfa-plus"></span> Add New</a>
                        </div>
                    </div>        
                </div>
                <div class="span6 bgColumn">
                    <div class="column full">
                        <h4 class="widgettitle title-primary">
                            <?php if ($_SESSION['userdata']['role'] == 'admin' || $_SESSION['userdata']['role'] == 'manager' ) { ?>
                                <a href="<?=BASE_URL ?>/setting/editBoxLabel?module=researchlabels&label=revenue" class="editLabelModal editHeadline"><i class="fas fa-edit"></i></a>
                            <?php } ?>

                            <?php echo $canvasLabels["revenue"]; ?>
                        </h4>
                        <div class="contentInner status_uniquevalue">
        <?php foreach($this->get('canvasItems') as $row) { ?>
            <?php if($row["box"] == "revenue") {?>
                                    <div class="ticketBox" id="item_<?php echo $row["id"];?>">
                                            
                                            <div class="pull-right">
                                                <span id="status" class="f-left label label-<?php echo $row["status"];?>" style="margin-left:0px;">
                <?php 
                if($row["status"] == "danger") { echo "Not validated yet"; 
                } elseif($row["status"] == "info") { echo "Validated and it's false";
                } elseif($row["status"] == "success") { echo "Validated and it's true";
                }
                ?>
                                                </span>    
                                            </div>
                                            <h4><a href="<?=BASE_URL ?>/leancanvas/editCanvasItem/<?php echo $row["id"];?>" class="canvasModal"  data="item_<?php echo $row["id"];?>"><?php echo $row["description"];?></a></h4>
                                            <br />
                                            
                                            <input type="hidden" value="<?php echo $row["description"];?>" name="description" class="description"/>
                                            <input type="hidden" value="<?php echo $row["assumptions"];?>" name="assumptions" class="assumptions"/>
                                            <input type="hidden" value="<?php echo $row["data"];?>" name="data" class="data"/>
                                            <input type="hidden" value="<?php echo $row["conclusion"];?>" name="conclusion" class="conclusion"/>
                                            <input type="hidden" value="<?php echo $row["box"];?>" name="box" class="box"/>
                                            <input type="hidden" value="<?php echo $row["status"];?>" name="status" class="status"/>
                                            <input type="hidden" value="<?php echo $row["id"];?>" name="itemId" class="itemId"/>
                                            
                                            
                                            
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
                            <a href="<?=BASE_URL ?>/leancanvas/editCanvasItem?type=revenue" class="canvasModal" id="revenue"><span class="iconfa iconfa-plus"></span> Add New</a>
                        </div>
                    </div>        
                </div>
            </div>    
        </div>
        <div class="clearfix"></div>

        <?php if($_SESSION['userdata']['role'] == "admin" || $_SESSION['userdata']['role'] == 'manager'){ ?>
            <br />
            <a href="<?=BASE_URL ?>/leancanvas/delCanvas/<?php echo $this->get('currentCanvas')?>" class="delete right"><i class="fa fa-trash"></i> Delete Board</a>
        <?php } ?>

    <?php } else {

        echo "Create your first canvas before you begin";

    }
    ?>


<small class="align-center">
       <br /> Lean Canvas is adapted from <a href="https://strategyzer.com/" target="_blank">Business Model Canvas</a> and is licensed under the Creative Commons Attribution-Share Alike 3.0
</small>









        <!-- Modals -->


        <div class="modal fade bs-example-modal-lg" id="addCanvas">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form action="" method="post">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h4 class="modal-title">Create a new canvas</h4>
                        </div>
                        <div class="modal-body">
                            <label>What is the name of your new product or service?</label>
                            <input type="text" name="canvastitle" placeholder="Enter a title for your new canvas"/>


                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <input type="submit"  class="btn btn-default" value="Create Canvas" name="newCanvas" />
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
        
        
        <div class="modal fade bs-example-modal-lg" id="addItem">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="" method="post">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Add Item</h4>
                      </div>
                      <div class="modal-body">
                        <input type="hidden" value="<?php echo $this->get('currentCanvas'); ?>" name="canvasId" />
                        <input type="hidden" value="" name="box" id="box"/>
                        <label>Description</label>
                        <input type="text" name="description" value="" placeholder="Describe your hypothesis.."/><br />
                        <label>Status of your hypothesis</label>
                        <select name="status">
                            <option value="danger">Not validated yet</option>
                            <option value="info">Validated and it's false</option>
                            <option value="success">Validated and it's true</option>
                        </select><br />
                        <label>Assumptions</label>
                        <textarea rows="5" cols="10" name="assumptions" class="modalTextArea" placeholder="What are your assumptions"></textarea><br />
                        <label>Data</label>
                        <textarea rows="5" cols="10" name="data" class="modalTextArea" placeholder="How do you validate your hypothesis"></textarea><br />
                        <label>Conclusion</label>
                        <textarea rows="5" cols="10" name="conclusion" class="modalTextArea" placeholder="What conclusion do you draw based on the data you collected"></textarea><br />
                        <h4 class="widgettitle title-light">Milestones</h4>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <input type="submit" class="btn btn-primary" name="addItem" value="Save" />
                      </div>
                </form>    
            </div><!-- /.modal-content -->
          </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
        
        
        
        <div class="modal fade" id="editItem">
          <div class="modal-dialog">
            <div class="modal-content">
                <form action="" method="post">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Edit Item</h4>
                      </div>
                      <div class="modal-body">
                          <input type="hidden" value="" name="itemId" class="itemId"/>
                        <input type="hidden" value="<?php echo $this->get('currentCanvas'); ?>" name="canvasId" class="canvasId"/>
                        <input type="hidden" value="" name="box" class="box"/>
                        <label>Description</label>
                        <input type="text" name="description" value="" placeholder="Describe your hypothesis.." class="description"/><br />
                        <label>Status of your hypothesis</label>
                        <select name="status" class="status">
                            <option value="danger">Not validated yet</option>
                            <option value="info">Validated and it's false</option>
                            <option value="success">Validated and it's true</option>
                        </select><br />
                        <label>Assumptions</label>
                        <textarea rows="5" cols="10" name="assumptions" class="modalTextArea assumptions" placeholder="What are your assumptions"></textarea><br />
                        <label>Data</label>
                        <textarea rows="5" cols="10" name="data" class="modalTextArea data" placeholder="How do you validate your hypothesis"></textarea><br />
                        <label>Conclusion</label>
                        <textarea rows="5" cols="10" name="conclusion" class="modalTextArea conclusion" placeholder="What conclusion do you draw based on the data you collected"></textarea><br />
                        <a href="" class="delete"><i class="fa fa-x"></i>Delete Item</a>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <input type="submit" class="btn btn-primary" name="editItem" value="Save" />
                      </div>
                </form>    
            </div><!-- /.modal-content -->
          </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
        
    </div>
</div>
        
