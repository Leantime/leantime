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
        <h5>Administration</h5>
        <h1>{{ __("EDIT_CLIENT") }}</h1>
    </div>
    <?php $tpl->dispatchTplEvent('beforePageHeaderClose'); ?>
</div><!--pageheader-->
<?php $tpl->dispatchTplEvent('afterPageHeaderClose'); ?>

<div class="maincontent">
    <div class="maincontentinner">

        @displayNotification()

        <form action="" method="post" class="stdform">

            <div class="widget">
            <h4 class="widgettitle">{{ __("OVERVIEW") }}</h4>
            <div class="widgetcontent">

                <x-global::forms.text-input 
                    type="text" 
                    name="name" 
                    id="name" 
                    value="{{ $values['name'] }}" 
                    labelText="{{ __('NAME') }}" 
                />
                
                <x-global::forms.text-input 
                    type="text" 
                    name="email" 
                    id="email" 
                    value="{{ $values['email'] }}" 
                    labelText="{{ __('EMAIL') }}" 
                />
                
                <x-global::forms.text-input 
                    type="text" 
                    name="internet" 
                    id="internet" 
                    value="{{ $values['internet'] }}" 
                    labelText="{{ __('URL') }}" 
                />
                
                <x-global::forms.text-input 
                    type="text" 
                    name="street" 
                    id="street" 
                    value="{{ $values['street'] }}" 
                    labelText="{{ __('STREET') }}" 
                />
                
                <x-global::forms.text-input 
                    type="text" 
                    name="zip" 
                    id="zip" 
                    value="{{ $values['zip'] }}" 
                    labelText="{{ __('ZIP') }}" 
                />
                
                <x-global::forms.text-input 
                    type="text" 
                    name="city" 
                    id="city" 
                    value="{{ $values['city'] }}" 
                    labelText="{{ __('CITY') }}" 
                />
                
                <x-global::forms.text-input 
                    type="text" 
                    name="state" 
                    id="state" 
                    value="{{ $values['state'] }}" 
                    labelText="{{ __('STATE') }}" 
                />
                
                <x-global::forms.text-input 
                    type="text" 
                    name="country" 
                    id="country" 
                    value="{{ $values['country'] }}" 
                    labelText="{{ __('COUNTRY') }}" 
                />
                
                <x-global::forms.text-input 
                    type="text" 
                    name="phone" 
                    id="phone" 
                    value="{{ $values['phone'] }}" 
                    labelText="{{ __('PHONE') }}" 
                />
                
                <x-global::forms.button 
                    type="submit" 
                    name="save" 
                    id="save" 
                    content-role="primary"
                >
                    {{ __('SAVE') }}
                </x-global::forms.button>
                </div>
            </div>

        </form>

    </div>
</div>
