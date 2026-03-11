<div class="center padding-lg">

    <x-globals::undrawSvg
        image="undraw_Organizing_projects_0p9a.svg"
        maxWidth="50%"
        headlineSize="var(--font-size-xxxl)"
        maxheight="auto"
        height="250px"
        headline=""
    ></x-globals::undrawSvg>
    <br />
    {!! __('text.show_projects_helper_content') !!}
    <br /><br />

    <a href="javascript:void(0);" onclick="leantime.helperController.closeModal()">{{ __('links.close') }}</a><br />

</div>
