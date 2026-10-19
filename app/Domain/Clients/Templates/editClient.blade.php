@extends($layout)
@section('content')

<x-global::layout.page-header icon="fa {{ $tpl->getModulePicture() }}" :eyebrow="__('label.administration')">
    {!! __('headline.edit_client') !!}
</x-global::layout.page-header>

<div class="maincontent">
    <x-global::layout.content-box>

        {!! $tpl->displayNotification() !!}

        <form action="" method="post" class="stdform">

            <x-global::elements.card>

                <x-slot:title>{!! __('label.overview') !!}</x-slot:title>

                <x-global::forms.label for="name">{!! __('label.name') !!}</x-global::forms.label>
                <x-global::forms.text-input name="name" id="name" value="{{ $values['name'] }}" /><br />

                <x-global::forms.label for="email">{!! __('label.email') !!}</x-global::forms.label>
                <x-global::forms.text-input name="email" id="email" value="{{ $values['email'] }}" /><br />

                <x-global::forms.label for="internet">{!! __('label.url') !!}</x-global::forms.label> <x-global::forms.text-input
                    name="internet" id="internet"
                    value="{{ $values['internet'] }}" /><br />

                <x-global::forms.label for="street">{!! __('label.street') !!}</x-global::forms.label> <x-global::forms.text-input
                    name="street" id="street"
                    value="{{ $values['street'] }}" /><br />

                <x-global::forms.label for="zip">{!! __('label.zip') !!}</x-global::forms.label> <x-global::forms.text-input
                    name="zip" id="zip" value="{{ $values['zip'] }}" /><br />

                <x-global::forms.label for="city">{!! __('label.city') !!}</x-global::forms.label> <x-global::forms.text-input
                    name="city" id="city" value="{{ $values['city'] }}" /><br />

                <x-global::forms.label for="state">{!! __('label.state') !!}</x-global::forms.label> <x-global::forms.text-input
                    name="state" id="state"
                    value="{{ $values['state'] }}" /><br />

                <x-global::forms.label for="country">{!! __('label.country') !!}</x-global::forms.label> <x-global::forms.text-input
                    name="country" id="country"
                    value="{{ $values['country'] }}" /><br />

                <x-global::forms.label for="phone">{!! __('label.phone') !!}</x-global::forms.label> <x-global::forms.text-input
                    name="phone" id="phone"
                    value="{{ $values['phone'] }}" /><br />

                <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.save')" name="save" id="save" />

                </x-global::elements.card>

        </form>

    </x-global::layout.content-box>
</div>

@endsection
