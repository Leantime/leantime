@extends($layout) 
@section('content')

<h4 class="widgettitle title-light"><x-global::elements.icon name="delete" /> {{ __('label.delete') }}</h4>

<form method="post" action="{{ BASE_URL."/goalcanvas/delCanvas/$id" }}">
    @if(isset($csrf_token))
        <input type="hidden" name="csrf_token" value="{{ $csrf_token }}">
    @endif
    <p>{{ __('text.confirm_board_deletion') }}</p><br />
    <x-globals::forms.button submit type="danger" name="del">{{ __('buttons.yes_delete') }}</x-globals::forms.button>
    <x-globals::forms.button link="{{ BASE_URL }}/goalcanvas/showCanvas" type="secondary">{{ __('buttons.back') }}</x-globals::forms.button>
</form>

@endsection