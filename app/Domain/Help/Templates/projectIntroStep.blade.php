<form class="onboardingModal" method="post" action="{{ BASE_URL }}/help/firstLogin?step={{ $nextStep }}">
    <input type="hidden" name="currentStep" value="{{ $currentStep }}" />
    <div class="row">
        <div class="col-md-6">
            <h1>{{  __('headlines.hi_there') }}</h1>
            <p>{{ __('text.first_login_intro') }}</p>
            <br />
            <label>{{ __('label.name_your_first_project') }}</label>
            <input type="text" id="projectName" name="projectname" value="" placeholder=""/><br />

            <input type="submit" value="{{ __('buttons.next') }}"/>
            <a href="javascript:void(0);" class="btn btn-secondary" onclick="skipOnboarding();">{{ __('links.skip_for_now') }}</a>
        </div>
        <div class="col-md-6">
            <div class='svgContainer' style="width:300px">
                {!! file_get_contents(ROOT . "/dist/images/svg/undraw_game_day_ucx9.svg") !!}
            </div>
        </div>
    </div>
</form>
