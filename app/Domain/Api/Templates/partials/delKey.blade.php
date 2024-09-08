<x-global::content.modal.modal-buttons/>

<?php
$user = $tpl->get('user');
?>

@displayNotification()

<h5 class="subtitle">{{ __("subtitles.delete_key") }}</h5>

<x-global::content.modal.form method="post">
    <input type="hidden" name="<?=session("formTokenName")?>" value="<?=session("formTokenValue")?>" />
    <p>{{ __("text.confirm_key_deletion") }}</p><br />
    <input type="submit" value="{{ __("buttons.yes_delete") }}" name="del" class="button" />
    <a class="btn btn-primary" href="{{ BASE_URL }}/setting/editCompanySettings/#apiKeys">{{ __("buttons.back") }}</a>
</x-global::content.modal.form>

