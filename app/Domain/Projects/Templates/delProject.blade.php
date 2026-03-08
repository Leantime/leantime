@php
    $project = $tpl->get('project');
@endphp

<x-globals::layout.page-header :icon="$tpl->getModulePicture()" subtitle="{{ __('label.administration') }}" headline="{{ sprintf(__('headlines.delete_project_x'), $project['name']) }}" />

<div class="maincontent">
    <div class="maincontentinner">

        {!! $tpl->displayNotification() !!}

        <x-globals::elements.section-title variant="plain" icon="delete">{{ __('label.delete') }}</x-globals::elements.section-title>
        <div class="widgetcontent">

            <form method="post">
                <p>{{ __('text.confirm_project_deletion') }}</p><br />
                <x-globals::forms.button submit type="danger" name="del">{{ __('buttons.yes_delete') }}</x-globals::forms.button>
                <x-globals::forms.button link="{{ BASE_URL }}/projects/showProject/{{ $project['id'] }}" type="primary">{{ __('buttons.back') }}</x-globals::forms.button>
            </form>

        </div>

    </div>
</div>
