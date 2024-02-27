<div style="max-width:900px;">
    <form class="onboardingModal" method="post" action="{{ BASE_URL }}/help/firstLogin?step={{ $nextStep }}">
        <input type="hidden" name="currentStep" value="{{ $currentStep }}" />
        <div class="row">
            <div class="col-md-8">
                <h1>{{  __('headlines.make_it_happen') }}</h1>
                <p>{!!  __('text.structured_project_thinking') !!}</p>
                <br />
                <label><strong>{{ __('label.what_are_you_trying_to_accomplish') }}</strong></label>
                <textarea id="accomplish" name="accomplish" value="" placeholder="" rows="3" style="width:99%; overflow-x: hidden;"></textarea>
                <br />
                <label><strong>{{ __('label.how_does_the_world_look_like') }}</strong></label>
                <textarea id="wordlview" name="worldview" value="" placeholder="" rows="3" style="width:99%; overflow-x: hidden;"></textarea>
                <br />
                <label><strong>{{ __('label.why_is_this_important') }}</strong></label>
                <textarea id="whyImportant" name="whyImportant" value="" placeholder="" rows="3" style="width:99%; overflow-x: hidden;"></textarea>

            </div>
            <div class="col-md-4">
                <div class='svgContainer' style="width:400px; margin-top:40px;">
                    {!! file_get_contents(ROOT . "/dist/images/svg/undraw_goals_re_lu76.svg") !!}
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 tw-text-right">
                <a href="javascript:void(0);" class="btn btn-secondary" onclick="jQuery.nmTop().close();">{{ __('links.skip_for_now') }}</a>
                <input type="submit" value="{{ __('buttons.next') }}" />
            </div>
        </div>
    </form>
</div>
