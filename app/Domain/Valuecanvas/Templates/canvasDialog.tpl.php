<?php

/**
 * Dialog
 */

foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$canvasName = 'value';


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
?>

<script type="text/javascript">
    window.onload = function() {
        if (!window.jQuery) {
            //It's not a modal
            location.href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/showCanvas?showModal=<?php echo $canvasItem['id']; ?>";
        }
    }
</script>

<div class="" style="width:900px;">

  <h4 class="widgettitle title-light" style="padding-bottom: 0"><i class="fas <?=$canvasTypes[$canvasItem['box']]['icon']; ?>"></i> <?=$canvasTypes[$canvasItem['box']]['title']; ?></h4>
  <hr style="margin-top: 5px; margin-bottom: 15px;">
    <?php echo $tpl->displayNotification(); ?>

    <form class="formModal" method="post" action="<?=BASE_URL ?>/<?=$canvasName ?>canvas/editCanvasItem/<?php echo $id;?>">

        <input type="hidden" value="<?php echo $tpl->get('currentCanvas'); ?>" name="canvasId" />
        <input type="hidden" value="<?php $tpl->e($canvasItem['box']) ?>" name="box" id="box"/>
        <input type="hidden" value="<?php echo $id ?>" name="itemId" id="itemId"/>

        <label><?=$tpl->__("label.description") ?></label>
        <input type="text" name="description" value="<?php $tpl->e($canvasItem['description']) ?>" style="width:100%" /><br />

        <?php if (!empty($statusLabels)) { ?>
            <label><?=$tpl->__("label.status") ?></label>
            <select name="status" style="width: 50%" id="statusCanvas">
            </select><br /><br />
        <?php } else { ?>
            <input type="hidden" name="status" value="<?php echo $canvasItem['status'] ?? array_key_first(
                $hiddenStatusLabels
            ) ?>" />
        <?php } ?>

        <?php if (!empty($relatesLabels)) { ?>
            <label><?=$tpl->__("label.relates") ?></label>
            <select name="relates"  style="width: 50%" id="relatesCanvas">
            </select><br />
        <?php } else { ?>
            <input type="hidden" name="relates" value="<?php echo $canvasItem['relates'] ?? array_key_first(
                $hiddenRelatesLabels
            ) ?>" />
        <?php } ?>

        <?php if ($dataLabels[1]['active']) { ?>
          <label><?=$tpl->__($dataLabels[1]['title'] . "." . $tpl->escape($canvasItem['box'])) ?></label>
            <?php if (isset($dataLabels[1]['type']) && $dataLabels[1]['type'] == 'int') { ?>
                <input type="number" name="<?=$dataLabels[1]['field'] ?>" value="<?=$canvasItem[$dataLabels[1]['field']] ?>"/><br />
            <?php } elseif (isset($dataLabels[1]['type']) && $dataLabels[1]['type'] == 'string') { ?>
                <input type="text" name="<?=$dataLabels[1]['field'] ?>" value="<?=$canvasItem[$dataLabels[1]['field']] ?>" style="width:100%"/><br />
            <?php } else { ?>
                <textarea style="width:100%" rows="3" cols="10" name="<?=$dataLabels[1]['field'] ?>" class="modalTextArea tinymceSimple"><?=$canvasItem[$dataLabels[1]['field']] ?></textarea><br />
            <?php } ?>
        <?php } else { ?>
            <input type="hidden" name="<?=$dataLabels[1]['field'] ?>" value="" />
        <?php } ?>

        <?php if ($dataLabels[2]['active']) { ?>
          <label><?=$tpl->__($dataLabels[2]['title'] . "." . $tpl->escape($canvasItem['box'])) ?></label>
            <?php if (isset($dataLabels[2]['type']) && $dataLabels[2]['type'] == 'int') { ?>
                <input type="number" name="<?=$dataLabels[2]['field'] ?>" value="<?=$canvasItem[$dataLabels[2]['field']] ?>"/><br />
            <?php } elseif (isset($dataLabels[2]['type']) && $dataLabels[2]['type'] == 'string') { ?>
                <input type="text" name="<?=$dataLabels[2]['field'] ?>" value="<?=$canvasItem[$dataLabels[2]['field']] ?>" style="width:100%"/><br />
            <?php } else { ?>
                <textarea style="width:100%" rows="3" cols="10" name="<?=$dataLabels[2]['field'] ?>" class="modalTextArea tinymceSimple"><?=$canvasItem[$dataLabels[2]['field']] ?></textarea><br />
            <?php } ?>
        <?php } else { ?>
            <input type="hidden" name="<?=$dataLabels[2]['field'] ?>" value="" />
        <?php } ?>

        <?php if ($dataLabels[3]['active']) { ?>
          <label><?=$tpl->__($dataLabels[3]['title'] . "." . $tpl->escape($canvasItem['box'])) ?></label>
            <?php if (isset($dataLabels[3]['type']) && $dataLabels[3]['type'] == 'int') { ?>
                <input type="number" name="<?=$dataLabels[3]['field'] ?>" value="<?=$canvasItem[$dataLabels[2]['field']] ?>"/><br />
            <?php } elseif (isset($dataLabels[3]['type']) && $dataLabels[3]['type'] == 'string') { ?>
                <input type="text" name="<?=$dataLabels[3]['field'] ?>" value="<?=$canvasItem[$dataLabels[2]['field']] ?>"/><br />
            <?php } else { ?>
                <textarea style="width:100%" rows="3" cols="10" name="<?=$dataLabels[3]['field'] ?>" class="modalTextArea tinymceSimple"><?=$canvasItem[$dataLabels[3]['field']] ?></textarea><br />
            <?php } ?>
        <?php } else { ?>
            <input type="hidden" name="<?=$dataLabels[3]['field'] ?>" value="" />
        <?php } ?>


        <input type="hidden" name="milestoneId" value="<?php echo $canvasItem['milestoneId'] ?>" />
        <input type="hidden" name="changeItem" value="1" />

        <?php if ($id != '') {?>
            <a href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/delCanvasItem/<?php echo $id;?>" class="<?=$canvasName ?>CanvasModal delete right"><i class='fa fa-trash-can'></i> <?php echo $tpl->__("links.delete") ?></a>
        <?php } ?>

        <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
            <input type="submit" value="<?=$tpl->__("buttons.save") ?>" id="primaryCanvasSubmitButton"/>
            <button type="submit" class="btn btn-default" value="closeModal" id="saveAndClose" onclick="leantime.<?=$canvasName ?>CanvasController.setCloseModal();"><?=$tpl->__("buttons.save_and_close") ?></button>


        <?php } ?>

        <?php if ($id !== '') { ?>
            <br /><br />
            <h4 class="widgettitle title-light"><span class="fa fa-link"></span> <?=$tpl->__("headlines.linked_milestone") ?> <i class="fa fa-question-circle-o helperTooltip" data-tippy-content="<?=$tpl->__("tooltip.link_milestones_tooltip") ?>"></i></h4>

            <ul class="sortableTicketList" style="width: 100%">

            <?php if ($canvasItem['milestoneId'] == '') {?>
                <li class="ui-state-default center" id="milestone_0">
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

                </li>
                <?php
            } else {


                ?>

                    <li class="ui-state-default" id="milestone_<?php echo $canvasItem['milestoneId']; ?>" class="<?=$canvasName ?>CanvasMilestone" >
                        <div hx-trigger="load"
                             hx-indicator=".htmx-indicator"
                             hx-get="<?=BASE_URL ?>/hx/tickets/milestones/showCard?milestoneId=<?=$canvasItem['milestoneId'] ?>">
                            <div class="htmx-indicator">
                                <?=$tpl->__("label.loading_milestone") ?>
                            </div>
                        </div>
                        <a href="<?=CURRENT_URL ?>?removeMilestone=<?php echo $canvasItem['milestoneId'];?>" class="<?=$canvasName ?>CanvasModal delete formModal"><i class="fa fa-close"></i> <?=$tpl->__("links.remove") ?></a>

                    </li>
            <?php } ?>

        </ul>

        <?php } ?>

    </form>

    <?php if ($id !== '') { ?>
        <br />
        <input type="hidden" name="comment" value="1" />
        <h4 class="widgettitle title-light"><span class="fa fa-comments"></span><?php echo $tpl->__('subtitles.discussion'); ?></h4>
        <?php
        $tpl->assign("formUrl", "/<?=$canvasName ?>canvas/editCanvasItem/" . $id . "");
        $tpl->displaySubmodule('comments-generalComment');?>
    <?php } ?>
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

        leantime.editorController.initSimpleEditor();

        <?php if (!$login::userIsAtLeast($roles::$editor)) { ?>
            leantime.authController.makeInputReadonly(".nyroModalCont");

        <?php } ?>

        <?php if ($login::userHasRole([$roles::$commenter])) { ?>
            leantime.commentsController.enableCommenterForms();
        <?php }?>

    })
</script>

