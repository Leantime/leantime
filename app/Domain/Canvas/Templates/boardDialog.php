<?php

/**
 * modals.inc template - Generic template for create / edit / clone modals
 */
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$allCanvas = $tpl->get('allCanvas');
$canvasTitle = $tpl->get('canvasTitle');
$canvasName = $tpl->get('canvasName');
?>

  <form action="<?= BASE_URL ?>/<?= $canvasName?>canvas/boardDialog<?= isset($_GET['id']) ? '/'.(int) $_GET['id'] : ''?>" method="post" class="formModal">
    <div style="margin-bottom: 15px;">
      <h4 class="widgettitle title-light"><i class='fa fa-plus'></i> <?= $tpl->__('subtitles.create_new_board') ?></h4>
    </div>
    <div style="margin-bottom: 15px;">
      <label><?= $tpl->__('label.title_new') ?></label><br />
      <input type="text" name="canvastitle" value="<?= $tpl->escape($canvasTitle) ?>" placeholder="<?= $tpl->__('input.placeholders.enter_title_for_board') ?>"
             style="width: 100%"/>

    </div>
    <div style="display: flex; justify-content: flex-end; gap: 8px; padding-top: 10px;">
        <?php if (isset($_GET['id'])) {?>
            <input type="submit"  class="btn btn-primary" value="<?= $tpl->__('buttons.save_board') ?>" name="newCanvas" />
            <input type="hidden" name="editCanvas" value="<?= (int) $_GET['id'] ?? ''?>">
        <?php } else { ?>
            <input type="hidden" name="newCanvas" value="true">
            <input type="submit"  class="btn btn-primary" value="<?= $tpl->__('buttons.create_board') ?>" name="newCanvas" />
        <?php } ?>
        <button type="button" class="btn btn-default" onclick="leantime.modals.closeModal();"><?= $tpl->__('buttons.close') ?></button>
    </div>
  </form>
