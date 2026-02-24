<div class="center padding-lg">

    <x-global::undrawSvg
        image="undraw_new_ideas_jdea.svg"
        maxWidth="50%"
        headlineSize="var(--font-size-xxxl)"
        maxheight="auto"
        height="250px"
        headline="{{ __('headlines.welcome_to_organized_idea_board') }}"
    ></x-global::undrawSvg>
    <br />
    <p>{!! __('text.advanced_boards_helper_content') !!}</p>
    <br /><br />

    <a href="javascript:void(0);" onclick="leantime.helperController.closeModal()">{{ __('links.close') }}</a><br />
    <a href="javascript:void(0);" onclick="leantime.helperController.hideAndKeepHidden('advancedIdeaBoards')">{{ __('links.close_dont_show_again') }}</a>

</div>
