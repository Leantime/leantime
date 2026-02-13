<div class="center padding-lg">

    <div class="row">
        <div class="col-md-12">
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
    </div>

    <div class="row">
        <div class="col-md-12">
            <a href="javascript:void(0);" onclick="leantime.helperController.closeModal()">{{ __('links.close') }}</a><br />
        </div>
    </div>

</div>
