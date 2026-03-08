@extends($layout)

@section('content')

<x-globals::layout.page-header icon="lock" headline="{{ __('label.twoFA') }}" />

<div class="maincontent">
    <div class="maincontentinner">

        {!! $tpl->displayNotification() !!}

        <div class="row-fluid">
            <div class="span12">

                <h3>{{ __('label.twoFA_setup') }}</h3>
                <br />
                <div class="center">

                    @if(! $tpl->get('twoFAEnabled'))
                        <h5>1. {{ __('text.twoFA_qr') }}</h5>
                        <br />
                        <img src="{{ $tpl->get('qrData') }}" style="border-radius: var(--box-radius);"/><br />
                        Secret: <p>{{ $tpl->get('secret') }}</p><br/>
                        <form action="" method="post">
                            <h5>2. {{ __('text.twoFA_verify_code') }}</h5>
                            <p>
                                <span>{{ __('label.twoFACode_short') }}:</span>
                                <x-globals::forms.text-input name="twoFACode" id="twoFACode" /><br/>
                            </p>

                            <input type="hidden" name="secret" value="{{ $tpl->get('secret') }}" />
                            <br/>
                            <p class="stdformbutton">
                                <x-globals::forms.button :submit="true" contentRole="primary" name="save" id="save">{{ __('buttons.save') }}</x-globals::forms.button>
                            </p>
                        </form>
                    @else
                        <form action="" method="post">
                            <h5>{{ __('text.twoFA_already_enabled') }}</h5>
                            <input type="hidden" name="{{ session('formTokenName') }}" value="{{ session('formTokenValue') }}" />
                            <p class="stdformbutton">
                                <x-globals::forms.button :submit="true" contentRole="primary" name="disable" id="disable">{{ __('buttons.remove') }}</x-globals::forms.button>
                                <x-globals::forms.button element="a" href="{{ BASE_URL }}/users/editOwn" contentRole="secondary">{{ __('buttons.back') }}</x-globals::forms.button>
                            </p>
                        </form>
                    @endif
                </div>

            </div>
        </div>
    </div>
</div>

@endsection
