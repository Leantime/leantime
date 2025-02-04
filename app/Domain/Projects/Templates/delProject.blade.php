<?php
$project = $tpl->get('project');
?>

@displayNotification()
<h4 class="widget widgettitle">{!! __("subtitles.delete") !!}</h4>

<div class="widgetcontent">
    <form method="post">
        <p>{{ __("text.confirm_project_deletion") }}</p><br />
        <x-global::forms.button type="submit" name="del" class="button">
            {{ __('buttons.yes_delete') }}
        </x-global::forms.button>
        
        <x-global::forms.button tag="a" href="{{ BASE_URL }}/projects/showProject/{{ $project['id'] }}">
            {{ __('buttons.back') }}
        </x-global::forms.button>
    </form>
</div>
