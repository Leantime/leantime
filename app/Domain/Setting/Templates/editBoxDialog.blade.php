@extends($layout)
@section('content')

<x-global::actions.modal-header>
    <x-slot:title>{!! __('headlines.edit_label') !!}</x-slot:title>
</x-global::actions.modal-header>{!! $tpl->displayNotification() !!}

<form class="formModal" method="post" action="{{ BASE_URL }}/setting/editBoxLabel?{{ http_build_query(['module' => request()->query('module', ''), 'label' => request()->query('label', '')]) }}">

    <x-global::forms.field for="newLabel" :label="__('label.label')">
        <x-global::forms.text-input name="newLabel" id="newLabel" value="{{ $currentLabel }}" />
    </x-global::forms.field>

    <div class="row">
        <div class="col-md-6">
            <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.save')" />
        </div>

    </div>

</form>

@endsection
