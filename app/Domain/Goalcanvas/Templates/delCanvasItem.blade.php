@extends($layout)
@section('content')

<x-global::actions.modal-header>
    <x-slot:title>{!! __("subtitles.delete") !!}</x-slot:title>
</x-global::actions.modal-header>
<hr style="margin-top: 5px; margin-bottom: 15px;">

<form method="post" action="{{ BASE_URL }}/goalcanvas/delCanvasItem/{{ $id }}">
    <p>{{ __('text.confirm_board_item_deletion') }}</p><br />
    <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.yes_delete')" name="del" />
    <x-global::forms.button tag="a" contentRole="tertiary" link="{{ BASE_URL }}/goalcanvas/showCanvas">{{ __('buttons.back') }}</x-global::forms.button>
</form>
@endsection
