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
                <input type="text" name="name" id="name" value="{{ $values['name'] }}" /><br />

                <label for="email">{{ __('EMAIL') }}</label>
                <input type="text" name="email" id="email" value="{{ $values['email'] }}" /><br />

                <label for="internet">{{ __('URL') }}</label> <input
                    type="text" name="internet" id="internet"
                    value="{{ $values['internet'] }}" /><br />

                <label for="street">{{ __('STREET') }}</label> <input
                    type="text" name="street" id="street"
                    value="{{ $values['street'] }}" /><br />

                <label for="zip">{{ __('ZIP') }}</label> <input type="text"
                    name="zip" id="zip" value="{{ $values['zip'] }}" /><br />

                <label for="city">{{ __('CITY') }}</label> <input type="text"
                    name="city" id="city" value="{{ $values['city'] }}" /><br />

                <label for="state">{{ __('STATE') }}</label> <input
                    type="text" name="state" id="state"
                    value="{{ $values['state'] }}" /><br />

                <label for="country">{{ __('COUNTRY') }}</label> <input
                    type="text" name="country" id="country"
                    value="{{ $values['country'] }}" /><br />

                <label for="phone">{{ __('PHONE') }}</label> <input
                    type="text" name="phone" id="phone"
                    value="{{ $values['phone'] }}" /><br />

                <input type="submit" name="save" id="save"
                    value="{{ __('SAVE') }}" class="button" />

                </div>
            </div>

        </form>

    </div>
</div>
