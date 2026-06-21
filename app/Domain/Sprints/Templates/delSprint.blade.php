@extends($layout)

@section('content')

<h4 class="widgettitle title-light">{!! __('headlines.delete_sprint') !!}</h4>

<form method="post" action="{{ BASE_URL }}/sprints/delSprint/{{ $id }}">
    <p>{!! __('text.are_you_sure_delete_sprint') !!}</p><br />
    <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.yes_delete')" name="del" />
    <x-global::forms.button tag="a" contentRole="tertiary" link="{{ session('lastPage') }}">{!! __('buttons.back') !!}</x-global::forms.button>
</form>

@endsection
