<div class="center padding-lg">

    <div>
        <x-globals::undrawSvg
            image="undraw_scrum-board_uqku.svg"
            maxWidth="auto"
            headlineSize="var(--font-size-xxxl)"
            maxheight="auto"
            height="250px"
            headline="{{ __('headlines.the_kanban_board') }}"
        ></x-globals::undrawSvg>
    </div>

    <div style="font-size:var(--font-size-l);">
        <br />
        <div id="firstLoginContent">
            <p><br />{!! __('text.kanban_helper_content') !!}
            </p><br />
        </div>
        <br /><br />
        <div class="center">
            <x-globals::forms.button link="javascript:void(0)" type="secondary" onclick="leantime.helperController.closeModal()">I'll explore on my own</x-globals::forms.button>
            <x-globals::forms.button link="javascript:void(0)" type="primary" onclick="leantime.helperController.closeModal(); leantime.helperController.startKanbanTour();">{{ __("buttons.start_tour") }} <i class="fa-solid fa-arrow-right"></i></x-globals::forms.button>
        </div>
        <div class="tw:mt-3 center">
            <form hx-post="{{ BASE_URL }}/help/helperModal/dontShowAgain" hx-trigger="change" hx-swap="none">
                <input type="hidden" name="modalId" value="kanban" />
                <x-globals::forms.checkbox name="hidePermanently" id="dontShowAgain" label="Don't show this again" />
            </form>
        </div>
    </div>
</div>
