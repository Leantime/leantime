<div class="center padding-lg">

    <div class="row">
        <div class="col-md-12">

            <x-global::undrawSvg
                image="undraw_goals_re_lu76.svg"
                maxWidth="auto"
                headlineSize="var(--font-size-xxxl)"
                maxheight="auto"
                height="250px"
                headline="Goals to keep you focused"
            ></x-global::undrawSvg>
        </div>
    </div>

    <div class="row ">
        <div class="col-md-12" style="font-size:var(--font-size-l);">
            <br />
            <div id="firstLoginContent">
                <p><br />Goals allow you to break down your project into achievable, measurable and actionable chunks.<br />While milestones focus on execution, goals are about the metrics you want to achieve.<br/>
                    Each goal should have a clear objective (the thing you want to achieve) and should be easily measurable using a single metric.<br /><br />
                    Once you have a goal you can assign milestones to it to break it down into the executable tasks.<br />
                </p><br />
            </div>
            <br /><br />
            <div class="row">
                <div class="col-md-12 tw:text-center">
                    <a href="javascript:void(0)" class="btn btn-secondary" onclick="leantime.helperController.closeModal()">I'll explore on my own</a>
                    <a href="javascript:void(0)" class="btn btn-primary" onclick="leantime.helperController.closeModal(); leantime.helperController.startGoalTour();">{{ __("buttons.start_tour") }} <i class="fa-solid fa-arrow-right"></i></a>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-12 tw:text-center">
                    <form hx-post="{{ BASE_URL }}/help/helperModal/dontShowAgain" hx-trigger="change" hx-swap="none">
                        <label class="tw:text-sm tw:mt-sm" >
                            <input type="hidden" name="modalId" value="goals" />
                            <input type="checkbox" id="dontShowAgain" name="hidePermanently"  style="margin-top:-2px;">
                            Don't show this again
                        </label>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

