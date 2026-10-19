@extends($layout)

@section('content')

<x-global::layout.page-header icon="fa fa-lock">
    {!! __('label.twoFA') !!}
</x-global::layout.page-header>

<div class="maincontent">
    <x-global::layout.content-box>

        {!! $tpl->displayNotification() !!}

        <div class="row-fluid">
            <div class="span12">

                    <h3>{!! __('label.twoFA_setup') !!}</h3>
                <br />
                        <div class="center">

                        @if (! $twoFAEnabled)
                            <h5>1. {!! __('text.twoFA_qr') !!}</h5>
                            <br />
                            <img src="{{ $qrData }}" style="border-radius: var(--box-radius);"/><br />
                            {{ __('label.secret_colon') }} <p>{{ $secret }}</p><br/>
                            <form action="" method="post" class='stdform'>
                                <h5>2. {!! __('text.twoFA_verify_code') !!}</h5>
                                <p>
                                    <span>{!! __('label.twoFACode_short') !!}:</span>
                                    <x-global::forms.text-input name="twoFACode" id="twoFACode" /><br/>
                                </p>

                                <input type="hidden" name="secret" value="{{ $secret }}" />
                                <br/>
                                <p class='stdformbutton'>
                                    <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.save')" name="save" id="save" />
                                </p>
                            </form>
                        @else
                            <form action="" method="post" class='stdform'>
                                <h5>{!! __('text.twoFA_already_enabled') !!}</h5>
                                <input type="hidden" name="{{ session('formTokenName') }}" value="{{ session('formTokenValue') }}" />
                                <p class='stdformbutton'>
                                    <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.remove')" name="disable" id="disable" />
                                    <x-global::forms.button tag="a" link="{{ BASE_URL }}/users/editOwn">{!! __('buttons.back') !!}</x-global::forms.button>
                                </p>
                            </form>
                        @endif
                        </div>

            </div>
        </div>
    </x-global::layout.content-box>
</div>

@endsection
