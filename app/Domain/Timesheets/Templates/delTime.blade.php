@extends($layout)

@section('content')

<h4 class="widgettitle title-light">{!! __('headlines.delete_time') !!}</h4>

<form method="post" action="{{ BASE_URL }}/timesheets/delTime/{{ $id }}">
    <p>{!! __('text.confirm_delete_timesheet') !!}</p><br />
    <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.yes_delete')" name="del" />
    <x-global::forms.button tag="a" contentRole="tertiary" link="{{ session('lastPage') }}">{!! __('buttons.back') !!}</x-global::forms.button>
</form>

@endsection
