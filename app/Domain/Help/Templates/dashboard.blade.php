<div class="center padding-lg" style="width:800px;">
    <div class="row">
        <div class="col-md-12">
            <x-global::undrawSvg image="undraw_social_serenity_vhix.svg" maxWidth="auto"  maxheight="auto" height="250px" headline="{{ __('headlines.welcome') }}"></x-global::undrawSvg>
        </div>
    </div>
    <div class="row onboarding">
        <div class="col-md-12" style="font-size:var(--font-size-l);">
            <br />
            {!! __('text.help_dashboard_intro') !!}
            <br /><br />
            <x-global::forms.button tag="a" link="javascript:void(0)" contentRole="primary" onclick="leantime.helperController.hideAndKeepHidden('dashboard'); leantime.helperController.startProjectDashboardTour();">{{ __("buttons.lets_go") }} <i class="fa-solid fa-arrow-right"></i></x-global::forms.button>
            <div class="clearall"></div>
        </div>
    </div>
</div>
