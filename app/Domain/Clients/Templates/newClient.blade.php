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
                            <div class="form-group">
                                <x-global::forms.label class="span4 control-label">{!! __('label.name') !!}</x-global::forms.label>
                                <div class="span6">
                                    <x-global::forms.text-input name="name" id="name" value="{{ $values['name'] }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <x-global::forms.label class="span4 control-label">{!! __('label.email') !!}</x-global::forms.label>
                                <div class="span6">
                                    <x-global::forms.text-input name="email" id="email" value="{{ $values['email'] }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <x-global::forms.label class="span4 control-label">{!! __('label.url') !!}</x-global::forms.label>
                                <div class="span6">
                                    <x-global::forms.text-input
                                            name="internet" id="internet"
                                            value="{{ $values['internet'] }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <x-global::forms.label class="span4 control-label">{!! __('label.street') !!}</x-global::forms.label>
                                <div class="span6">
                                    <x-global::forms.text-input
                                            name="street" id="street"
                                            value="{{ $values['street'] }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <x-global::forms.label class="span4 control-label">{!! __('label.zip') !!}</x-global::forms.label>
                                <div class="span6">
                                    <x-global::forms.text-input
                                           name="zip" id="zip" value="{{ $values['zip'] }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <x-global::forms.label class="span4 control-label">{!! __('label.city') !!}</x-global::forms.label>
                                <div class="span6">
                                    <x-global::forms.text-input
                                           name="city" id="city" value="{{ $values['city'] }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <x-global::forms.label class="span4 control-label">{!! __('label.state') !!}</x-global::forms.label>
                                <div class="span6">
                                    <x-global::forms.text-input
                                            name="state" id="state"
                                            value="{{ $values['state'] }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <x-global::forms.label class="span4 control-label">{!! __('label.country') !!}</x-global::forms.label>
                                <div class="span6">
                                    <x-global::forms.text-input
                                            name="country" id="country"
                                            value="{{ $values['country'] }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <x-global::forms.label class="span4 control-label">{!! __('label.phone') !!}</x-global::forms.label>
                                <div class="span6">
                                    <x-global::forms.text-input
                                            name="phone" id="phone"
                                            value="{{ $values['phone'] }}" />
                                </div>
                            </div>

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
