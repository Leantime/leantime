@extends($layout)
@section('content')

<x-global::layout.page-header icon="fa fa-address-book" :eyebrow="__('label.administration')">
    {!! __('headline.new_client') !!}
</x-global::layout.page-header>

<div class="maincontent">
    <x-global::layout.content-box>

        {!! $tpl->displayNotification() !!}

           <x-global::elements.card>

               <x-slot:title>{!! __('subtitle.details') !!}</x-slot:title>

                <form action="" method="post" class="stdform">

                    @dispatchEvent('afterFormOpen')

                    <div class="row row-fluid">
                        <div class="col-md-6">
                            <x-global::forms.field for="name" :label="__('label.name')">
                                <x-global::forms.text-input name="name" id="name" value="{{ $values['name'] }}" />
                            </x-global::forms.field>

                            <x-global::forms.field for="email" :label="__('label.email')">
                                <x-global::forms.text-input name="email" id="email" value="{{ $values['email'] }}" />
                            </x-global::forms.field>

                            <x-global::forms.field for="internet" :label="__('label.url')">
                                <x-global::forms.text-input
                                        name="internet" id="internet"
                                        value="{{ $values['internet'] }}" />
                            </x-global::forms.field>

                            <x-global::forms.field for="street" :label="__('label.street')">
                                <x-global::forms.text-input
                                        name="street" id="street"
                                        value="{{ $values['street'] }}" />
                            </x-global::forms.field>

                            <x-global::forms.field for="zip" :label="__('label.zip')">
                                <x-global::forms.text-input
                                       name="zip" id="zip" value="{{ $values['zip'] }}" />
                            </x-global::forms.field>

                            <x-global::forms.field for="city" :label="__('label.city')">
                                <x-global::forms.text-input
                                       name="city" id="city" value="{{ $values['city'] }}" />
                            </x-global::forms.field>

                            <x-global::forms.field for="state" :label="__('label.state')">
                                <x-global::forms.text-input
                                        name="state" id="state"
                                        value="{{ $values['state'] }}" />
                            </x-global::forms.field>

                            <x-global::forms.field for="country" :label="__('label.country')">
                                <x-global::forms.text-input
                                        name="country" id="country"
                                        value="{{ $values['country'] }}" />
                            </x-global::forms.field>

                            <x-global::forms.field for="phone" :label="__('label.phone')">
                                <x-global::forms.text-input
                                        name="phone" id="phone"
                                        value="{{ $values['phone'] }}" />
                            </x-global::forms.field>

                            @dispatchEvent('beforeSubmitButton')

                            <div class="form-group">
                                <div class="span4 control-label">
                                    <x-global::forms.button tag="input" inputType="submit" name="save" id="save"
                                           :labelText="__('buttons.save')" contentRole="primary" />
                                </div>
                                <div class="span6">

                                </div>
                            </div>
                        </div>
                    </div>

                    @dispatchEvent('beforeFormClose')

                </form>
            </x-global::elements.card>
    </x-global::layout.content-box>
</div>

@endsection
