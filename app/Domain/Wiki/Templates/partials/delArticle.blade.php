
<x-global::content.modal.modal-buttons/>

<?php
$ticket = $tpl->get('ticket');
?>

<h4 class="widgettitle title-light"><i class="fa fa-trash"></i> {{ __("buttons.delete") }}</h4>

<x-global::content.modal.form action="{{ BASE_URL }}/wiki/delArticle/{{ (int)$_GET['id'] }}">
    <p>{{ __("text.are_you_sure_delete_article") }}</p><br />
    <input type="submit" value="<?php echo $tpl->__("buttons.yes_delete")?>" name="del" class="button" />
    <a class="btn btn-secondary" href="{{ BASE_URL }}/wiki/show/">{{ __("buttons.back") }}</a>
</x-global::content.modal.form>
