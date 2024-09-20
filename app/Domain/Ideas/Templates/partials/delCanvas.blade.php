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
                    <p>{!! __('text.are_you_sure_delete_idea_board') !!}</p>
                    <x-global::forms.button type="submit" name="del" class="button">
                        {!! __('buttons.yes_delete') !!}
                    </x-global::forms.button>

                    <x-global::forms.button tag="a" href="{{ BASE_URL }}/ideas/showBoards"
                        class="btn btn-secondary" content-role="secondary">
                        {!! __('buttons.back') !!}
                    </x-global::forms.button>
                </form>
            </div>
        </div>
    </div>
@endsection
