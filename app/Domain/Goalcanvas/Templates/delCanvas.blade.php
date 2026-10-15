@extends($layout) 
@section('content')

<x-global::actions.modal-header>
    <x-slot:title>{!!__("subtitles.delete") !!}</x-slot:title>
</x-global::actions.modal-header>
<form method="post" action="{{ BASE_URL."/goalcanvas/delCanvas/$id" }}">
    @if(isset($csrf_token))
        <input type="hidden" name="csrf_token" value="{{ $csrf_token }}">
    @endif
    <p>{{ __('text.confirm_board_deletion') }}</p><br />
    <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.yes_delete')" name="del" />
    <x-global::forms.button tag="a" contentRole="tertiary" link="{{ BASE_URL }}/goalcanvas/showCanvas">{{ __('buttons.back') }}</x-global::forms.button>
</form>

@endsection