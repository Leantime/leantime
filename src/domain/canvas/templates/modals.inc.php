<?php
/**
 * Generic template for create / edit / clone modals
 */
?>
<!-- Modals -->
<div class="modal fade bs-example-modal-lg" id="addCanvas">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="" method="post">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title"><i class='fa fa-plus'></i> <?=$this->__('subtitles.create_new_board') ?></h4>
        </div>
        <div class="modal-body">
          <label><?=$this->__("label.$canvasName.title_new") ?></label>
          <input type="text" name="canvastitle" placeholder="<?=$this->__("input.placeholders.$canvasName.enter_title_for_board") ?>"
                 style="width: 100%"/>
        </div>
        <div class="modal-footer">
          <input type="submit"  class="btn btn-default" value="<?=$this->__('buttons.create_board') ?>" name="newCanvas" />
          <button type="button" class="btn btn-default" data-dismiss="modal"><?=$this->__('buttons.close') ?></button>
        </div>
      </form>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal fade bs-example-modal-lg" id="editCanvas">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="" method="post">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title"><i class='fa fa-edit'></i><?=$this->__('subtitle.edit_board') ?></h4>
        </div>
        <div class="modal-body">
          <label><?=$this->__("label.$canvasName.title_edited") ?></label>
          <input type="text" name="canvastitle" value="<?php $this->e($canvasTitle); ?>" style="width: 100%"/>
        </div>
        <div class="modal-footer">
          <input type="submit"  class="btn btn-default" value="<?=$this->__('buttons.save') ?>" name="editCanvas" />
          <button type="button" class="btn btn-default" data-dismiss="modal"><?=$this->__('buttons.close') ?></button>
        </div>
      </form>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal fade bs-example-modal-lg" id="cloneCanvas">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="" method="post">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title"><i class='fa fa-clone'></i> <?=$this->__('subtitle.clone_board') ?></h4>
        </div>
        <div class="modal-body">
          <label><?=$this->__("label.$canvasName.title_clone") ?></label>
          <input type="text" name="canvastitle" value="<?php $this->e($canvasTitle); ?>" style="width: 100%"/>
        </div>
        <div class="modal-footer">
          <input type="submit"  class="btn btn-default" value="<?=$this->__('buttons.save') ?>" name="cloneCanvas" />
          <button type="button" class="btn btn-default" data-dismiss="modal"><?=$this->__('buttons.close') ?></button>
        </div>
      </form>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
