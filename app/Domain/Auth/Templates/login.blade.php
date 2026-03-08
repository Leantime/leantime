@extends($layout)

@section('content')

<x-globals::layout.page-header headline="{{ __('headlines.login') }}" />

<div class="regcontent">
    @dispatchEvent('afterRegcontentOpen')
    {!! $tpl->displayInlineNotification() !!}

    @if($tpl->get('noLoginForm') === false)
        <form id="login" action="{{ BASE_URL }}/auth/login" method="post">
            @dispatchEvent('afterFormOpen')
            <input type="hidden" name="redirectUrl" value="{{ $tpl->get('redirectUrl') }}" />

            <div class="">
                <label for="username">Email</label>
                <x-globals::forms.text-input name="username" id="username" placeholder="{{ __($tpl->get('inputPlaceholder')) }}" value="" />
            </div>
            <div class="">
                <label for="password">Password</label>
                <x-globals::forms.text-input type="password" name="password" id="password" autocomplete="off" placeholder="{{ __('input.placeholders.enter_password') }}" value="" />
                <div class="forgotPwContainer">
                    <a href="{{ BASE_URL }}/auth/resetPw" class="forgotPw">{{ __('links.forgot_password') }}</a>
                </div>
            </div>
            @dispatchEvent('beforeSubmitButton')
            <div class="">
                <x-globals::forms.button contentRole="primary" :submit="true" name="login">{{ __('buttons.login') }}</x-globals::forms.button>
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
            <div class="tw:mt-5 tw:border-b tw:border-gray-300 tw:w-full tw:h-[10px] tw:text-center tw:mb-10 tw:overflow-visible">
                <p class="tw:text-center tw:inline-block tw:bg-[var(--secondary-background)] tw:px-1">{{ __('label.or_login_with') }}</p>
            </div>
            <x-globals::forms.button element="a" href="{{ BASE_URL }}/oidc/login" contentRole="primary" class="tw:w-full">
                {{ __('buttons.oidclogin') }}
            </x-globals::forms.button>
        </div>
    @endif

    @dispatchEvent('beforeRegcontentClose')
</div>

@endsection
