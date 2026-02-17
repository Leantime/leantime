<div style="max-width:700px;">
    <form class="onboardingModal" method="post" id="projectTitleOnboarding" action="{{ BASE_URL }}/help/firstLogin?step={{ $nextStep }}">
        <input type="hidden" name="currentStep" value="{{ $currentStep }}" />
        <div class="tw:grid tw:grid-cols-[2fr_1fr] tw:gap-6">
            <div>
                <h1>{{  __('headlines.hi_there') }}</h1>
                <p>{!!  __('text.get_organized_with_projects') !!}</p>
                <br />
                <label>{{ __('label.start_with_project_title') }}</label>
                <input type="text" id="projectName" name="projectname" value="" placeholder="" style="width:100%;"/><br />

            </div>
            <div>
                <div class='svgContainer' style="width:300px; margin-top:40px;">
                    {!! file_get_contents(ROOT . "/dist/images/svg/undraw_game_day_ucx9.svg") !!}
                </div>
            </div>
        </div>
        <div class="tw:text-right">
            <input type="submit" value="{{ __('buttons.next') }}" />
        </div>
    </form>
</div>
