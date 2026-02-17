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
        <div class="tw:text-center">
            <a href="javascript:void(0)" class="btn btn-secondary" onclick="leantime.helperController.closeModal()">I'll explore on my own</a>
            <a href="javascript:void(0)" class="btn btn-primary" onclick="leantime.helperController.closeModal(); leantime.helperController.startProjectDashboardTour();">{{ __("buttons.start_tour") }} <i class="fa-solid fa-arrow-right"></i></a>
        </div>
        <div class="tw:mt-3 tw:text-center">
            <form hx-post="{{ BASE_URL }}/help/helperModal/dontShowAgain" hx-trigger="change" hx-swap="none">
                <label class="tw:text-sm tw:mt-sm" >
                    <input type="hidden" name="modalId" value="projectDashboard" />
                    <input type="checkbox" id="dontShowAgain" name="hidePermanently"  style="margin-top:-2px;">
                    Don't show this again
                </label>
            </form>
        </div>
    </div>
</div>
