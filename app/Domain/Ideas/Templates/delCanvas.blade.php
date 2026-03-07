<div class="pageheader">
    <div class="pageicon"><x-global::elements.icon name="delete" /></div>
    <div class="pagetitle">
        <h5>{{ session('currentProjectClient') . ' // ' . session('currentProjectName') }}</h5>
        <h1>{{ $tpl->__('headline.delete_board') }}</h1>
    </div>
</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner">
        <h4 class="widget widgettitle"><x-global::elements.icon name="delete" /> {{ $tpl->__('label.delete') }}</h4>
        <div class="widgetcontent">
            <form method="post" action="{{ BASE_URL }}/ideas/delCanvas/{{ $tpl->escape($_GET['id']) }}">
                <p>{{ $tpl->__('text.are_you_sure_delete_idea_board') }}</p>
                <x-globals::forms.button submit type="danger" name="del">{{ $tpl->__('buttons.yes_delete') }}</x-globals::forms.button>
                <x-globals::forms.button link="{{ BASE_URL }}/ideas/showBoards" type="secondary">{{ $tpl->__('buttons.back') }}</x-globals::forms.button>
            </form>
        </div>
    </div>
</div>
