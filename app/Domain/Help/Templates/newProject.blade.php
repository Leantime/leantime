<div class="center padding-lg">

    <x-global::undrawSvg
        image="undraw_Organizing_projects_0p9a.svg"
        maxWidth="50%"
        headlineSize="var(--font-size-xxxl)"
        maxheight="auto"
        height="250px"
        headline="{{ __('headlines.welcome_to_your_project') }}"
    ></x-global::undrawSvg>
    <br />
    {!! __('text.new_project_helper_content') !!}

    <div class="center">
        <a href="javascript:void(0);" onclick="leantime.helperController.closeModal()">{{ __('links.close') }}</a><br />
    </div>

</div>
