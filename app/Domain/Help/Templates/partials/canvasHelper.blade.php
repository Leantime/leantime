<div class="center padding-lg">

    <div>
        <x-global::undrawSvg
            image="undraw_design_data_khdb.svg"
            maxWidth="300px"
            headlineSize="var(--font-size-xxxl)"
            maxheight="auto"
            height="250px"
            headline="{{ __("headlines.{$canvasName}.welcome_to_board") }}"
        ></x-global::undrawSvg>
        <br />
        {!! __("text.{$canvasName}.helper_content") !!}
        <br /><br />
    </div>

    <div>
        <a href="javascript:void(0);" onclick="leantime.helperController.closeModal()">{{ __('links.close') }}</a><br />
    </div>

</div>
