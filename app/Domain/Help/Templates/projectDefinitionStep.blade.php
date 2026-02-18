<div style="max-width:900px;">
    <form class="onboardingModal" method="post" action="{{ BASE_URL }}/help/firstLogin?step={{ $nextStep }}">
        <input type="hidden" name="currentStep" value="{{ $currentStep }}" />
        <div class="tw:grid tw:grid-cols-[2fr_1fr] tw:gap-6">
            <div>
                <h1>{{  __('headlines.make_it_happen') }}</h1>
                <p>{!!  __('text.structured_project_thinking') !!}</p>
                <br />
                <label><strong>{{ __('label.what_are_you_trying_to_accomplish') }}</strong></label>
                <x-global::forms.textarea name="accomplish" id="accomplish" rows="3" style="width:99%; overflow-x: hidden;" />
                <br />
                <label><strong>{{ __('label.how_does_the_world_look_like') }}</strong></label>
                <x-global::forms.textarea name="worldview" id="wordlview" rows="3" style="width:99%; overflow-x: hidden;" />
                <br />
                <label><strong>{{ __('label.why_is_this_important') }}</strong></label>
                <x-global::forms.textarea name="whyImportant" id="whyImportant" rows="3" style="width:99%; overflow-x: hidden;" />

            </div>
            <div>
                <div class='svgContainer' style="width:400px; margin-top:40px;">
                    {!! file_get_contents(ROOT . "/dist/images/svg/undraw_goals_re_lu76.svg") !!}
                </div>
            </div>
        </div>
        <div class="tw:text-right">
            <x-global::button submit type="primary">{{ __('buttons.next') }}</x-global::button>
        </div>
    </form>
</div>
