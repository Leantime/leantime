@extends($layout) 
@section('content')

<x-globals::elements.section-title icon="delete">{{ __('label.delete') }}</x-globals::elements.section-title>

<form method="post" action="{{ BASE_URL."/goalcanvas/delCanvas/$id" }}">
    @if(isset($csrf_token))
        <input type="hidden" name="csrf_token" value="{{ $csrf_token }}">
    @endif
    <p>{{ __('text.confirm_board_deletion') }}</p><br />
    <x-globals::forms.button :submit="true" state="danger" name="del">{{ __('buttons.yes_delete') }}</x-globals::forms.button>
    <x-globals::forms.button link="{{ BASE_URL }}/goalcanvas/showCanvas" contentRole="secondary">{{ __('buttons.back') }}</x-globals::forms.button>
</form>

@endsection