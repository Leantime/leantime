<?php
$client = $tpl->get('client');
?>


@displayNotification()
<h4 class="widget widgettitle">{!! __("subtitles.delete") !!}</h4>
<div class="widgetcontent">
            <form method="post">
            <form method="post">

    <form method="post">

                <?php $tpl->dispatchTplEvent('afterFormOpen'); ?>
                <?php $tpl->dispatchTplEvent('afterFormOpen'); ?>

        <?php $tpl->dispatchTplEvent('afterFormOpen'); ?>

        <p>{{ __("text.confirm_client_deletion") }}<br /></p>
        <x-global::forms.button type="submit" name="del" content-role="primary">
            {{ __('buttons.yes_delete') }}
        </x-global::forms.button>
        <x-global::forms.button tag="a" href="/clients/showClient/{{ $client->id }}" content-role="tertiary">
            {{ __('buttons.back') }}
        </x-global::forms.button>
                <?php $tpl->dispatchTplEvent('beforeFormClose'); ?>
                <?php $tpl->dispatchTplEvent('beforeFormClose'); ?>

        <?php $tpl->dispatchTplEvent('beforeFormClose'); ?>

    </form>
</div>

