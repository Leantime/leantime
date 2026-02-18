@extends($layout)

@section('content')

@dispatchEvent('beforePageHeaderOpen')
<div class="pageheader">
    @dispatchEvent('afterPageHeaderOpen')
    <div class="pagetitle">
        <h1>{{ __('headlines.login') }}</h1>
    </div>
    @dispatchEvent('beforePageHeaderClose')
</div>
@dispatchEvent('afterPageHeaderClose')

<div class="regcontent">
    @dispatchEvent('afterRegcontentOpen')
    {!! $tpl->displayInlineNotification() !!}

    @if($tpl->get('noLoginForm') === false)
        <form id="login" action="{{ BASE_URL }}/auth/login" method="post">
            @dispatchEvent('afterFormOpen')
            <input type="hidden" name="redirectUrl" value="{{ $tpl->get('redirectUrl') }}" />

            <div class="">
                <label for="username">Email</label>
                <x-global::forms.input name="username" id="username" placeholder="{{ __($tpl->get('inputPlaceholder')) }}" value="" />
            </div>
            <div class="">
                <label for="password">Password</label>
                <x-global::forms.input type="password" name="password" id="password" autocomplete="off" placeholder="{{ __('input.placeholders.enter_password') }}" value="" />
                <div class="forgotPwContainer">
                    <a href="{{ BASE_URL }}/auth/resetPw" class="forgotPw">{{ __('links.forgot_password') }}</a>
                </div>
            </div>
            @dispatchEvent('beforeSubmitButton')
            <div class="">
                <x-global::button submit type="primary" name="login">{{ __('buttons.login') }}</x-global::button>
            </div>
            <div>
            </div>
            @dispatchEvent('beforeFormClose')
        </form>
    @else
        {{ __('text.no_login_form') }}<br /><br />
    @endif

    @if($tpl->get('oidcEnabled'))
        @dispatchEvent('beforeOidcButton')
        <div class="">
            <div style="margin-top:20px; border-bottom:1px solid #ccc; width:100%; height:10px; overflow:show; text-align:center; margin-bottom:40px;">
                <p style="text-align:center; display:inline-block; background:var(--secondary-background); padding:0px 5px;">{{ __('label.or_login_with') }}</p>
            </div>
            <x-global::button link="{{ BASE_URL }}/oidc/login" type="primary" style="width:100%;">
                {{ __('buttons.oidclogin') }}
            </x-global::button>
        </div>
    @endif

    @dispatchEvent('beforeRegcontentClose')
</div>

@endsection
