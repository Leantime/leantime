
<div class="center padding-lg" style="width:800px;">
    <div class="row">
        <div class="col-md-12">
            <x-global::undrawSvg
                image="undraw_joyride_re_968t.svg"
                maxWidth="auto"
                headlineSize="var(--font-size-xxxl)"
                maxheight="auto"
                height="250px"
                headline="{{ __('headlines.managing_projects') }}"
            ></x-global::undrawSvg>
        </div>
    </div>
    <div class="row onboarding">
        <div class="col-md-12" style="font-size:var(--font-size-l);">
            <br />
            <div id="firstLoginContent">
                <p><br />{!! __('text.project_dashboard_tour_intro') !!}</p><br />
            </div>
            <br /><br />
            <div class="row">
                <div class="col-md-12 tw-text-center">
                    <x-global::forms.button tag="a" link="javascript:void(0)" contentRole="tertiary" onclick="leantime.helperController.closeModal()">{{ __('buttons.explore_on_my_own') }}</x-global::forms.button>
                    <x-global::forms.button tag="a" link="javascript:void(0)" contentRole="primary" onclick="leantime.helperController.closeModal(); leantime.helperController.startProjectDashboardTour();">{{ __("buttons.start_tour") }} <i class="fa-solid fa-arrow-right"></i></x-global::forms.button>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-12 tw-text-center">
                    <form hx-post="{{ BASE_URL }}/help/helperModal/dontShowAgain" hx-trigger="change" hx-swap="none">
                        <input type="hidden" name="modalId" value="projectDashboard" />
                        <x-global::forms.checkbox variant="toggle" id="dontShowAgain" name="hidePermanently" value="on" wrapperClass="tw-mt-sm">{{ __('label.dont_show_this_again') }}</x-global::forms.checkbox>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

