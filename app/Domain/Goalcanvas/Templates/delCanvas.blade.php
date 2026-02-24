@extends($layout) 
@section('content')

<h4 class="widgettitle title-light">{!!__("subtitles.delete") !!}</h4>

<form method="post" action="{{ BASE_URL."/goalcanvas/delCanvas/$id" }}">
    @if(isset($csrf_token))
        <input type="hidden" name="csrf_token" value="{{ $csrf_token }}">
    @endif
    <p>{{ __('text.confirm_board_deletion') }}</p><br />
    <x-global::button submit type="danger" name="del">{{ __('buttons.yes_delete') }}</x-global::button>
    <x-global::button link="{{ BASE_URL }}/goalcanvas/showCanvas" type="secondary">{{ __('buttons.back') }}</x-global::button>
</form>

@endsection