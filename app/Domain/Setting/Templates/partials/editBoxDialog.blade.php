<x-global::content.modal.modal-buttons/>

<?php
$currentLabel = $tpl->get('currentLabel');
?>

<h4 class="widgettitle title-light"><?=$tpl->__("headlines.edit_label")?></h4>

@displayNotification()

<x-global::content.modal.form action="{{ BASE_URL }}/setting/editBoxLabel?module={{ $_GET['module'] }}&label={{ $_GET['label'] }}">

<x-global::forms.text-input 
    type="text" 
    name="headline" 
    placeholder="{{ __('input.placeholders.create_task') }}" 
    autofocus 
    variant="title"
/>

    <div class="row">
        <div class="col-md-6">
            <input type="submit" value="{{ __("buttons.save") }}"/>
        </div>

    </div>

</x-global::content.modal.form>

