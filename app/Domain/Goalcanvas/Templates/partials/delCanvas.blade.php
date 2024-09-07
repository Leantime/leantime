@extends($layout) 
@section('content')

<h4 class="widgettitle title-light">{!!__("subtitles.delete") !!}</h4>

<form method="post" action="{{ BASE_URL."/goalcanvas/delCanvas/$id" }}">
    @if(isset($csrf_token))
        <input type="hidden" name="csrf_token" value="{{ $csrf_token }}">
    @endif
    <p>{{ __('text.confirm_board_deletion') }}</p><br />
    <input type="submit" value="{{ __('buttons.yes_delete') }}" name="del" class="button" />
    <a class="btn btn-secondary" href="{{ BASE_URL."/goalcanvas/showCanvas" }}">{{ __('buttons.back') }}</a>
</form>

@endsection