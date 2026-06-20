@extends($layout)
@section('content')

@dispatchEvent('beforePageHeaderOpen')
<div class="pageheader">
    @dispatchEvent('afterPageHeaderOpen')
    <div class="pageicon"><span class="fa {{ $tpl->getModulePicture() }}"></span></div>
    <div class="pagetitle">
        <h5>Administration</h5>
        <h1>{!! __('EDIT_CLIENT') !!}</h1>
    </div>
    @dispatchEvent('beforePageHeaderClose')
</div><!--pageheader-->
@dispatchEvent('afterPageHeaderClose')

<div class="maincontent">
    <div class="maincontentinner">

        {!! $tpl->displayNotification() !!}

        <form action="" method="post" class="stdform">

            <div class="widget">
            <h4 class="widgettitle">{!! __('OVERVIEW') !!}</h4>
            <div class="widgetcontent">

                <label for="name">{!! __('NAME') !!}</label>
                <x-global::forms.text-input name="name" id="name" value="{{ $values['name'] }}" /><br />

                <label for="email">{!! __('EMAIL') !!}</label>
                <x-global::forms.text-input name="email" id="email" value="{{ $values['email'] }}" /><br />

                <label for="internet">{!! __('URL') !!}</label> <x-global::forms.text-input
                    name="internet" id="internet"
                    value="{{ $values['internet'] }}" /><br />

                <label for="street">{!! __('STREET') !!}</label> <x-global::forms.text-input
                    name="street" id="street"
                    value="{{ $values['street'] }}" /><br />

                <label for="zip">{!! __('ZIP') !!}</label> <x-global::forms.text-input
                    name="zip" id="zip" value="{{ $values['zip'] }}" /><br />

                <label for="city">{!! __('CITY') !!}</label> <x-global::forms.text-input
                    name="city" id="city" value="{{ $values['city'] }}" /><br />

                <label for="state">{!! __('STATE') !!}</label> <x-global::forms.text-input
                    name="state" id="state"
                    value="{{ $values['state'] }}" /><br />

                <label for="country">{!! __('COUNTRY') !!}</label> <x-global::forms.text-input
                    name="country" id="country"
                    value="{{ $values['country'] }}" /><br />

                <label for="phone">{!! __('PHONE') !!}</label> <x-global::forms.text-input
                    name="phone" id="phone"
                    value="{{ $values['phone'] }}" /><br />

                <input type="submit" name="save" id="save"
                    value="{{ __('SAVE') }}" class="button" />

                </div>
            </div>

        </form>

    </div>
</div>

@endsection
