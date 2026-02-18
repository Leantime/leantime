<div class="pageheader">
    <div class="pageicon"><span class="fa fa-trash"></span></div>
    <div class="pagetitle">
        <h5>{{ session('currentProjectClient') . ' // ' . session('currentProjectName') }}</h5>
        <h1>{{ $tpl->__('headline.delete_board') }}</h1>
    </div>
</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner">
        <h4 class="widget widgettitle">{{ $tpl->__('subtitles.delete') }}</h4>
        <div class="widgetcontent">
            <form method="post" action="{{ BASE_URL }}/ideas/delCanvas/{{ $tpl->escape($_GET['id']) }}">
                <p>{{ $tpl->__('text.are_you_sure_delete_idea_board') }}</p>
                <x-global::button submit type="danger" name="del">{{ $tpl->__('buttons.yes_delete') }}</x-global::button>
                <x-global::button link="{{ BASE_URL }}/ideas/showBoards" type="secondary">{{ $tpl->__('buttons.back') }}</x-global::button>
            </form>
        </div>
    </div>
</div>
