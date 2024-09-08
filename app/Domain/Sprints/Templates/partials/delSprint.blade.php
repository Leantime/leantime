<x-global::content.modal.modal-buttons/>

<h4 class="widgettitle title-light"><?=$tpl->__("headlines.delete_sprint") ?></h4>

<x-global::content.modal.form action="{{ BASE_URL }}/sprints/delSprint/{{ $id }}">
    <p><?=$tpl->__("text.are_you_sure_delete_sprint") ?></p><br />
    <input type="submit" value="<?=$tpl->__("buttons.yes_delete") ?>" name="del" class="btn" />
    <a class="btn btn-ghost" href="<?php echo session("lastPage") ?>"><?=$tpl->__("buttons.back") ?></a>
</x-global::content.modal.form>

