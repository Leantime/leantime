@extends($layout)

@section('content')

<x-globals::layout.page-header headline="{{ __('headlines.twoFA_login') }}" />

<div class="regcontent">
    <form id="login" action="{{ BASE_URL }}/twoFA/verify" method="post">
        <input type="hidden" name="redirectUrl" value="{{ $tpl->get('redirectUrl') }}"/>

        {!! $tpl->displayInlineNotification() !!}

        <div class="">
            <x-globals::forms.text-input name="twoFA_code" id="twoFA_code"
                   placeholder="{{ __('label.twoFACode') }}"
                   value="" autofocus />
        </div>
        <div class="">
            <div class="forgotPwContainer">
                <a href="{{ BASE_URL }}/auth/logout" class="forgotPw">{{ __('menu.sign_out') }}</a>
            </div>
            <x-globals::forms.button :submit="true" contentRole="primary" name="login">{{ __('buttons.login') }}</x-globals::forms.button>
        </div>
    </form>
</div>

@endsection
