@extends($layout)
@section('content')

@dispatchEvent('beforePageHeaderOpen')
<div class="pageheader">
    @dispatchEvent('afterPageHeaderOpen')
    <div class="pagetitle">
        <h1>{!! __('headlines.login') !!}</h1>
    </div>
    @dispatchEvent('beforePageHeaderClose')
</div>
@dispatchEvent('afterPageHeaderClose')

<div class="regcontent">
    @dispatchEvent('afterRegcontentOpen')
    {!! $tpl->displayInlineNotification() !!}

    @if ($noLoginForm === false)
        <form id="login" action="{{ BASE_URL }}/auth/login" method="post">
            @csrf
            @dispatchEvent('afterFormOpen')
        <input type="hidden" name="redirectUrl" value="{{ $redirectUrl }}" />

        <div class="">
            <label for="username">Email</label>
            <x-global::forms.text-input variant="form" name="username" id="username" placeholder="{{ __($inputPlaceholder) }}" value="" />
        </div>
        <div class="">
            <label for="password">Password</label>
            <x-global::forms.text-input inputType="password" variant="form" name="password" id="password" autocomplete="off" placeholder="{{ __('input.placeholders.enter_password') }}" value="" />
            <div class="forgotPwContainer">
                <a href="{{ BASE_URL }}/auth/resetPw" class="forgotPw">{!! __('links.forgot_password') !!}</a>
            </div>
        </div>
            @dispatchEvent('beforeSubmitButton')
        <div class="">
            <x-global::forms.button tag="input" inputType="submit" name="login" contentRole="primary" :labelText="__('buttons.login')" />
        </div>
        <div>
        </div>
            @dispatchEvent('beforeFormClose')

    </form>
    @else
        {!! __('text.no_login_form') !!}<br /><br />
    @endif

    @if ($oidcEnabled)

        @dispatchEvent('beforeOidcButton')

        <div class="">
            <div style="margin-top:20px; border-bottom:1px solid #ccc; with:100%; height:10px; overflow:show; text-align:center; margin-bottom:40px;">
                <p style="text-align:center; display:inline-block; background:var(--secondary-background); padding:0px 5px;">{!! __('label.or_login_with') !!}</p>
            </div>
            <x-global::forms.button tag="a" :link="BASE_URL . '/oidc/login'" contentRole="primary" style="width:100%;">{!! __('buttons.oidclogin') !!}</x-global::forms.button>
        </div>
    @endif

    @dispatchEvent('beforeRegcontentClose')
</div>

@endsection
