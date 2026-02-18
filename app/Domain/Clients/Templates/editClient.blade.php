@php
    $values = $tpl->get('values');
@endphp

@dispatchEvent('beforePageHeaderOpen')
<div class="pageheader">
    @dispatchEvent('afterPageHeaderOpen')
    <div class="pageicon"><span class="fa {{ $tpl->getModulePicture() }}"></span></div>
    <div class="pagetitle">
        <h5>Administration</h5>
        <h1>{{ __('EDIT_CLIENT') }}</h1>
    </div>
    @dispatchEvent('beforePageHeaderClose')
</div>
@dispatchEvent('afterPageHeaderClose')

<div class="maincontent">
    <div class="maincontentinner">

        {!! $tpl->displayNotification() !!}

        <form action="" method="post" class="stdform">

            <div class="widget">
            <h4 class="widgettitle">{{ __('OVERVIEW') }}</h4>
            <div class="widgetcontent">

                <label for="name">{{ __('NAME') }}</label>
                <x-global::forms.input name="name" id="name" value="{{ $values['name'] }}" /><br />

                <label for="email">{{ __('EMAIL') }}</label>
                <x-global::forms.input name="email" id="email" value="{{ $values['email'] }}" /><br />

                <label for="internet">{{ __('URL') }}</label> <x-global::forms.input name="internet" id="internet" value="{{ $values['internet'] }}" /><br />

                <label for="street">{{ __('STREET') }}</label> <x-global::forms.input name="street" id="street" value="{{ $values['street'] }}" /><br />

                <label for="zip">{{ __('ZIP') }}</label> <x-global::forms.input name="zip" id="zip" value="{{ $values['zip'] }}" /><br />

                <label for="city">{{ __('CITY') }}</label> <x-global::forms.input name="city" id="city" value="{{ $values['city'] }}" /><br />

                <label for="state">{{ __('STATE') }}</label> <x-global::forms.input name="state" id="state" value="{{ $values['state'] }}" /><br />

                <label for="country">{{ __('COUNTRY') }}</label> <x-global::forms.input name="country" id="country" value="{{ $values['country'] }}" /><br />

                <label for="phone">{{ __('PHONE') }}</label> <x-global::forms.input name="phone" id="phone" value="{{ $values['phone'] }}" /><br />

                <x-global::button submit type="primary" name="save" id="save">{{ __('SAVE') }}</x-global::button>

                </div>
            </div>

        </form>

    </div>
</div>
