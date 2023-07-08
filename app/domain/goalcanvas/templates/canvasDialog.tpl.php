<?php

/**
 * Dialog
 */

$canvasName = 'goal';


/**
 * canvasDialog.inc template - Generic template for comments
 *
 * Required variables:
 * - $canvasName   Name of current canvas
 */
defined('RESTRICTED') or die('Restricted access');

$canvasItem = $this->get('canvasItem');
$canvasTypes = $this->get('canvasTypes');
$hiddenStatusLabels = $this->get('statusLabels');
$statusLabels = $statusLabels ?? $hiddenStatusLabels;
$hiddenRelatesLabels = $this->get('relatesLabels');
$relatesLabels = $relatesLabels ?? $hiddenRelatesLabels;
$dataLabels = $this->get('dataLabels');

$id = "";
if (isset($canvasItem['id']) && $canvasItem['id'] != '') {
    $id = $canvasItem['id'];
}
?>

<script type="text/javascript">
    window.onload = function() {
        if (!window.jQuery) {
            //It's not a modal
            location.href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/showCanvas&showModal=<?php echo $canvasItem['id']; ?>";
        }
    }
</script>

<div style="width:1000px">

  <h1><i class="fas <?=$canvasTypes[$canvasItem['box']]['icon']; ?>"></i> <?=$canvasTypes[$canvasItem['box']]['title']; ?></h1>

    <?php echo $this->displayNotification(); ?>

    <form class="<?=$canvasName ?>CanvasModal" method="post" action="<?=BASE_URL ?>/<?=$canvasName ?>canvas/editCanvasItem/<?php echo $id;?>">

        <input type="hidden" value="<?php echo $this->get('currentCanvas'); ?>" name="canvasId" />
        <input type="hidden" value="<?php $this->e($canvasItem['box']) ?>" name="box" id="box"/>
        <input type="hidden" value="<?php echo $id ?>" name="itemId" id="itemId"/>
        <input type="hidden" name="milestoneId" value="<?php echo $canvasItem['milestoneId'] ?? '' ?>" />
        <input type="hidden" name="changeItem" value="1" />

        <div class="row">
            <div class="col-md-8">


        <label><?=$this->__("label.what_is_your_goal") ?></label>
        <input type="text" name="title" value="<?php $this->e($canvasItem['title']) ?>" placeholder="<?=$this->__('input.placeholders.describe_element') ?>" style="width:100%" /><br />






             <?php if (!empty($relatesLabels)) { ?>
            <label><?=$this->__("label.relates") ?></label>
            <select name="relates"  style="width: 50%" id="relatesCanvas">
            </select><br />
        <?php } else { ?>
            <input type="hidden" name="relates" value="<?php echo isset($canvasItem['relates']) ? $canvasItem['relates'] : array_key_first($hiddenRelatesLabels) ?>" />
        <?php } ?>
                <br />
                <h4 class="widgettitle title-light" style="margin-bottom:0px;"><i class="fa-solid fa-ranking-star"></i> <?=$this->__("Metrics") ?></h4>


                <?php $this->dispatchTplEvent('beforeMeasureGoalContainer', $canvasItem); ?>
                <div id="measureGoalContainer">
                    <label>How will you measure this objective. What metric will you be using.</label>
                    <input type="text" name="description" value="<?=$canvasItem['description'] ?>" style="width:100%"/><br />
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <label>Starting Value</label>
                        <input type="number" step="0.01" name="startValue" value="<?=$canvasItem['startValue'] ?>" style="width:100%"/>
                    </div>
                    <div class="col-md-3">
                        <label>Current Value</label>
                        <input type="number" step="0.01" name="currentValue" id="currentValueField" value="<?=$canvasItem['currentValue'] ?>"
                            <?php if($canvasItem['setting'] == 'linkAndReport') { echo "readonly='readonly'";}?>
                            <?php if($canvasItem['setting'] == 'linkAndReport') { echo "data-tippy-content='Current value calculated from child goals'";}?>
                               style="width:100%"/>
                    </div>
                    <div class="col-md-3">
                        <label>Goal Value</label>
                        <input type="number" step="0.01" name="endValue" value="<?=$canvasItem['endValue'] ?>" style="width:100%"/>
                    </div>
                    <div class="col-md-3">
                        <label>Type</label>
                        <select name="metricType">
                            <option value="number" <?php if($canvasItem['metricType'] == 'number') echo"selected"; ?>>Number</option>
                            <option value="percent" <?php if($canvasItem['metricType'] == 'percent') echo"selected"; ?>>% Percent</option>
                            <option value="currency" <?php if($canvasItem['metricType'] == 'currency') echo"selected"; ?>><?=$this->__('language.currency') ?></option>
                        </select>
                    </div>
                </div>

                <br />
                <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
                    <input type="submit" value="<?=$this->__("buttons.save") ?>" id="primaryCanvasSubmitButton"/>
                    <input type="submit" value="<?=$this->__("buttons.save_and_close") ?>" id="saveAndClose" onclick="leantime.goalCanvasController.setCloseModal();"/>
                <?php } ?>



                <?php if ($id !== '') { ?>
                    <br /><br /><br />
                    <input type="hidden" name="comment" value="1" />
                    <h4 class="widgettitle title-light"><span class="fa fa-comments"></span><?php echo $this->__('subtitles.discussion'); ?></h4>
                    <?php
                    $this->assign("formUrl", "/strategyPro/editCanvasItem/" . $id . "");
                    $this->displaySubmodule('comments-generalComment');?>
                <?php } ?>
            </div>

            <div class="col-md-4">


                <?php if (!empty($statusLabels)) { ?>
                    <label><?=$this->__("label.status") ?></label>
                    <select name="status" style="width: 50%" id="statusCanvas">
                    </select><br /><br />
                <?php } else { ?>
                    <input type="hidden" name="status" value="<?php echo isset($canvasItem['status']) ? $canvasItem['status'] : array_key_first($hiddenStatusLabels) ?>" />
                <?php } ?>








                <h4 class="widgettitle title-light" style="margin-bottom:0px;"><i class="fa-solid fa-calendar"></i> Dates</h4>

                <label>Start Date</label>
                <input type="text" value="<?=$this->getFormattedDateString($canvasItem['startDate']); ?>" name="startDate" class="dates"/>

                <label>End Date</label>
                <input type="text" value="<?=$this->getFormattedDateString($canvasItem['endDate']); ?>" name="endDate" class="dates"/>


                <?php if ($id !== '') { ?>
            <br /><br />
            <h4 class="widgettitle title-light"><span class="fas fa-map"></span> <?=$this->__("headlines.attached_milestone") ?></h4>

            <ul class="sortableTicketList" style="width: 100%">

            <?php if ($canvasItem['milestoneId'] == '') {?>
                <li class="ui-state-default center" id="milestone_0">
                    <h4><?=$this->__("headlines.no_milestone_attached") ?></h4>
                        <div class="row" id="milestoneSelectors">
                            <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
                            <div class="col-md-12">
                                <a href="javascript:void(0);" onclick="leantime.<?=$canvasName ?>CanvasController.toggleMilestoneSelectors('new');"><?=$this->__("links.create_attach_milestone") ?></a>
                                <?php if (count($this->get('milestones')) > 0) { ?>
                                    | <a href="javascript:void(0);" onclick="leantime.<?=$canvasName ?>CanvasController.toggleMilestoneSelectors('existing');"><?=$this->__("links.attach_existing_milestone") ?></a>
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
                                <input type="button" value="<?=$this->__("buttons.cancel") ?>" onclick="leantime.<?=$canvasName ?>CanvasController.toggleMilestoneSelectors('hide')" class="btn btn-primary" />
                            </div>
                        </div>

                        <div class="row" id="existingMilestone" style="display:none;">
                            <div class="col-md-12">
                                <select data-placeholder="<?=$this->__("input.placeholders.filter_by_milestone") ?>" name="existingMilestone"  class="user-select">
                                    <option value=""></option>
                                        <?php foreach ($this->get('milestones') as $milestoneRow) { ?>
                                            <?php echo"<option value='" . $milestoneRow->id . "'";

                                            if (isset($searchCriteria['milestone']) && ($searchCriteria['milestone'] == $milestoneRow->id)) {
                                                echo" selected='selected' ";
                                            }

                                            echo">" . $milestoneRow->headline . "</option>"; ?>
                                            <?php
                                        }     ?>
                                </select>
                                <input type="hidden" name="type" value="milestone" />
                                <input type="hidden" name="<?=$canvasName ?>canvasitemid" value="<?php echo $id; ?> " />
                                <input type="button" value="<?=$this->__("buttons.save") ?>" onclick="jQuery('#primaryCanvasSubmitButton').click()" class="btn btn-primary" />
                                <input type="button" value="<?=$this->__("buttons.cancel") ?>" onclick="leantime.<?=$canvasName ?>CanvasController.toggleMilestoneSelectors('hide')" class="btn btn-primary" />
                            </div>
                        </div>

                </li>
                <?php
            } else {
                if ($canvasItem['milestoneEditTo'] == "0000-00-00 00:00:00") {
                    $date = $this->__("text.no_date_defined");
                } else {
                    $date = new DateTime($canvasItem['milestoneEditTo']);
                    $date = $date->format($this->__("language.dateformat"));
                }

                ?>

                    <li class="ui-state-default" id="milestone_<?php echo $canvasItem['milestoneId']; ?>" class="<?=$canvasName ?>CanvasMilestone" >
                        <div class="ticketBox fixed">

                            <div class="row">
                                <div class="col-md-8">
                                    <strong><a href="<?=BASE_URL ?>/tickets/showKanban&milestone=<?php echo $canvasItem['milestoneId'];?>" ><?php $this->e($canvasItem['milestoneHeadline']); ?></a></strong>
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

            </div>
        </div>

        <?php if ($id != '') {?>
            <a href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/delCanvasItem/<?php echo $id;?>" class="<?=$canvasName ?>CanvasModal delete right"><i class='fa fa-trash-can'></i> <?php echo $this->__("links.delete") ?></a>
        <?php } ?>

    </form>


</div>

<script type="text/javascript">
    jQuery(document).ready(function(){

        <?php if (!empty($statusLabels)) { ?>
            new SlimSelect({
                select: '#statusCanvas',
                showSearch: false,
                valuesUseText: false,
                data: [
                    <?php foreach ($statusLabels as $key => $data) { ?>
                        <?php if ($data['active']) { ?>
                            { innerHTML: '<?php echo "<i class=\"fas fa-fw " . $data["icon"] . "\"></i>&nbsp;" . $data['title']; ?>',
                              text: "<?=$data['title'] ?>", value: "<?=$key ?>", selected: <?php echo $canvasItem['status'] == $key ? 'true' : 'false'; ?>},
                        <?php } ?>
                    <?php } ?>
                ]
            });
        <?php } ?>

        <?php if (!empty($relatesLabels)) { ?>
            new SlimSelect({
                select: '#relatesCanvas',
                showSearch: false,
                valuesUseText: false,
                data: [
                    <?php foreach ($relatesLabels as $key => $data) { ?>
                        <?php if ($data['active']) { ?>
                            { innerHTML: '<?php echo "<i class=\"fas fa-fw " . $data["icon"] . "\"></i>&nbsp;" . $data['title']; ?>',
                              text: "<?=$data['title'] ?>", value: "<?=$key ?>", selected: <?php echo $canvasItem['relates'] == $key ? 'true' : 'false'; ?>},
                        <?php } ?>
                    <?php } ?>
                ]
            });
        <?php } ?>

        leantime.generalController.initSimpleEditor();

        <?php if (!$login::userIsAtLeast($roles::$editor)) { ?>
            leantime.generalController.makeInputReadonly(".nyroModalCont");

        <?php } ?>

        <?php if ($login::userHasRole([$roles::$commenter])) { ?>
            leantime.generalController.enableCommenterForms();
        <?php }?>

    })
</script>

