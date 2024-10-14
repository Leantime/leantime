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
                                    value="{{ $tpl->escape($values['name']) }}"
                                    labelText="{{ __('label.name') }}"
                                    class="form-control"
                                />
                            </div>

                            <div class="form-group">
                                <x-global::forms.text-input
                                    inputType="text"
                                    name="email"
                                    id="email"
                                    value="{{ $tpl->escape($values['email']) }}"
                                    labelText="{{ __('label.email') }}"
                                    class="form-control"
                                />
                            </div>

                            <div class="form-group">
                                <x-global::forms.text-input
                                    inputType="text"
                                    name="internet"
                                    id="internet"
                                    value="{{ $tpl->escape($values['internet']) }}"
                                    labelText="{{ __('label.url') }}"
                                    class="form-control"
                                />
                            </div>

                            <div class="form-group">
                                <x-global::forms.text-input
                                    inputType="text"
                                    name="street"
                                    id="street"
                                    value="{{ $tpl->escape($values['street']) }}"
                                    labelText="{{ __('label.street') }}"
                                    class="form-control"
                                />
                            </div>

                            <div class="form-group">
                                <x-global::forms.text-input
                                    inputType="text"
                                    name="zip"
                                    id="zip"
                                    value="{{ $tpl->escape($values['zip']) }}"
                                    labelText="{{ __('label.zip') }}"
                                    class="form-control"
                                />
                            </div>

                            <div class="form-group">
                                <x-global::forms.text-input
                                    inputType="text"
                                    name="city"
                                    id="city"
                                    value="{{ $tpl->escape($values['city']) }}"
                                    labelText="{{ __('label.city') }}"
                                    class="form-control"
                                />
                            </div>

                            <div class="form-group">
                                <x-global::forms.text-input
                                    inputType="text"
                                    name="state"
                                    id="state"
                                    value="{{ $tpl->escape($values['state']) }}"
                                    labelText="{{ __('label.state') }}"
                                    class="form-control"
                                />
                            </div>

                            <div class="form-group">
                                <x-global::forms.text-input
                                    inputType="text"
                                    name="country"
                                    id="country"
                                    value="{{ $tpl->escape($values['country']) }}"
                                    labelText="{{ __('label.country') }}"
                                    class="form-control"
                                />
                            </div>

                            <div class="form-group">
                                <x-global::forms.text-input
                                    inputType="text"
                                    name="phone"
                                    id="phone"
                                    value="{{ $tpl->escape($values['phone']) }}"
                                    labelText="{{ __('label.phone') }}"
                                    class="form-control"
                                />
                            </div>

                            <?php $tpl->dispatchTplEvent('beforeSubmitButton'); ?>

                            <div class="form-group">
                                <div class="span4 control-label">
                                    <x-global::forms.button
                                        type="submit"
                                        name="save"
                                        id="save"
                                        class="btn btn-primary"
                                    >
                                        {{ __('buttons.save') }}
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
