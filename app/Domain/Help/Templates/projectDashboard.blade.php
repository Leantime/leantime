<div class="center padding-lg" style="width:800px;">
    <div>
        <x-global::undrawSvg
            image="undraw_joyride_re_968t.svg"
            maxWidth="auto"
            headlineSize="var(--font-size-xxxl)"
            maxheight="auto"
            height="250px"
            headline="Managing Projects"
        ></x-global::undrawSvg>
    </div>
    <div class="onboarding" style="font-size:var(--font-size-l);">
        <br />
        <div id="firstLoginContent">
            <p><br />Projects in Leantime are collaborative workspaces where you and your team organize, track, and deliver work efficiently. Each project serves as a container for related goals, tasks, milestones, and allows you to monitor progress in one central location. <br /><br />
                Whether you're managing work, school, or internal personal initiatives, Leantime projects provide the structure and tools needed to turn ideas into successful outcomes.
            </p><br />
        </div>
        <br /><br />
        <div class="center">
            <x-global::button link="javascript:void(0)" type="secondary" onclick="leantime.helperController.closeModal()">I'll explore on my own</x-global::button>
            <x-global::button link="javascript:void(0)" type="primary" onclick="leantime.helperController.closeModal(); leantime.helperController.startProjectDashboardTour();">{{ __("buttons.start_tour") }} <i class="fa-solid fa-arrow-right"></i></x-global::button>
        </div>
        <div class="tw:mt-3 center">
            <form hx-post="{{ BASE_URL }}/help/helperModal/dontShowAgain" hx-trigger="change" hx-swap="none">
                <input type="hidden" name="modalId" value="projectDashboard" />
                <x-global::forms.checkbox name="hidePermanently" id="dontShowAgain" label="Don't show this again" />
            </form>
        </div>
    </div>
</div>
