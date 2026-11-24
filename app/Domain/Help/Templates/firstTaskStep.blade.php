<div style="max-width:700px;">
    <form class="onboardingModal" method="post" id="firstTaskOnboarding" action="{{ BASE_URL }}/help/firstLogin?step={{ $nextStep }}">
        <input type="hidden" name="currentStep" value="{{ $currentStep }}" />
        <div class="row">
            <div class="col-md-8">
                <h1>{{ __('headlines.welcome_to_leantime') }}</h1>
                <p>{{ __('text.lets_start_with_first_task') }}</p>
                <br />
                <x-global::forms.field for="firstTask" :label="__('label.whats_one_thing_to_do_today')" :help="__('text.first_task_help')">
                    <x-global::forms.text-input width="full" id="firstTask" name="headline" value="" placeholder="{{ __('input.placeholder.finish_slide_deck') }}" aria-describedby="firstTask-help" required />
                </x-global::forms.field>
                <br />
                <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.lets_go')" />

            </div>
            <div class="col-md-4">
                <div class='svgContainer' style="width:300px; margin-top:40px;">
                    {!! file_get_contents(ROOT . "/dist/images/svg/undraw_happy_news_re_tsbd.svg") !!}
                </div>
            </div>
        </div>
        <div class="row">

        </div>
    </form>
</div>
