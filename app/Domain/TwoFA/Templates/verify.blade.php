@extends($layout)

@section('content')

<div class="pageheader">
    <div class="pagetitle">
        <h1>{{ __('headlines.twoFA_login') }}</h1>
    </div>
</div>

<div class="regcontent">
    <form id="login" action="{{ BASE_URL }}/twoFA/verify" method="post">
        <input type="hidden" name="redirectUrl" value="{{ $tpl->get('redirectUrl') }}"/>

        {!! $tpl->displayInlineNotification() !!}

        <div class="">
            <input type="text" name="twoFA_code" id="twoFA_code" class="form-control"
                   placeholder="{{ __('label.twoFACode') }}"
                   value="" autofocus/>
        </div>
        <div class="">
            <div class="forgotPwContainer">
                <a href="{{ BASE_URL }}/auth/logout" class="forgotPw">{{ __('menu.sign_out') }}</a>
            </div>
            <input type="submit" name="login" value="{{ __('buttons.login') }}"
                   class="btn btn-primary"/>
        </div>
    </form>
</div>

@endsection
