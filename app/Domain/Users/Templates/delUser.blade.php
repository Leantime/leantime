<?php
$user = $tpl->get('user');
?>

@displayNotification()

<h4 class="widget widgettitle">{!! __("subtitles.delete") !!}</h4>

<div class="widgetcontent">
    <form method="post">
        <input type="hidden" name="<?=session("formTokenName")?>" value="<?=session("formTokenValue")?>" />
        
        <p>{{ __("text.confirm_user_deletion") }}</p><br />
        
        <x-global::forms.button type="submit" name="del" class="button">
            {{ __('buttons.yes_delete') }}
        </x-global::forms.button>
        
        <x-global::forms.button tag="a" href="{{ BASE_URL }}/users/showAll">
            {{ __('buttons.back') }}
        </x-global::forms.button>
    </form>
</div>
