@extends($layout)
@section('content')

<h4 class="widgettitle title-light">{!! __('headlines.edit_label') !!}</h4>

{!! $tpl->displayNotification() !!}

<form class="formModal" method="post" action="{{ BASE_URL }}/setting/editBoxLabel?{{ http_build_query(['module' => request()->query('module', ''), 'label' => request()->query('label', '')]) }}">

    <label>{!! __('label.label') !!}</label>
    <x-global::forms.text-input name="newLabel" value="{{ $currentLabel }}" /><br />

    <div class="row">
        <div class="col-md-6">
            <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.save')" />
        </div>

    </div>

</form>

@endsection
