@extends($layout)
@section('content')

<x-global::layout.page-header icon="fa {{ $tpl->getModulePicture() }}" :eyebrow="__('label.administration')">
    {!! __('headline.edit_client') !!}
</x-global::layout.page-header>

<div class="maincontent">
    <div class="maincontentinner">

        {!! $tpl->displayNotification() !!}

        <form action="" method="post" class="stdform">

            <x-global::elements.card>

                <x-slot:title>{!! __('label.overview') !!}</x-slot:title>

                <label for="name">{!! __('label.name') !!}</label>
                <x-global::forms.text-input name="name" id="name" value="{{ $values['name'] }}" /><br />

                <label for="email">{!! __('label.email') !!}</label>
                <x-global::forms.text-input name="email" id="email" value="{{ $values['email'] }}" /><br />

                <label for="internet">{!! __('label.url') !!}</label> <x-global::forms.text-input
                    name="internet" id="internet"
                    value="{{ $values['internet'] }}" /><br />

                <label for="street">{!! __('label.street') !!}</label> <x-global::forms.text-input
                    name="street" id="street"
                    value="{{ $values['street'] }}" /><br />

                <label for="zip">{!! __('label.zip') !!}</label> <x-global::forms.text-input
                    name="zip" id="zip" value="{{ $values['zip'] }}" /><br />

                <label for="city">{!! __('label.city') !!}</label> <x-global::forms.text-input
                    name="city" id="city" value="{{ $values['city'] }}" /><br />

                <label for="state">{!! __('label.state') !!}</label> <x-global::forms.text-input
                    name="state" id="state"
                    value="{{ $values['state'] }}" /><br />

                <label for="country">{!! __('label.country') !!}</label> <x-global::forms.text-input
                    name="country" id="country"
                    value="{{ $values['country'] }}" /><br />

                <label for="phone">{!! __('label.phone') !!}</label> <x-global::forms.text-input
                    name="phone" id="phone"
                    value="{{ $values['phone'] }}" /><br />

                <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.save')" name="save" id="save" />

                </x-global::elements.card>

        </form>

    </div>
</div>

@endsection
