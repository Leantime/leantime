<div class="center padding-lg">

    <x-globals::undrawSvg
        image="undraw_design_data_khdb.svg"
        maxWidth="50%"
        headlineSize="var(--font-size-xxxl)"
        maxheight="auto"
        height="250px"
        headline="{{ __('headlines.welcome_to_simple_research_board') }}"
    ></x-globals::undrawSvg>
    <br />
    {!! __('text.simple_lean_canvas_helper_content') !!}
    <br /><br />

    <a href="javascript:void(0);" onclick="leantime.helperController.closeModal()">{{ __('links.close') }}</a><br />

</div>
