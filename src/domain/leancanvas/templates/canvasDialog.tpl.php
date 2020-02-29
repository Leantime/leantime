<?php
$canvasItem = $this->get('canvasItem');
$canvasTypes = $this->get('canvasTypes');

$id = "";
if(isset($canvasItem['id']) && $canvasItem['id'] != '') {$id = $canvasItem['id'];
}
?>

<script type="text/javascript">
    window.onload = function() {
        if (!window.jQuery) {
            //It's not a modal
            location.href="<?=BASE_URL ?>/leancanvas/simpleCanvas&showModal=<?php echo $canvasItem['id']; ?>";
        }
    }
</script>

<div class="showDialogOnLoad" style="display:none;">

    <h4 class="widgettitle title-light"><i class="iconfa iconfa-columns"></i> <?php echo $canvasTypes[$canvasItem['box']]; ?> <?php echo $canvasItem['description']; ?></h4>

    <?php echo $this->displayNotification(); ?>

    <form class="canvasModal" method="post" action="<?=BASE_URL ?>/leancanvas/editCanvasItem/<?php echo $id;?>">


        <input type="hidden" value="<?php echo $this->get('currentCanvas'); ?>" name="canvasId" />
        <input type="hidden" value="<?php echo $canvasItem['box'] ?>" name="box" id="box"/>
        <input type="hidden" value="<?php echo $id ?>" name="itemId" id="itemId"/>
        <label>Description</label>
        <input type="text" name="description" value="<?php echo $canvasItem['description'] ?>" placeholder="Describe your hypothesis.." style="width:100%"/><br />
        <label>Status of your hypothesis</label>
        <select name="status">
            <option value="danger" <?php if($canvasItem['status'] == 'danger') {echo"selected='selected' ";
                                    }?>>Not validated yet</option>
            <option value="info" <?php if($canvasItem['status'] == 'info') {echo"selected='selected' ";
                                    }?>>Validated and it's false</option>
            <option value="success" <?php if($canvasItem['status'] == 'success') {echo"selected='selected' ";
                                    }?>>Validated and it's true</option>
        </select><br />
        <label>Assumptions</label>
        <textarea rows="3" cols="10" name="assumptions" class="modalTextArea" placeholder="What are your assumptions"><?php echo $canvasItem['assumptions'] ?></textarea><br />
        <label>Data</label>
        <textarea rows="3" cols="10" name="data" class="modalTextArea" placeholder="How do you validate your hypothesis"><?php echo $canvasItem['data'] ?></textarea><br />
        <label>Conclusion</label>
        <textarea rows="3" cols="10" name="conclusion" class="modalTextArea" placeholder="What conclusion do you draw based on the data you collected"><?php echo $canvasItem['conclusion'] ?></textarea><br />
        <input type="hidden" name="milestoneId" value="<?php echo $canvasItem['milestoneId'] ?>" />
        <input type="hidden" name="changeItem" value="1" />

        <?php if($id != '') {?>
            <a href="<?=BASE_URL ?>/leancanvas/delCanvasItem/<?php echo $id;?>" class="canvasModal delete right"><i class="fa fa-trash"></i> Delete <?php echo $canvasTypes[$canvasItem['box']]; ?></a>
        <?php } ?>
        <input type="submit" value="Save" id="primaryCanvasSubmitButton"/>
        <input type="submit" value="Save & Close" id="saveAndClose" onclick="leantime.leanCanvasController.setCloseModal();"/>
        <?php if($id !== '') { ?>
            <br /><br />
            <h4 class="widgettitle title-light"><span class="fas fa-map"></span> Attached Milestone</h4>



            <ul class="sortableTicketList" style="width:99%">
            <?php
            if($canvasItem['milestoneId'] == '') {


                ?>
                <li class="ui-state-default center" id="milestone_0">
                    <h4>You don't have a milestone attached!</h4>
                    Use a milestone to track progress towards your Lean Canvas.<br />
                        <div class="row" id="milestoneSelectors">
                            <div class="col-md-12">
                                <a href="javascript:void(0);" onclick="leantime.leanCanvasController.toggleMilestoneSelectors('new');">Create and attach a new Milestone</a>
                                | <a href="javascript:void(0);" onclick="leantime.leanCanvasController.toggleMilestoneSelectors('existing');">Attach an existing Milestone</a>

                            </div>

                        </div>
                        <div class="row" id="newMilestone" style="display:none;">
                            <div class="col-md-12">
                                <textarea name="newMilestone"></textarea><br />
                                <input type="hidden" name="type" value="milestone" />
                                <input type="hidden" name="leancanvasitemid" value="<?php echo $id; ?> " />
                                <input type="button" value="Save" onclick="jQuery('#primaryCanvasSubmitButton').click()" class="btn btn-primary" />
                                <a href="javascript:void(0);" onclick="leantime.leanCanvasController.toggleMilestoneSelectors('hide');">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </div>

                        <div class="row" id="existingMilestone" style="display:none;">
                            <div class="col-md-12">
                                <select data-placeholder="Filter by Milestone..." name="existingMilestone"  class="user-select">
                                    <option value="">All Milestones</option>
                                    <?php foreach($this->get('milestones') as $milestoneRow){
                                        if($milestoneRow->leanCanvasId == '') {
                                            ?>

                                            <?php echo"<option value='".$milestoneRow->id."'";

                                            if(isset($searchCriteria['milestone']) && ($searchCriteria['milestone'] == $milestoneRow->id)) { echo" selected='selected' ";
                                            }

                                            echo">".$milestoneRow->headline."</option>"; ?>

                                        <?php }
                                    }     ?>
                                </select>
                                <input type="hidden" name="type" value="milestone" />
                                <input type="hidden" name="leancanvasitemid" value="<?php echo $id; ?> " />
                                <input type="button" value="Save" onclick="jQuery('#primaryCanvasSubmitButton').click()" class="btn btn-primary" />
                                <a href="javascript:void(0);"  onclick="leantime.leanCanvasController.toggleMilestoneSelectors('hide');">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </div>

                </li>
                <?php

            }else{

                if($canvasItem['milestoneEditTo'] == "0000-00-00 00:00:00") {
                    $date = "No Date defined";
                }else {
                    $date = new DateTime($canvasItem['milestoneEditTo']);
                    $date= $date->format("m/d/Y");
                }

                ?>

                    <li class="ui-state-default" id="milestone_<?php echo $canvasItem['milestoneId']; ?>" class="leanCanvasMilestone" >
                        <div class="ticketBox fixed">

                            <div class="row">
                                <div class="col-md-8">
                                    <strong><a href="<?=BASE_URL ?>/tickets/showKanban&milestone=<?php echo $canvasItem['milestoneId'];?>" ><?php echo $canvasItem['milestoneHeadline']; ?></a></strong>
                                </div>
                                <div class="col-md-4 align-right">
                                    <a href="<?=BASE_URL ?>/leancanvas/editCanvasItem/<?php echo $id;?>&removeMilestone=<?php echo $canvasItem['milestoneId'];?>" class="canvasModal delete"><i class="fa fa-close"></i> Remove</a>
                                </div>
                            </div>
                            <div class="row">

                                <div class="col-md-7">
                                    Due By:
                                    <?php echo $date; ?>
                                </div>
                                <div class="col-md-5" style="text-align:right">
                                    <?php echo $canvasItem['percentDone']; ?>% Complete
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="progress">
                                        <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?php echo $canvasItem['percentDone']; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $canvasItem['percentDone']; ?>%">
                                            <span class="sr-only"><?php echo $canvasItem['percentDone']; ?>% Complete</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
            <?php } ?>

        </ul>

        <?php } ?>

    </form>

    <?php if($id !== '') { ?>
    <br />
    <input type="hidden" name="comment" value="1" />

        <?php
        $this->assign("formUrl", "/leancanvas/editCanvasItem/".$id."");
        $this->displaySubmodule('comments-generalComment');?>
    <?php } ?>
</div>