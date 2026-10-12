@extends($layout)

@section('content')

<x-global::actions.modal-header>
    <x-slot:title><i class="fa fa-trash"></i> {!! __('buttons.delete') !!}</x-slot:title>
</x-global::actions.modal-header>
<form method="post" action="{{ BASE_URL }}/ideas/delCanvasItem/{{ (int) $_GET['id'] }}">
    <p>{!! __('text.are_you_sure_delete_idea') !!}</p><br />
    <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.yes_delete')" name="del" />
    <x-global::forms.button tag="a" contentRole="tertiary" link="{{ BASE_URL }}/ideas/showBoards/">{!! __('buttons.back') !!}</x-global::forms.button>
</form>

@endsection
