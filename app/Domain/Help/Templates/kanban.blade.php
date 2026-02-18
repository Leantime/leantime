<div class="center padding-lg">

    <div>
        <x-global::undrawSvg
            image="undraw_scrum-board_uqku.svg"
            maxWidth="auto"
            headlineSize="var(--font-size-xxxl)"
            maxheight="auto"
            height="250px"
            headline="{{ __('headlines.the_kanban_board') }}"
        ></x-global::undrawSvg>
    </div>

    <div style="font-size:var(--font-size-l);">
        <br />
        <div id="firstLoginContent">
            <p><br />{!! __('text.kanban_helper_content') !!}
            </p><br />
        </div>
        <br /><br />
        <div class="tw:text-center">
            <x-global::button link="javascript:void(0)" type="secondary" onclick="leantime.helperController.closeModal()">I'll explore on my own</x-global::button>
            <x-global::button link="javascript:void(0)" type="primary" onclick="leantime.helperController.closeModal(); leantime.helperController.startKanbanTour();">{{ __("buttons.start_tour") }} <i class="fa-solid fa-arrow-right"></i></x-global::button>
        </div>
        <div class="tw:mt-3 tw:text-center">
            <form hx-post="{{ BASE_URL }}/help/helperModal/dontShowAgain" hx-trigger="change" hx-swap="none">
                <label class="tw:text-sm tw:mt-sm" >
                    <input type="hidden" name="modalId" value="kanban" />
                    <input type="checkbox" id="dontShowAgain" name="hidePermanently"  style="margin-top:-2px;">
                    Don't show this again
                </label>
            </form>
        </div>
    </div>
</div>
