@extends($layout)

@section('content')

    <x-auth::onboardingProgress current="" />

<h2>🎉 {{ __('headlines.onboarding_journey_begins') }}</h2>

<div class="regcontent">

    <form id="resetPassword" action="" method="post">

        <input type="hidden" name="step" value="5"/>
        <input type="hidden" name="complete" value="1"/>

        {{  $tpl->displayInlineNotification() }}

        <div class="row">
            <div class="col-md-6">
                <x-global::elements.card>
                    <span class="fancyLink">{{ __('label.did_you_know') }}</span><br />
                    <span style="font-size:16px;">{!! __('text.onboarding_intentions_fact') !!}</span>
                </x-global::elements.card>
            </div>
            <div class="col-md-6">
                <x-global::undrawSvg image="undraw_adventure_map_hnin.svg" maxWidth="60%" maxHeight="300px"></x-global::undrawSvg>
            </div>
        </div>

        <p><br />{!! __('text.onboarding_next_steps') !!}</p> <br />

        <br />
        <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.complete_sign_up')" name="createAccount" />


    </form>

</div>

@endsection
