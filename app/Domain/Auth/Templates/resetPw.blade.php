@extends($layout)

@section('content')

@dispatchEvent('beforePageHeaderOpen')
<div class="pageheader">
    @dispatchEvent('afterPageHeaderOpen')
    <div class="pagetitle">
        <h1>{{ __('headlines.reset_password') }}</h1>
    </div>
    @dispatchEvent('beforePageHeaderClose')
</div>
@dispatchEvent('afterPageHeaderClose')

<div class="regcontent">
    @dispatchEvent('afterRegcontentOpen')
    <form id="resetPassword" action="" method="post">
        @dispatchEvent('afterFormOpen')

        {!! $tpl->displayInlineNotification() !!}

        <p>{{ __('text.enter_new_password') }}<br /><br /></p>

        <div class="">
            <x-global::forms.input type="password" autocomplete="off" name="password" id="password" placeholder="{{ __('input.placeholders.enter_new_password') }}" />
            <span id="pwStrength" style="width:100%;"></span>
        </div>
        <div class="">
            <x-global::forms.input type="password" autocomplete="off" name="password2" id="password2" placeholder="{{ __('input.placeholders.confirm_password') }}" />
        </div>
        <small>{{ __('label.passwordRequirements') }}</small><br /><br />
        <div class="">
            @dispatchEvent('beforeSubmitButton')
            <x-global::button submit type="primary" name="resetPassword">{{ __('buttons.reset_password') }}</x-global::button>
            <div class="forgotPwContainer">
                <a href="{{ BASE_URL }}/" class="forgotPw">{{ __('links.back_to_login') }}</a>
            </div>
        </div>
        @dispatchEvent('beforeFormClose')
    </form>
    @dispatchEvent('beforeRegcontentClose')
</div>

<script>
jQuery(document).ready(function(){
    leantime.usersController.checkPWStrength('password');
});
</script>

@endsection
