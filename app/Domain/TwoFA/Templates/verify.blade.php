@extends($layout)

@section('content')

<x-global::layout.page-header>
    {!! __('headlines.twoFA_login') !!}
</x-global::layout.page-header>
<div class="regcontent">
    <form id="login" action="{{ BASE_URL }}/twoFA/verify" method="post">
        <input type="hidden" name="redirectUrl" value="{{ $redirectUrl }}"/>

        {!! $tpl->displayInlineNotification() !!}

        <div class="">
            <x-global::forms.text-input name="twoFA_code" id="twoFA_code"
                   placeholder="{{ __('label.twoFACode') }}"
                   value="" autofocus />
        </div>
        <div class="">
            <div class="forgotPwContainer">
                <a href="{{ BASE_URL }}/auth/logout" class="forgotPw">{!! __('menu.sign_out') !!}</a>
            </div>
            <x-global::forms.button tag="input" inputType="submit" name="login" :labelText="__('buttons.login')"
                   contentRole="primary"/>
        </div>
    </form>
</div>

@endsection
