<div style="max-width:700px;">
    <form class="onboardingModal" method="post" id="projectTitleOnboarding" action="{{ BASE_URL }}/help/firstLogin?step={{ $nextStep }}">
        <input type="hidden" name="currentStep" value="{{ $currentStep }}" />
        <div class="row">
            <div class="col-md-8">
                <h1>{{  __('headlines.hi_there') }}</h1>
                <p>{!!  __('text.get_organized_with_projects') !!}</p>
                <br />
                <label>{{ __('label.start_with_project_title') }}</label>
                <x-global::forms.text-input 
                    type="text" 
                    id="projectName" 
                    name="projectname" 
                    value="" 
                    placeholder="" 
                    variant="title" 
                    class="w-full" 
                />
            <br />
            
            </div>
            <div class="col-md-4">
                <div class='svgContainer' style="width:300px; margin-top:40px;">
                    {!! file_get_contents(ROOT . "/dist/images/svg/undraw_game_day_ucx9.svg") !!}
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 text-right">
                <input type="submit" value="{{ __('buttons.next') }}" />
            </div>
        </div>
    </form>
</div>
