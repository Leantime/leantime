<div class="center padding-lg">

    <x-global::undrawSvg
        image="undraw_new_ideas_jdea.svg"
        maxWidth="50%"
        headlineSize="var(--font-size-xxxl)"
        maxheight="auto"
        height="250px"
        headline="{{ __('headlines.welcome_to_idea_board') }}"
    ></x-global::undrawSvg>
    <br />
    <p>{!! __('text.idea_board_helper_content') !!}</p>
    <br /><br />

    <a href="javascript:void(0);" onclick="leantime.helperController.closeModal()">{{ __('links.close') }}</a><br />

</div>
