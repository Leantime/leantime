@extends($layout)

@section('content')

<x-globals::layout.page-header headline="{{ __('headlines.reset_password') }}" />

<div class="regcontent">
    @dispatchEvent('afterRegcontentOpen')
    <form id="resetPassword" action="" method="post">
        @dispatchEvent('afterFormOpen')
        {!! $tpl->displayInlineNotification() !!}
        <p>{{ __('text.enter_email_address_to_reset') }}<br /><br /></p>
        <div class="">
            <x-globals::forms.text-input name="username" id="username" placeholder="{{ __('input.placeholders.enter_email') }}" />
        </div>
        <div class="">
            <div class="forgotPwContainer">
                <a href="{{ BASE_URL }}/" class="forgotPw">{{ __('links.back_to_login') }}</a>
            </div>
            @dispatchEvent('beforeSubmitButton')
            <x-globals::forms.button submit type="primary" name="resetPassword">{{ __('buttons.reset_password') }}</x-globals::forms.button>
        </div>
        @dispatchEvent('beforeFormClose')
    </form>
    @dispatchEvent('beforeRegcontentClose')
</div>

@endsection
