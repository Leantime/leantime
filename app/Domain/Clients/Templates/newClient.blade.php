@php
    $values = $tpl->get('values');
@endphp

@dispatchEvent('beforePageHeaderOpen')
<div class="pageheader">
    @dispatchEvent('afterPageHeaderOpen')
    <div class="pageicon"><span class="fa fa-address-book"></span></div>
    <div class="pagetitle">
        <h5>{{ __('label.administration') }}</h5>
        <h1>{{ __('headline.new_client') }}</h1>
    </div>
    @dispatchEvent('beforePageHeaderClose')
</div>
@dispatchEvent('afterPageHeaderClose')

<div class="maincontent">
    <div class="maincontentinner">

        {!! $tpl->displayNotification() !!}

        <div class="widget">
           <h4 class="widgettitle">{{ __('subtitle.details') }}</h4>
           <div class="widgetcontent">

                <form action="" method="post" class="stdform">

                    @dispatchEvent('afterFormOpen')

                    <div>
                        <div>
                            <div class="form-group">
                                <label class="span4 control-label">{{ __('label.name') }}</label>
                                <div class="span6">
                                    <x-globals::forms.input name="name" id="name" value="{{ e($values['name']) }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="span4 control-label">{{ __('label.email') }}</label>
                                <div class="span6">
                                    <x-globals::forms.input name="email" id="email" value="{{ e($values['email']) }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="span4 control-label">{{ __('label.url') }}</label>
                                <div class="span6">
                                    <x-globals::forms.input name="internet" id="internet" value="{{ e($values['internet']) }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="span4 control-label">{{ __('label.street') }}</label>
                                <div class="span6">
                                    <x-globals::forms.input name="street" id="street" value="{{ e($values['street']) }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="span4 control-label">{{ __('label.zip') }}</label>
                                <div class="span6">
                                    <x-globals::forms.input name="zip" id="zip" value="{{ e($values['zip']) }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="span4 control-label">{{ __('label.city') }}</label>
                                <div class="span6">
                                    <x-globals::forms.input name="city" id="city" value="{{ e($values['city']) }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="span4 control-label">{{ __('label.state') }}</label>
                                <div class="span6">
                                    <x-globals::forms.input name="state" id="state" value="{{ e($values['state']) }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="span4 control-label">{{ __('label.country') }}</label>
                                <div class="span6">
                                    <x-globals::forms.input name="country" id="country" value="{{ e($values['country']) }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="span4 control-label">{{ __('label.phone') }}</label>
                                <div class="span6">
                                    <x-globals::forms.input name="phone" id="phone" value="{{ e($values['phone']) }}" />
                                </div>
                            </div>

                            @dispatchEvent('beforeSubmitButton')

                            <div class="form-group">
                                <div class="span4 control-label">
                                    <x-globals::forms.button submit type="primary" name="save" id="save">{{ __('buttons.save') }}</x-globals::forms.button>
                                </div>
                                <div class="span6">
                                </div>
                            </div>
                        </div>
                    </div>

                    @dispatchEvent('beforeFormClose')

                </form>
            </div>
        </div>
    </div>
</div>
