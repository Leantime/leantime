@extends($layout)
@section('content')

<h4 class="widgettitle title-light"><i class="fa fa-trash"></i> {!! __("buttons.delete") !!}</h4>

<form method="post" action="{{ BASE_URL }}/ideas/delCanvasItem/{{ (int)$_GET['id'] }}">
    <p>{!! __("text.are_you_sure_delete_idea") !!}</p><br />
    <input type="submit" value="{!! __("buttons.yes_delete") !!}" name="del" class="button" />
    <a class="btn btn-secondary" href="{{ BASE_URL }}/ideas/showBoards/">{!! __("buttons.back") !!}</a>
</form>

@endsection