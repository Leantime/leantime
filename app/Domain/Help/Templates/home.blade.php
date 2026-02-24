<div class="center padding-lg" style="width:800px;">
    <div>
        <x-globals::undrawSvg
            image="undraw_social_serenity_vhix.svg"
            maxWidth="auto"
            headlineSize="var(--font-size-xxxl)"
            maxheight="auto"
            height="250px"
            headline="{{ __('headlines.your_personal_dashboard') }}"
        ></x-globals::undrawSvg>
    </div>
    <div class="onboarding" style="font-size:var(--font-size-l);">
        <br />
            <div id="firstLoginContent">
                <p>Your My Work dashboard brings everything that matters into focus. <br />
                   Your most important work is now front and center, organized just for you and how your brain works best.<br /><br />
                   From quick tasks to ambitious goals, everything you need is right here. This is your space to capture ideas, track progress, and celebrate wins along the way.<br />
                </p><br />
            </div>
        <br /><br />
        <div class="center">
            <x-globals::forms.button link="javascript:void(0)" type="secondary" onclick="leantime.helperController.closeModal()">I'll explore on my own</x-globals::forms.button>
            <x-globals::forms.button link="javascript:void(0)" type="primary" onclick="leantime.helperController.closeModal(); leantime.helperController.startMyWorkDashboardTour();">{{ __("buttons.start_tour") }} <i class="fa-solid fa-arrow-right"></i></x-globals::forms.button>
        </div>
        <div class="tw:mt-3 center">
            <form hx-post="{{ BASE_URL }}/help/helperModal/dontShowAgain" hx-trigger="change" hx-swap="none">
                <input type="hidden" name="modalId" value="home" />
                <x-globals::forms.checkbox name="hidePermanently" id="dontShowAgain" label="Don't show this again" />
            </form>
        </div>
    </div>
</div>
