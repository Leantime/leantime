@extends($layout)
@section('content')

<div class="pageheader">
    <div class="pageicon"><span class="fa fa-trash"></span></div>
    <div class="pagetitle">
        <h5>{{ session("currentProjectClient") . " // " . session("currentProjectName") }}</h5>
        <h1>{!! __("headline.delete_board") !!}</h1>
    </div>
</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner">
        <h4 class="widget widgettitle">{!! __("subtitles.delete") !!}</h4>
        <div class="widgetcontent">
            <form method="post" action="{{ BASE_URL }}/ideas/delCanvas/{{ $_GET['id'] }}">
                <p>{!! __("text.are_you_sure_delete_idea_board") !!}</p>
                <input type="submit" value="{!! __("buttons.yes_delete") !!}" name="del" class="button" />
                <a class="btn btn-secondary" href="{{ BASE_URL }}/ideas/showBoards">{!! __("buttons.back") !!}</a>
            </form>
        </div>
    </div>
</div>

@endsection