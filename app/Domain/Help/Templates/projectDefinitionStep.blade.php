<div style="max-width:900px;">
    <form class="onboardingModal" method="post" action="{{ BASE_URL }}/help/firstLogin?step={{ $nextStep }}">
        <input type="hidden" name="currentStep" value="{{ $currentStep }}" />
        <div class="row">
            <div class="col-md-8">
                <h1>{{  __('headlines.make_it_happen') }}</h1>
                <p>{!!  __('text.structured_project_thinking') !!}</p>
                <br />
                <label><strong>{{ __('label.what_are_you_trying_to_accomplish') }}</strong></label>
                <x-globals::forms.textarea name="accomplish" id="accomplish" rows="3" style="width:99%; overflow-x: hidden;" />
                <br />
                <label><strong>{{ __('label.how_does_the_world_look_like') }}</strong></label>
                <x-globals::forms.textarea name="worldview" id="wordlview" rows="3" style="width:99%; overflow-x: hidden;" />
                <br />
                <label><strong>{{ __('label.why_is_this_important') }}</strong></label>
                <x-globals::forms.textarea name="whyImportant" id="whyImportant" rows="3" style="width:99%; overflow-x: hidden;" />

            </div>
            <div class="col-md-4">
                <div class='svgContainer' style="width:400px; margin-top:40px;">
                    {!! file_get_contents(ROOT . "/dist/images/svg/undraw_goals_re_lu76.svg") !!}
                </div>
            </div>
        </div>
        <div class="align-right">
            <x-globals::forms.button submit type="primary">{{ __('buttons.next') }}</x-globals::forms.button>
        </div>
    </form>
</div>
