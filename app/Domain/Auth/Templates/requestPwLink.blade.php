@extends($layout)

@section('content')

@dispatchEvent('beforePageHeaderOpen')
<div class="pageheader">
    <div class="pagetitle">
        <h1>{{ __('headlines.reset_password') }}</h1>
    </div>
</div>
@dispatchEvent('afterPageHeaderClose')

<div class="regcontent">
    @dispatchEvent('afterRegcontentOpen')
    <form id="resetPassword" action="" method="post">
        @dispatchEvent('afterFormOpen')
        {!! $tpl->displayInlineNotification() !!}
        <p>{{ __('text.enter_email_address_to_reset') }}<br /><br /></p>
        <div class="">
            <input type="text" name="username" id="username" placeholder="{{ __('input.placeholders.enter_email') }}" />
        </div>
        <div class="">
            <div class="forgotPwContainer">
                <a href="{{ BASE_URL }}/" class="forgotPw">{{ __('links.back_to_login') }}</a>
            </div>
            @dispatchEvent('beforeSubmitButton')
            <input type="submit" name="resetPassword" value="{{ __('buttons.reset_password') }}" />
        </div>
        @dispatchEvent('beforeFormClose')
    </form>
    @dispatchEvent('beforeRegcontentClose')
</div>

@endsection
