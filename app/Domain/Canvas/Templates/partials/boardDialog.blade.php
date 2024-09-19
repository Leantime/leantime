@extends($layout)

@section('content')

<?php
$allCanvas = $tpl->get('allCanvas');
$canvasTitle = $tpl->get('canvasTitle');
$canvasName = $tpl->get('canvasName');
?>

  <form action="{{ BASE_URL }}/<?=$canvasName?>canvas/boardDialog<?=isset($_GET['id']) ? '/'.(int)$_GET['id'] : ''?>" method="post" class="formModal">
    <div class="modal-header">
      <h4 class="modal-title"><i class='fa fa-plus'></i> <?=$tpl->__('subtitles.create_new_board') ?></h4>
    </div>
    <div class="modal-body">
      <label><?=$tpl->__("label.title_new") ?></label><br />
      <x-global::forms.text-input 
          type="text" 
          name="canvastitle" 
          value="{{ $canvasTitle }}" 
          placeholder="{{ $tpl->__('input.placeholders.enter_title_for_board') }}" 
          variant="title" 
      />
  

    </div>
    <div class="modal-footer">
        <?php if(isset($_GET['id'])){?>
            <input type="submit"  class="btn btn-primary" value="<?=$tpl->__('buttons.save_board') ?>" name="newCanvas" />
            <input type="hidden" name="editCanvas" value="<?=(int)$_GET['id'] ?? ''?>">
        <?php }else{ ?>
            <input type="hidden" name="newCanvas" value="true">
            <input type="submit"  class="btn btn-primary" value="<?=$tpl->__('buttons.create_board') ?>" name="newCanvas" />
        <?php } ?>
        <button type="button" class="btn btn-default" onclick="jQuery.nmTop().close();"><?=$tpl->__('buttons.close') ?></button>
    </div>
  </form>
