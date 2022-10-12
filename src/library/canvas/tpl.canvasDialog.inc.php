<?php
/**
 * Generic template for comments
 *
 * Required variables:
 * - $canvasName   Name of current canvas
 * - $canvasTemplate Template of current canvas
 * - $options      Options that override defaut layout (see $params below for defaults)
 */
defined('RESTRICTED') or die('Restricted access');

$canvasItem = $this->get('canvasItem');
$canvasTypes = $this->get('canvasTypes');
$canvasTemplate = $canvasTemplate ?? '';

// $statusDropdown: Default HTML code of status dropdown
$statusDropdown = 
	'<option value="info" '.($canvasItem['status'] == 'info' ? "selected='selected' " : '').'>'.
	$this->__("status.not_validated").'</option>'."\n".
	'<option value="success" '.($canvasItem['status'] == 'success' ? "selected='selected' " : '').'>'.
	$this->__("status.validated_false").'</option>'."\n".
	'<option value="danger" '.($canvasItem['status'] == 'danger' ? "selected='selected' " : '').'>'.
	$this->__("status.validated_true").'</option>'."\n";

// Only field changed need to be overwritten. XXXData must always contain assumptions, data, and conclusion (order is irrelevant)
// If 'XXXTitle' is false, field is not displayed
$params = [ 'elementTitle' => "label.title_element", 'elementPlaceholder' => "input.placeholders.describe_title",
			'statusTitle'  => "label.status",        'statusDropdown'     => $statusDropdown,
			'firstTitle'   => 'label.assumptions',   'firstPlaceholder'   => 'input.placeholders.describe_assumption', 'firstData'  => 'assumptions',
			'secondTitle'  => 'label.data',          'secondPlaceholder'  => 'input.placeholders.describe_data',       'secondData' => 'data',
			'thirdTitle'   => 'label.conclusion', '   thirdPlaceholder'   => 'input.placeholders.describe_conclusion', 'thirdData'  => 'conclusion'
			];
if(isset($options) && is_array($options)) {
	foreach($options as $key => $value) {
		$params[$key] = $value;
	}
}

$id = "";
if (isset($canvasItem['id']) && $canvasItem['id'] != '') {
    $id = $canvasItem['id'];
}
?>

<script type="text/javascript">
    window.onload = function() {
        if (!window.jQuery) {
            //It's not a modal
            location.href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/<?=$canvasTemplate.$canvasName ?>Canvas&showModal=<?php echo $canvasItem['id']; ?>";
        }
    }
</script>

<div class="showDialogOnLoad" style="display:none;">

  <h4 class="widgettitle title-light" style="padding-bottom: 0"><?php echo $canvasTypes[$canvasItem['box']]; ?></h4>
  <hr style="margin-top: 5px; margin-bottom: 15px;">
    <?php echo $this->displayNotification(); ?>

    <form class="<?=$canvasName ?>CanvasModal" method="post" action="<?=BASE_URL ?>/<?=$canvasName ?>canvas/editCanvasItem/<?php echo $id;?>">

        <input type="hidden" value="<?php echo $this->get('currentCanvas'); ?>" name="canvasId" />
        <input type="hidden" value="<?php $this->e($canvasItem['box']) ?>" name="box" id="box"/>
        <input type="hidden" value="<?php echo $id ?>" name="itemId" id="itemId"/>

	    <?php if($params['elementTitle'] !== false) { ?>
	        <label><?=$this->__($params['elementTitle']) ?></label>
            <input type="text" name="description" value="<?php $this->e($canvasItem['description']) ?>"
	            placeholder="<?=$this->__($params['elementPlaceholder']) ?>" style="width:100%"/><br />
		<?php } ?>

	    <?php if($params['statusTitle'] !== false) { ?>
	        <label><?=$this->__($params['statusTitle']) ?></label>
            <select name="status"><?=$params['statusDropdown'] ?></select><br />
		<?php } else { ?>
            <input type="hidden" name="status" value="" />
		<?php } ?>

	    <?php if($params['firstTitle'] !== false) { ?>
          <label><?=$this->__($params['firstTitle']) ?></label>
          <textarea style="width:100%" rows="3" cols="10" name="<?=$params['firstData'] ?>" class="modalTextArea tinymceSimple"
		      placeholder="<?=$this->__($params['firstPlaceholder']) ?>"><?=$canvasItem[$params['firstData']] ?></textarea><br />
		<?php } else { ?>
            <input type="hidden" name="<?=$params['firstData'] ?>" value="" />
		<?php } ?>

	    <?php if($params['secondTitle'] !== false) { ?>
          <label><?=$this->__($params['secondTitle']) ?></label>
          <textarea style="width:100%" rows="3" cols="10" name="<?=$params['secondData'] ?>" class="modalTextArea tinymceSimple"
		      placeholder="<?=$this->__($params['secondPlaceholder']) ?>"><?=$canvasItem[$params['secondData']] ?></textarea><br />
		<?php } else { ?>
            <input type="hidden" name="<?=$params['secondData'] ?>" value="" />
		<?php } ?>

	    <?php if($params['thirdTitle'] !== false) { ?>
          <label><?=$this->__($params['thirdTitle']) ?></label>
          <textarea style="width:100%" rows="3" cols="10" name="<?=$params['thirdData'] ?>" class="modalTextArea tinymceSimple"
		      placeholder="<?=$this->__($params['thirdPlaceholder']) ?>"><?=$canvasItem[$params['thirdData']] ?></textarea><br />
		<?php } else { ?>
            <input type="hidden" name="<?=$params['thirdData'] ?>" value="" />
		<?php } ?>
		
        <input type="hidden" name="milestoneId" value="<?php echo $canvasItem['milestoneId'] ?>" />
        <input type="hidden" name="changeItem" value="1" />

        <?php if($id != '') {?>
            <a href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/delCanvasItem/<?php echo $id;?>" class="<?=$canvasName ?>CanvasModal delete right"
			    ><i class='fa fa-trash-can'></i> <?php echo $this->__("links.delete") ?></a>
        <?php } ?>
								
        <?php if($login::userIsAtLeast($roles::$editor)) { ?>
            <input type="submit" value="<?=$this->__("buttons.save") ?>" id="primaryCanvasSubmitButton"/>
            <input type="submit" value="<?=$this->__("buttons.save_and_close") ?>" id="saveAndClose" onclick="leantime.<?=$canvasName ?>canvasController.setCloseModal();"/>
        <?php } ?>

        <?php if($id !== '') { ?>
            <br /><br />
            <h4 class="widgettitle title-light"><span class="fas fa-map"></span> <?=$this->__("headlines.attached_milestone") ?></h4>

            <ul class="sortableTicketList" style="width:99%">
            <?php
            if($canvasItem['milestoneId'] == '') {

                ?>
                <li class="ui-state-default center" id="milestone_0">
                    <h4><?=$this->__("headlines.no_milestone_attached") ?></h4>
                        <div class="row" id="milestoneSelectors">
                            <?php if($login::userIsAtLeast($roles::$editor)) { ?>
                            <div class="col-md-12">
                                <a href="javascript:void(0);" onclick="leantime.<?=$canvasName ?>canvasController.toggleMilestoneSelectors('new');"><?=$this->__("links.create_attach_milestone") ?></a>
                            <?php if(count($this->get('milestones')) > 0) { ?>
                                    | <a href="javascript:void(0);" onclick="leantime.<?=$canvasName ?>canvasController.toggleMilestoneSelectors('existing');"><?=$this->__("links.attach_existing_milestone") ?></a>
                                <?php } ?>
                             </div>
                            <?php } ?>
                        </div>
                        <div class="row" id="newMilestone" style="display:none;">
                            <div class="col-md-12">
                                <input type="text" width="50%" name="newMilestone"></textarea><br />
                                <input type="hidden" name="type" value="milestone" />
                                <input type="hidden" name="<?=$canvasName ?>canvasitemid" value="<?php echo $id; ?> " />
                                <input type="button" value="<?=$this->__("buttons.save") ?>" onclick="jQuery('#primaryCanvasSubmitButton').click()" class="btn btn-primary" />
                                <input type="button" value="<?=$this->__("buttons.cancel") ?>" onclick="leantime.<?=$canvasName ?>canvasController.toggleMilestoneSelectors('hide')" class="btn btn-primary" />
                            </div>
                        </div>

                        <div class="row" id="existingMilestone" style="display:none;">
                            <div class="col-md-12">
                                <select data-placeholder="<?=$this->__("input.placeholders.filter_by_milestone") ?>" name="existingMilestone"  class="user-select">
                                    <option value=""></option>
                                        <?php foreach($this->get('milestones') as $milestoneRow) { ?>

                                            <?php echo"<option value='".$milestoneRow->id."'";

                                            if(isset($searchCriteria['milestone']) && ($searchCriteria['milestone'] == $milestoneRow->id)) { echo" selected='selected' "; }

                                            echo">".$milestoneRow->headline."</option>"; ?>
                                        <?php
                                    }     ?>
                                </select>
                                <input type="hidden" name="type" value="milestone" />
                                <input type="hidden" name="<?=$canvasName ?>canvasitemid" value="<?php echo $id; ?> " />
                                <input type="button" value="<?=$this->__("buttons.save") ?>" onclick="jQuery('#primaryCanvasSubmitButton').click()" class="btn btn-primary" />
                                <input type="button" value="<?=$this->__("buttons.cancel") ?>" onclick="leantime.<?=$canvasName ?>canvasController.toggleMilestoneSelectors('hide')" class="btn btn-primary" />
                            </div>
                        </div>

                </li>
                <?php

            } else {

                if($canvasItem['milestoneEditTo'] == "0000-00-00 00:00:00") {
                    $date = $this->__("text.no_date_defined");
                } else {
                    $date = new DateTime($canvasItem['milestoneEditTo']);
                    $date= $date->format($this->__("language.dateformat"));
                }

                ?>

                    <li class="ui-state-default" id="milestone_<?php echo $canvasItem['milestoneId']; ?>" class="<?=$canvasName ?>canvasCanvasMilestone" >
                        <div class="ticketBox fixed">

                            <div class="row">
                                <div class="col-md-8">
                                    <strong><a href="<?=BASE_URL ?>/tickets/showKanban&milestone=<?php echo $canvasItem['milestoneId'];?>" ><?php echo $canvasItem['milestoneHeadline']; ?></a></strong>
                                </div>
                                <div class="col-md-4 align-right">
                                    <a href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/editCanvasItem/<?php echo $id;?>&removeMilestone=<?php echo $canvasItem['milestoneId'];?>" class="<?=$canvasName ?>CanvasModal delete"><i class="fa fa-close"></i> <?=$this->__("links.remove") ?></a>
                                </div>
                            </div>
                            <div class="row">

                                <div class="col-md-7">
                                    <?=$this->__("label.due") ?>
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
        <h4 class="widgettitle title-light"><span class="fa fa-comments"></span><?php echo $this->__('subtitles.discussion'); ?></h4>
        <?php
        $this->assign("formUrl", "/<?=$canvasName ?>canvas/editCanvasItem/".$id."");
        $this->displaySubmodule('comments-generalComment');?>
    <?php } ?>
</div>

<script type="text/javascript">
    jQuery(document).ready(function(){

        leantime.generalController.initSimpleEditor();

        <?php if(!$login::userIsAtLeast($roles::$editor)) { ?>

            leantime.generalController.makeInputReadonly(".nyroModalCont");

        <?php } ?>

        <?php if($login::userHasRole([$roles::$commenter])) { ?>
            leantime.generalController.enableCommenterForms();
        <?php }?>

    })
</script>
