<?php
/**
 * canvasComment.inc template - Generic template for comments
 *
 * Required variables:
 * - $canvasName   Name of current canvas
 */
defined('RESTRICTED') or die('Restricted access');

$canvasItem = $this->get('canvasItem');
$canvasTypes = $this->get('canvasTypes');

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

<div class="showDialogOnLoad" style="display:none;">

  <h4 class="widgettitle title-light" style="padding-bottom: 0"><i class="fas <?=$canvasTypes[$canvasItem['box']]['icon']; ?>"></i> <?=$canvasTypes[$canvasItem['box']]['title']; ?></h4>
  <hr style="margin-top: 5px; margin-bottom: 15px;">

    <?php echo $this->displayNotification(); ?>

    <h5 style="padding-left: 40px"><strong><?php $this->e($canvasItem['description']) ?></strong></h5>

    <?php if($id !== '') { ?>
    <br />
    <input type="hidden" name="comment" value="1" />
        <h4 class="widgettitle title-light"><span class="fa fa-comments"></span><?php echo $this->__('subtitles.discussion'); ?></h4>
        <?php
        $this->assign("formUrl", "/<?=$canvasName ?>canvas/editCanvasComment/".$id."");
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
