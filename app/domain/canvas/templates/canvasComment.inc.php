<?php

/**
 * canvasComment.inc template - Generic template for comments
 *
 * Required variables:
 * - $canvasName   Name of current canvas
 */

defined('RESTRICTED') or die('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$canvasItem = $tpl->get('canvasItem');
$canvasTypes = $tpl->get('canvasTypes');

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

<div class="showDialogOnLoad" style="display:none;">

  <h4 class="widgettitle title-light" style="padding-bottom: 0"><i class="fas <?=$canvasTypes[$canvasItem['box']]['icon']; ?>"></i> <?=$canvasTypes[$canvasItem['box']]['title']; ?></h4>
  <hr style="margin-top: 5px; margin-bottom: 15px;">

    <?php echo $tpl->displayNotification(); ?>

    <h5 style="padding-left: 40px"><strong><?php $tpl->e($canvasItem['description']) ?></strong></h5>

    <?php if ($id !== '') { ?>
    <br />
    <input type="hidden" name="comment" value="1" />
        <h4 class="widgettitle title-light"><span class="fa fa-comments"></span><?php echo $tpl->__('subtitles.discussion'); ?></h4>
        <?php
        $tpl->assign("formUrl", "/<?=$canvasName ?>canvas/editCanvasComment/" . $id . "");
        $tpl->displaySubmodule('comments-generalComment');?>
    <?php } ?>
</div>

<script type="text/javascript">
    jQuery(document).ready(function(){

        leantime.editorController.initSimpleEditor();

        <?php if (!$login::userIsAtLeast($roles::$editor)) { ?>
            leantime.authController.makeInputReadonly(".nyroModalCont");

        <?php } ?>

        <?php if ($login::userHasRole([$roles::$commenter])) { ?>
            leantime.commentsController.enableCommenterForms();
        <?php }?>

    })
</script>
