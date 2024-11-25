@extends($layout)

@section('content')

<?php
$values = $tpl->get('values');
?>

<?php $tpl->dispatchTplEvent('beforePageHeaderOpen'); ?>
<div class="pageheader">
    <?php $tpl->dispatchTplEvent('afterPageHeaderOpen'); ?>
    <div class="pageicon"><span class="fa fa-address-book"></span></div>
    <div class="pagetitle">
        <h5>{{ __("label.administration") }}</h5>
        <h1>{{ __("headline.new_client") }}</h1>
    </div>
    <?php $tpl->dispatchTplEvent('beforePageHeaderClose'); ?>
</div><!--pageheader-->
<?php $tpl->dispatchTplEvent('afterPageHeaderClose'); ?>

<div class="maincontent">
    <div class="maincontentinner">

        @displayNotification()

        <div class="widget">
           <h4 class="widgettitle">{{ __("subtitle.details") }}</h4>
           <div class="widgetcontent">

                <form action="" method="post" class="stdform">

                    <?php $tpl->dispatchTplEvent('afterFormOpen'); ?>

                    <div class="row row-fluid">
                        <div class="col-md-6">
                            <div class="form-group">
                                <x-global::forms.text-input
                                    inputType="text"
                                    name="name"
                                    id="name"
                                    value="{{ $values->name }}"
                                    labelText="{{ __('label.name') }}"
                                />
                            </div>

                            <div class="form-group">
                                <x-global::forms.text-input
                                    inputType="text"
                                    name="email"
                                    id="email"
                                    value="{{ $values->email }}"
                                    labelText="{{ __('label.email') }}"
                                />
                            </div>

                            <div class="form-group">
                                <x-global::forms.text-input
                                    inputType="text"
                                    name="internet"
                                    id="internet"
                                    value="{{ $values->internet }}"
                                    labelText="{{ __('label.url') }}"
                                />
                            </div>

                            <div class="form-group">
                                <x-global::forms.text-input
                                    inputType="text"
                                    name="street"
                                    id="street"
                                    value="{{ $values->street }}"
                                    labelText="{{ __('label.street') }}"
                                />
                            </div>

                            <div class="form-group">
                                <x-global::forms.text-input
                                    inputType="text"
                                    name="zip"
                                    id="zip"
                                    value="{{ $values->zip }}"
                                    labelText="{{ __('label.zip') }}"
                                />
                            </div>

                            <div class="form-group">
                                <x-global::forms.text-input
                                    inputType="text"
                                    name="city"
                                    id="city"
                                    value="{{ $values->city }}"
                                    labelText="{{ __('label.city') }}"
                                />
                            </div>

                            <div class="form-group">
                                <x-global::forms.text-input
                                    inputType="text"
                                    name="state"
                                    id="state"
                                    value="{{ $values->state }}"
                                    labelText="{{ __('label.state') }}"
                                />
                            </div>

                            <div class="form-group">
                                <x-global::forms.text-input
                                    inputType="text"
                                    name="country"
                                    id="country"
                                    value="{{ $values->country }}"
                                    labelText="{{ __('label.country') }}"
                                />
                            </div>

                            <div class="form-group">
                                <x-global::forms.text-input
                                    inputType="text"
                                    name="phone"
                                    id="phone"
                                    value="{{ $values->phone }}"
                                    labelText="{{ __('label.phone') }}"
                                />
                            </div>

                            <?php $tpl->dispatchTplEvent('beforeSubmitButton'); ?>

                            <div class="form-group">
                                <div class="mt-4 span4 control-label">
                                    <x-global::forms.button
                                        type="submit"
                                        name="save"
                                        value="true"
                                        id="save"
                                        content-role="primary"
                                    >
                                        {{ __('buttons.save') }}
                                    </x-global::forms.button>

                                    <x-global::forms.button 
                                        tag="a" 
                                        href="/clients/showAll" 
                                        content-role="tertiary"
                                    >
                                        {{ __('buttons.cancel') }}
                                    </x-global::forms.button>
                                </div>
                                <div class="span6">
                                </div>
                            </div>
                        </div>

                    </div>

                    <?php $tpl->dispatchTplEvent('beforeFormClose'); ?>

                </form>
            </div>
        </div>
    </div>
</div>

@endsection