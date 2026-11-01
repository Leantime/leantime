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

                <x-global::forms.field layout="inline" for="name" :label="__('label.name')">
                    <x-global::forms.text-input name="name" id="name" value="{{ $values['name'] }}" />
                </x-global::forms.field>

                <x-global::forms.field layout="inline" for="email" :label="__('label.email')">
                    <x-global::forms.text-input name="email" id="email" value="{{ $values['email'] }}" />
                </x-global::forms.field>

                <x-global::forms.field layout="inline" for="internet" :label="__('label.url')">
                    <x-global::forms.text-input
                        name="internet" id="internet"
                        value="{{ $values['internet'] }}" />
                </x-global::forms.field>

                <x-global::forms.field layout="inline" for="street" :label="__('label.street')">
                    <x-global::forms.text-input
                        name="street" id="street"
                        value="{{ $values['street'] }}" />
                </x-global::forms.field>

                <x-global::forms.field layout="inline" for="zip" :label="__('label.zip')">
                    <x-global::forms.text-input
                        name="zip" id="zip" value="{{ $values['zip'] }}" />
                </x-global::forms.field>

                <x-global::forms.field layout="inline" for="city" :label="__('label.city')">
                    <x-global::forms.text-input
                        name="city" id="city" value="{{ $values['city'] }}" />
                </x-global::forms.field>

                <x-global::forms.field layout="inline" for="state" :label="__('label.state')">
                    <x-global::forms.text-input
                        name="state" id="state"
                        value="{{ $values['state'] }}" />
                </x-global::forms.field>

                <x-global::forms.field layout="inline" for="country" :label="__('label.country')">
                    <x-global::forms.text-input
                        name="country" id="country"
                        value="{{ $values['country'] }}" />
                </x-global::forms.field>

                <x-global::forms.field layout="inline" for="phone" :label="__('label.phone')">
                    <x-global::forms.text-input
                        name="phone" id="phone"
                        value="{{ $values['phone'] }}" />
                </x-global::forms.field>

                <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.save')" name="save" id="save" />

                </x-global::elements.card>

        </form>

    </x-global::layout.content-box>
</div>

@endsection
