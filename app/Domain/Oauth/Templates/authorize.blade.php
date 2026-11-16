@extends($layout)
@section('content')

@if (! empty($expired))

    <x-global::layout.page-header>
        {{ __('oauth.headline.request_expired') }}
    </x-global::layout.page-header>

    <div class="regcontent">
        <p>{{ __('oauth.text.request_expired') }}</p>
    </div>

@else

    <x-global::layout.page-header>
        {{ sprintf(__('oauth.headline.connect_app'), $clientName) }}
    </x-global::layout.page-header>

    <div class="regcontent oauth-consent">

        <p>
            {!! sprintf(e(__('oauth.text.wants_access')), '<strong>'.e($clientName).'</strong>') !!}
        </p>

        <p><strong>{{ __('oauth.text.it_will_be_able_to') }}</strong></p>
        <ul class="tw-list-disc tw-pl-6 tw-mb-4">
            @foreach ($scopes as $scopeDescriptionKey)
                <li>{{ __($scopeDescriptionKey) }}</li>
            @endforeach
        </ul>

        <x-global::feedback.alert tone="warning">
            {!! sprintf(e(__('oauth.text.unverified_app')), '<strong>'.e($redirectHost).'</strong>') !!}
        </x-global::feedback.alert>

        <p class="tw-mt-4">
            {{ sprintf(__('oauth.text.signed_in_as'), $userName !== '' ? $userName.' ('.$userEmail.')' : $userEmail) }}
            <a href="{{ BASE_URL }}/auth/logout">{{ __('oauth.links.not_you') }}</a>
        </p>

        <div class="tw-flex tw-gap-2 tw-mt-6">
            <form method="post" action="{{ BASE_URL }}/oauth/authorize">
                @csrf
                <input type="hidden" name="auth_token" value="{{ $authToken }}" />
                <x-global::forms.button inputType="submit" contentRole="primary" :labelText="__('oauth.buttons.allow')" />
            </form>
            <form method="post" action="{{ BASE_URL }}/oauth/authorize/deny">
                @csrf
                <input type="hidden" name="auth_token" value="{{ $authToken }}" />
                <x-global::forms.button inputType="submit" contentRole="secondary" :labelText="__('oauth.buttons.deny')" />
            </form>
        </div>

        <p class="tw-mt-4" style="font-size: var(--font-size-s);">
            {{ sprintf(__('oauth.text.redirect_after'), $redirectHost) }}
        </p>
    </div>

@endif

@endsection
