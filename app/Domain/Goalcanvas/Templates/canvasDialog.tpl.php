<?php

/**
 * Dialog
 */

foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$canvasName = 'goal';


/**
 * canvasDialog.inc template - Generic template for comments
 *
 * Required variables:
 * - $canvasName   Name of current canvas
 */
defined('RESTRICTED') or die('Restricted access');

$canvasItem = $tpl->get('canvasItem');
$canvasTypes = $tpl->get('canvasTypes');
$hiddenStatusLabels = $tpl->get('statusLabels');
$statusLabels = $statusLabels ?? $hiddenStatusLabels;
$hiddenRelatesLabels = $tpl->get('relatesLabels');
$relatesLabels = $relatesLabels ?? $hiddenRelatesLabels;
$dataLabels = $tpl->get('dataLabels');

$id = "";
if (isset($canvasItem['id']) && $canvasItem['id'] != '') {
    $id = $canvasItem['id'];
}

$currentCanvas = $tpl->get('currentCanvas');

if (isset($_GET['canvasId'])) {
    $currentCanvas = (int)$_GET['canvasId'];
}
?>

<script type="text/javascript">
    window.onload = function() {
        if (!window.jQuery) {
            //It's not a modal
            location.href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/showCanvas?showModal=<?php echo $canvasItem['id']; ?>";
        }
    }
</script>

<div style="width:1000px">

    <h1><i class="fas <?=$canvasTypes[$canvasItem['box']]['icon']; ?>"></i> <?=$canvasTypes[$canvasItem['box']]['title']; ?></h1>

    <?php echo $tpl->displayNotification(); ?>

    <form class="formModal" method="post" action="<?=BASE_URL ?>/<?=$canvasName ?>canvas/editCanvasItem/<?php echo $id;?>">

        <input type="hidden" value="<?php echo $currentCanvas ?>" name="canvasId" />
        <input type="hidden" value="<?php $tpl->e($canvasItem['box']) ?>" name="box" id="box"/>
        <input type="hidden" value="<?php echo $id ?>" name="itemId" id="itemId"/>
        <input type="hidden" name="milestoneId" value="<?php echo $canvasItem['milestoneId'] ?? '' ?>" />
        <input type="hidden" name="changeItem" value="1" />

        <div class="row">
            <div class="col-md-8">


                <label><?=$tpl->__("label.what_is_your_goal") ?></label>
                <input type="text" name="title" value="<?php $tpl->e($canvasItem['title']) ?>"  style="width:100%" /><br />

                <?php if (!empty($relatesLabels)) { ?>
                    <label><?=$tpl->__("label.relates") ?></label>
                    <select name="relates"  style="width: 50%" id="relatesCanvas">
                    </select><br />
                <?php } else { ?>
                    <input type="hidden" name="relates" value="<?php echo $canvasItem['relates'] ?? array_key_first(
                        $hiddenRelatesLabels
                    ) ?>" />
                <?php } ?>
                <br />
                <h4 class="widgettitle title-light" style="margin-bottom:0px;"><i class="fa-solid fa-ranking-star"></i> <?=$tpl->__("Metrics") ?></h4>


                <?php $tpl->dispatchTplEvent('beforeMeasureGoalContainer', $canvasItem); ?>
                <div id="measureGoalContainer">
                    <label>How will you measure this objective. What metric will you be using.</label>
                    <input type="text" name="description" value="<?=$canvasItem['description'] ?>" style="width:100%"/><br />
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <label>Starting Value</label>
                        <input type="number" step="0.01" name="startValue" value="<?=$canvasItem['startValue'] ?>" style="width:105px"/>
                    </div>
                    <div class="col-md-3">
                        <label>Current Value</label>
                        <input type="number" step="0.01" name="currentValue" id="currentValueField" value="<?=$canvasItem['currentValue'] ?>"
                            <?php if ($canvasItem['setting'] == 'linkAndReport') {
                                echo "readonly='readonly'";
                            }?>
                            <?php if ($canvasItem['setting'] == 'linkAndReport') {
                                echo "data-tippy-content='Current value calculated from child goals'";
                            }?>
                               style="width:105px"/>
                    </div>
                    <div class="col-md-3">
                        <label>Goal Value</label>
                        <input type="number" step="0.01" name="endValue" value="<?=$canvasItem['endValue'] ?>" style="width:105px"/>
                    </div>
                    <div class="col-md-3">
                        <label>Type</label>
                        <select name="metricType">
                            <option value="number" <?php if ($canvasItem['metricType'] == 'number') {
                                echo"selected";
                                                   } ?>>Number</option>
                            <option value="percent" <?php if ($canvasItem['metricType'] == 'percent') {
                                echo"selected";
                                                    } ?>>% Percent</option>
                            <option value="currency" <?php if ($canvasItem['metricType'] == 'currency') {
                                echo"selected";
                                                     } ?>><?=$tpl->__('language.currency') ?></option>
                        </select>
                    </div>
                </div>

                <br />
                <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
                    <input type="submit" value="<?=$tpl->__("buttons.save") ?>" id="primaryCanvasSubmitButton"/>
                    <button type="submit"  class="btn btn-primary" id="saveAndClose" value="closeModal" onclick="leantime.goalCanvasController.setCloseModal();"><?=$tpl->__("buttons.save_and_close") ?></button>
                <?php } ?>



                <?php if ($id !== '') { ?>
                    <br /><br /><br />
                    <input type="hidden" name="comment" value="1" />
                    <h4 class="widgettitle title-light"><span class="fa fa-comments"></span><?php echo $tpl->__('subtitles.discussion'); ?></h4>
                    <?php
                    $tpl->assign("formUrl", "/strategyPro/editCanvasItem/" . $id . "");
                    $tpl->displaySubmodule('comments-generalComment');?>
                <?php } ?>
            </div>

            <div class="col-md-4">


                <?php if (!empty($statusLabels)) { ?>
                    <label><?=$tpl->__("label.status") ?></label>
                    <select name="status" style="width: 50%" id="statusCanvas">
                    </select><br /><br />
                <?php } else { ?>
                    <input type="hidden" name="status" value="<?php echo $canvasItem['status'] ?? array_key_first(
                        $hiddenStatusLabels
                    ) ?>" />
                <?php } ?>








                <h4 class="widgettitle title-light" style="margin-bottom:0px;"><i class="fa-solid fa-calendar"></i> Dates</h4>

                <label>Start Date</label>
                <input type="text" autocomplete="off" value="<?=format($canvasItem['startDate'])->date(); ?>" name="startDate" class="startDate"/>

                <label>End Date</label>
                <input type="text" autocomplete="off" value="<?=format($canvasItem['endDate'])->date(); ?>" name="endDate" class="endDate"/>


                <?php if ($id !== '') { ?>
                    <br /><br />
                    <h4 class="widgettitle title-light"><span class="fa fa-link"></span> <?=$tpl->__("headlines.linked_milestone") ?> <i class="fa fa-question-circle-o helperTooltip" data-tippy-content="<?=$tpl->__("tooltip.link_milestones_tooltip") ?>"></i></h4>



                        <?php if ($canvasItem['milestoneId'] == '') {?>
                                <center>
                                    <h4><?=$tpl->__("headlines.no_milestone_link") ?></h4>
                                    <div class="row" id="milestoneSelectors">
                                        <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
                                            <div class="col-md-12">
                                                <a href="javascript:void(0);" onclick="leantime.<?=$canvasName ?>CanvasController.toggleMilestoneSelectors('new');"><?=$tpl->__("links.create_link_milestone") ?></a>
                                                <?php if (count($tpl->get('milestones')) > 0) { ?>
                                                    | <a href="javascript:void(0);" onclick="leantime.<?=$canvasName ?>CanvasController.toggleMilestoneSelectors('existing');"><?=$tpl->__("links.link_existing_milestone") ?></a>
                                                <?php } ?>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <div class="row" id="newMilestone" style="display:none;">
                                        <div class="col-md-12">
                                            <input type="text" width="50%" name="newMilestone"></textarea><br />
                                            <input type="hidden" name="type" value="milestone" />
                                            <input type="hidden" name="<?=$canvasName ?>canvasitemid" value="<?php echo $id; ?> " />
                                            <input type="button" value="<?=$tpl->__("buttons.save") ?>" onclick="jQuery('#primaryCanvasSubmitButton').click()" class="btn btn-primary" />
                                            <input type="button" value="<?=$tpl->__("buttons.cancel") ?>" onclick="leantime.<?=$canvasName ?>CanvasController.toggleMilestoneSelectors('hide')" class="btn btn-primary" />
                                        </div>
                                    </div>

                                    <div class="row" id="existingMilestone" style="display:none;">
                                        <div class="col-md-12">
                                            <select data-placeholder="<?=$tpl->__("input.placeholders.filter_by_milestone") ?>" name="existingMilestone"  class="user-select">
                                                <option value=""></option>
                                                <?php foreach ($tpl->get('milestones') as $milestoneRow) { ?>
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
                                            <input type="button" value="<?=$tpl->__("buttons.save") ?>" onclick="jQuery('#primaryCanvasSubmitButton').click()" class="btn btn-primary" />
                                            <input type="button" value="<?=$tpl->__("buttons.cancel") ?>" onclick="leantime.<?=$canvasName ?>CanvasController.toggleMilestoneSelectors('hide')" class="btn btn-primary" />
                                        </div>
                                    </div>

                                </center>

                        <?php } else { ?>


                                <div hx-trigger="load"
                                     hx-indicator=".htmx-indicator"
                                     hx-get="<?=BASE_URL ?>/hx/tickets/milestones/showCard?milestoneId=<?=$canvasItem['milestoneId'] ?>">
                                    <div class="htmx-indicator">
                                        <?=$tpl->__("label.loading_milestone") ?>
                                    </div>
                                </div>
                                <a href="<?=CURRENT_URL ?>?removeMilestone=<?php echo $canvasItem['milestoneId'];?>" class="<?=$canvasName ?>CanvasModal delete formModal"><i class="fa fa-close"></i> <?=$tpl->__("links.remove") ?></a>


                        <?php } ?>



                <?php } ?>

            </div>
        </div>

        <?php if ($id != '') {?>
            <a href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/delCanvasItem/<?php echo $id;?>" class="formModal delete right"><i class='fa fa-trash-can'></i> <?php echo $tpl->__("links.delete") ?></a>
        <?php } ?>

    </form>


</div>

<script type="text/javascript">
    jQuery(document).ready(function(){

        leantime.dateController.initDateRangePicker(".startDate", ".endDate");


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

        leantime.editorController.initSimpleEditor();

        <?php if (!$login::userIsAtLeast($roles::$editor)) { ?>
        leantime.authController.makeInputReadonly(".nyroModalCont");

        <?php } ?>

        <?php if ($login::userHasRole([$roles::$commenter])) { ?>
        leantime.commentsController.enableCommenterForms();
        <?php }?>

    })
</script>
