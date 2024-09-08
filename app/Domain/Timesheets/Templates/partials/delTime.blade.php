<x-global::content.modal.modal-buttons/>

<h4 class="widgettitle title-light"><?php printf("" . $tpl->__('headlines.delete_time') . ""); ?></h4>

<x-global::content.modal.form method="post" action="{{ BASE_URL }}/timesheets/delTime/{{ $id }}">
    <p><?=$tpl->__("text.confirm_delete_timesheet") ?></p><br />
    <input type="submit" value="<?=$tpl->__("buttons.yes_delete") ?>" name="del" class="button" />
    <a class="btn btn-secondary" href="<?php echo session("lastPage") ?>"><?=$tpl->__("buttons.back") ?></a>
</x-global::content.modal.form>

