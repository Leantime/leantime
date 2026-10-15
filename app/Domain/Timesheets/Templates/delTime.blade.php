@extends($layout)

@section('content')

<x-global::actions.modal-header>
    <x-slot:title>{!! __('headlines.delete_time') !!}</x-slot:title>
</x-global::actions.modal-header>
<form method="post" action="{{ BASE_URL }}/timesheets/delTime/{{ $id }}">
    <p>{!! __('text.confirm_delete_timesheet') !!}</p><br />
    <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.yes_delete')" name="del" />
    <x-global::forms.button tag="a" contentRole="tertiary" link="{{ session('lastPage') }}">{!! __('buttons.back') !!}</x-global::forms.button>
</form>

@endsection
