<form class="onboardingModal" method="post" action="{{ BASE_URL }}/help/firstLogin?step={{ $nextStep }}">
    <input type="hidden" name="currentStep" value="{{ $currentStep }}" />
    <div class="row">
        <div class="col-md-6">
            <h1>{{ __('headlines.invite_crew') }}</h1>
            <p>{{ __('text.invite_team') }}</p>
            <br />
            <input type="email" name="email1" value="" placeholder="{{ __('input.placeholder.email_invite') }}" style="width: 100%;"/><br />
            <input type="email" name="email2" value="" placeholder="{{ __('input.placeholder.email_invite') }}" style="width: 100%;"/><br />
            <input type="email" name="email3" value="" placeholder="{{ __('input.placeholder.email_invite') }}" style="width: 100%;"/><br />
            <br />
                  </div>
        <div class="col-md-6">
            <div class='svgContainer' style="width:300px; margin-top:60px;">
                {!! file_get_contents(ROOT . "/dist/images/svg/undraw_children_re_c37f.svg"); !!}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 text-right">
            <a href="javascript:void(0);"  class="btn btn-secondary" onclick="jQuery.nmTop().close();">{{ __('links.skip_for_now') }}</a>
            <input type="submit" value="{{ __('buttons.lets_go') }}"/>
        </div>
    </div>

</form>
