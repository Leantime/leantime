<div class="center padding-lg" style="width:800px;">
    <div>
        <x-global::undrawSvg image="undraw_social_serenity_vhix.svg" maxWidth="auto"  maxheight="auto" height="250px" headline="{{ __('headlines.welcome') }}"></x-global::undrawSvg>
    </div>
    <div class="onboarding" style="font-size:var(--font-size-l);">
        <br />
        Leantime is built to empower your super powers and help you see your progress towards your goals.<br />
        <br />
        Most of us are used to creating task lists — but we're going to ask you to think about what exactly you are trying to accomplish.<br />
        <br />
        1. Set a vision (strategy) and set your goals<br />
        2. Define the work that will get you to those goals<br />
        3. And then reach them, planning your day to day with your My Work Dashboard.<br />
        <br /><br />
        <x-global::button link="javascript:void(0)" type="primary" onclick="leantime.helperController.hideAndKeepHidden('dashboard'); leantime.helperController.startProjectDashboardTour();">{{ __("buttons.lets_go") }} <i class="fa-solid fa-arrow-right"></i></x-global::button>
        <div class="clearall"></div>
    </div>
</div>
