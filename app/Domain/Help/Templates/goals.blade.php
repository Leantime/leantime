<div class="center padding-lg">

    <div>
        <x-globals::undrawSvg
            image="undraw_goals_re_lu76.svg"
            maxWidth="auto"
            headlineSize="var(--font-size-xxxl)"
            maxheight="auto"
            height="250px"
            headline="Goals to keep you focused"
        ></x-globals::undrawSvg>
    </div>

    <div style="font-size:var(--font-size-l);">
        <br />
        <div id="firstLoginContent">
            <p><br />Goals allow you to break down your project into achievable, measurable and actionable chunks.<br />While milestones focus on execution, goals are about the metrics you want to achieve.<br/>
                Each goal should have a clear objective (the thing you want to achieve) and should be easily measurable using a single metric.<br /><br />
                Once you have a goal you can assign milestones to it to break it down into the executable tasks.<br />
            </p><br />
        </div>
        <br /><br />
        <div class="center">
            <x-globals::forms.button link="javascript:void(0)" type="secondary" onclick="leantime.helperController.closeModal()">I'll explore on my own</x-globals::forms.button>
            <x-globals::forms.button link="javascript:void(0)" type="primary" onclick="leantime.helperController.closeModal(); leantime.helperController.startGoalTour();">{{ __("buttons.start_tour") }} <x-global::elements.icon name="arrow_forward" /></x-globals::forms.button>
        </div>
        <div class="tw:mt-3 center">
            <form hx-post="{{ BASE_URL }}/help/helperModal/dontShowAgain" hx-trigger="change" hx-swap="none">
                <input type="hidden" name="modalId" value="goals" />
                <x-globals::forms.checkbox name="hidePermanently" id="dontShowAgain" label="Don't show this again" />
            </form>
        </div>
    </div>
</div>
