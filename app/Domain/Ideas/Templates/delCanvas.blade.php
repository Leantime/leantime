<x-globals::layout.page-header icon="delete" headline="{{ $tpl->__('headline.delete_board') }}" subtitle="{{ session('currentProjectClient') . ' // ' . session('currentProjectName') }}" />

<div class="maincontent">
    <div class="maincontentinner">
        <x-globals::elements.section-title variant="plain" icon="delete">{{ $tpl->__('label.delete') }}</x-globals::elements.section-title>
        <div class="widgetcontent">
            <form method="post" action="{{ BASE_URL }}/ideas/delCanvas/{{ $tpl->escape($_GET['id']) }}">
                <p>{{ $tpl->__('text.are_you_sure_delete_idea_board') }}</p>
                <x-globals::forms.button :submit="true" state="danger" name="del">{{ $tpl->__('buttons.yes_delete') }}</x-globals::forms.button>
                <x-globals::forms.button link="{{ BASE_URL }}/ideas/showBoards" contentRole="secondary">{{ $tpl->__('buttons.back') }}</x-globals::forms.button>
            </form>
        </div>
    </div>
</div>
