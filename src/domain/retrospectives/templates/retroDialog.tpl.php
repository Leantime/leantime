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
            location.href="<?=BASE_URL ?>/retrospectives/showBoards&showRetroModal=<?php echo $canvasItem['id']; ?>";
        }
    }
</script>

<div class="showDialogOnLoad" style="display:none;">

    <h4 class="widgettitle title-light"><i class="iconfa iconfa-columns"></i>
        <?php
        if($canvasItem['description'] == "") {
            echo $this->__("headlines.retrospectives");
        }else{
            $this->e($canvasItem['description']);
        } ?>

    </h4>

    <?php echo $this->displayNotification(); ?>

    <form class="retroModal" method="post" action="<?=BASE_URL ?>/retrospectives/retroDialog/<?php echo $id;?>">


        <input type="hidden" value="<?php echo $this->get('currentCanvas'); ?>" name="canvasId" />
        <input type="hidden" value="<?php echo $canvasItem['box'] ?>" name="box" id="box"/>
        <input type="hidden" value="<?php echo $id ?>" name="itemId" id="itemId"/>
        <label><?php echo $this->__("label.description") ?></label>
        <input type="text" name="description" value="<?php echo $canvasItem['description'] ?>" placeholder="<?php echo $this->__("input.placeholders.describe_situation") ?>"/><br />


        <label><?php echo $this->__("label.examples") ?></label>
        <textarea rows="3" cols="10" name="data" class="modalTextArea" placeholder="<?php echo $this->__("input.placeholders.list_examples") ?>"><?php echo $canvasItem['data'] ?></textarea><br />

        <input type="hidden" name="milestoneId" value="<?php echo $canvasItem['milestoneId'] ?>" />
        <input type="hidden" name="changeItem" value="1" />

        <?php if($id != '') {?>
            <a href="<?=BASE_URL ?>/retrospectives/delCanvasItem/<?php echo $id;?>" class="retroModal delete right"><i class="fa fa-trash"></i> <?php echo $this->__("links.delete") ?></a>
        <?php } ?>
        <input type="submit" value="<?php echo $this->__("buttons.save")?>" id="primaryCanvasSubmitButton"/>
        <input type="submit" value="<?php echo $this->__("buttons.save_and_close")?>" id="saveAndClose" onclick="leantime.retroController.setCloseModal();"/>
        <?php if($id !== '') { ?>
            <br /><br />
            <h4 class="widgettitle title-light"><span class="fas fa-map"></span> <?php echo $this->__("headlines.attached_milestone") ?></h4>

            <ul class="sortableTicketList" style="width:99%">
            <?php
            if($canvasItem['milestoneId'] == '') {


                ?>
                <li class="ui-state-default center" id="milestone_0">
                    <h4><?php echo $this->__("headlines.no_milestone_attached") ?></h4>
                    <?php echo $this->__("text.use_milestone_to_track_retro") ?><br/>
                        <div class="row" id="milestoneSelectors">
                            <div class="col-md-12">
                                <a href="javascript:void(0);" onclick="leantime.leanCanvasController.toggleMilestoneSelectors('new');"><?php echo $this->__("links.create_attach_milestone") ?></a>
                                | <a href="javascript:void(0);" onclick="leantime.leanCanvasController.toggleMilestoneSelectors('existing');"><?php echo $this->__("links.attach_existing_milestone") ?></a>

                            </div>

                        </div>
                        <div class="row" id="newMilestone" style="display:none;">
                            <div class="col-md-12">
                                <textarea name="newMilestone"></textarea><br />
                                <input type="hidden" name="type" value="milestone" />
                                <input type="hidden" name="leancanvasitemid" value="<?php echo $id; ?> " />
                                <input type="button" value="<?php echo $this->__("buttons.save")?>" onclick="jQuery('#primaryCanvasSubmitButton').click()" class="btn btn-primary" />
                                <a href="javascript:void(0);" onclick="leantime.leanCanvasController.toggleMilestoneSelectors('hide');">
                                    <i class="fas fa-times"></i> <?php echo $this->__("links.cancel") ?>
                                </a>
                            </div>
                        </div>

                        <div class="row" id="existingMilestone" style="display:none;">
                            <div class="col-md-12">
                                <select data-placeholder="Filter by Milestone..." name="existingMilestone"  class="user-select">
                                    <option value=""><?php echo $this->__("text.all_milestones")?></option>
                                    <?php foreach($this->get('milestones') as $milestoneRow){

                                            ?>

                                            <?php echo"<option value='".$milestoneRow->id."'";

                                            if(isset($searchCriteria['milestone']) && ($searchCriteria['milestone'] == $milestoneRow->id)) { echo" selected='selected' ";
                                            }

                                            echo">".$milestoneRow->headline."</option>"; ?>

                                        <?php
                                    }     ?>
                                </select>
                                <input type="hidden" name="type" value="milestone" />
                                <input type="hidden" name="leancanvasitemid" value="<?php echo $id; ?> " />
                                <input type="button" value="<?php echo $this->__("buttons.save")?>" onclick="jQuery('#primaryCanvasSubmitButton').click()" class="btn btn-primary" />
                                <a href="javascript:void(0);"  onclick="leantime.leanCanvasController.toggleMilestoneSelectors('hide');">
                                    <i class="fas fa-times"></i> <?php echo $this->__("links.cancel")?>
                                </a>
                            </div>
                        </div>

                </li>
                <?php

            }else{

                if($canvasItem['milestoneEditTo'] == "0000-00-00 00:00:00") {
                    $date = $this->__("text.no_date_defined");
                }else {
                    $date = new DateTime($canvasItem['milestoneEditTo']);
                    $date= $date->format($this->__("language.dateformat"));
                }

                ?>

                    <li class="ui-state-default" id="milestone_<?php echo $canvasItem['milestoneId']; ?>" class="leanCanvasMilestone" >
                        <div class="ticketBox fixed">

                            <div class="row">
                                <div class="col-md-8">
                                    <strong><a href="<?=BASE_URL ?>/tickets/showKanban&milestone=<?php echo $canvasItem['milestoneId'];?>" ><?php echo $canvasItem['milestoneHeadline']; ?></a></strong>
                                </div>
                                <div class="col-md-4 align-right">
                                    <a href="<?=BASE_URL ?>/retrospectives/retroDialog/<?php echo $id;?>&removeMilestone=<?php echo $canvasItem['milestoneId'];?>" class="retroModal delete"><i class="fa fa-close"></i> <?php echo $this->__("links.remove")?></a>
                                </div>
                            </div>
                            <div class="row">

                                <div class="col-md-7">
                                    <?php echo $this->__("label.due") ?>
                                    <?php echo $date; ?>
                                </div>
                                <div class="col-md-5" style="text-align:right">
                                    <?=sprintf($this->__("text.percent_complete"), $canvasItem['percentDone'])?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="progress">
                                        <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?php echo $canvasItem['percentDone']; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $canvasItem['percentDone']; ?>%">
                                            <span class="sr-only"><?=sprintf($this->__("text.percent_complete"), $canvasItem['percentDone'])?></span>
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
        $this->assign("formUrl", BASE_URL."/retrospectives/retroDialog/".$id."");
        $this->displaySubmodule('comments-generalComment');?>
    <?php } ?>
</div>
