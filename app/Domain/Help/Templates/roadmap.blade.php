<div class="center padding-lg">

    <div class="row">
        <div class="col-md-12">

            <x-global::undrawSvg
                image="undraw_adjustments_p22m.svg"
                maxWidth="auto"
                headlineSize="var(--font-size-xxxl)"
                maxheight="auto"
                height="250px"
                headline="{{ __('headlines.welcome_to_your_roadmap') }}"
            ></x-global::undrawSvg>
        </div>
    </div>

    <div class="row ">
        <div class="col-md-12" style="font-size:var(--font-size-l);">
            <br />
            <div id="firstLoginContent">
                <p><br />{!! __('text.milestone_helper_content') !!}
                </p><br />
            </div>
            <br /><br />
            <div class="row">
                <div class="col-md-12 tw-text-center">
                    <a href="javascript:void(0)" class="btn btn-secondary" onclick="leantime.helperController.closeModal()">I'll explore on my own</a>
                    <a href="javascript:void(0)" class="btn btn-primary" onclick="leantime.helperController.closeModal(); leantime.helperController.startMilestoneTour();">{{ __("buttons.start_tour") }} <i class="fa-solid fa-arrow-right"></i></a>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-12 tw-text-center">
                    <form hx-post="{{ BASE_URL }}/help/helperModal/dontShowAgain" hx-trigger="change" hx-swap="none">
                        <label class="tw-text-sm tw-mt-sm" >
                            <input type="hidden" name="modalId" value="roadmap" />
                            <input type="checkbox" id="dontShowAgain" name="hidePermanently"  style="margin-top:-2px;">
                            Don't show this again
                        </label>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
