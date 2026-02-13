<div class="center padding-lg">

    <div class="row">
        <div class="col-md-12">
            <x-global::undrawSvg
                image="undraw_Organizing_projects_0p9a.svg"
                maxWidth="50%"
                headlineSize="var(--font-size-xxxl)"
                maxheight="auto"
                height="250px"
                headline=""
            ></x-global::undrawSvg>
            <br />
            {!! __('text.show_projects_helper_content') !!}
            <br /><br />
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <a href="javascript:void(0);" onclick="leantime.helperController.closeModal()">{{ __('links.close') }}</a><br />
        </div>
    </div>

</div>
